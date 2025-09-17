<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

/**
 * 사용자 샤드 테이블 생성을 위한 Artisan 커맨드
 *
 * 이 커맨드는 users 테이블과 동일한 구조의 샤드 테이블들을 생성합니다.
 * 샤드 개수는 config/sharding.php 파일에서 설정된 값을 사용합니다.
 * SQLite, MySQL, PostgreSQL 등 다양한 데이터베이스를 지원합니다.
 */
class CreateUserShards extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:shards {--rollback : 기존 샤드 테이블들을 삭제합니다}';


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'users_000 ~ users_NNN 샤드 테이블을 생성하거나 삭제합니다.';


    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        // rollback 옵션이 있으면 기존 샤드 테이블들을 삭제
        if ($this->option('rollback')) {
            $this->rollbackShards();
            return;
        }

        // config에서 샤드 개수를 가져옵니다
        $count = config('sharding.user.shard_count', 1000);

        $this->info("샤드 테이블 개수: {$count}개");

        // 데이터베이스 타입을 확인하고 출력
        $driver = $this->getDatabaseDriver();
        $this->info("데이터베이스 타입: " . strtoupper($driver));

        // 기존 users 테이블의 컬럼 정보를 가져옵니다
        $columns = $this->getUsersTableColumns();

        // 각 샤드 테이블을 생성합니다
        for ($i = 0; $i < $count; $i++) {
            // 샤드 테이블 이름 생성 (예: users_000, users_001, ...)
            $tableName = sprintf('%s%03d', config('sharding.user.table_prefix', 'users_'), $i);

            // Schema를 사용하여 테이블을 생성합니다
            Schema::create($tableName, function (Blueprint $table) use ($columns) {
                foreach ($columns as $column) {
                    $this->addColumnToTable($table, $column);
                }
            });

            $this->shardConfigEnable(true);
            $this->line("✅ [$tableName] 테이블 생성 완료");
        }

        $this->info("총 {$count}개의 샤드 테이블 생성 완료!");
    }

    /**
     * 샤딩 설정 파일에서 user.enable 설정을 업데이트합니다.
     *
     * @param bool $enable 샤딩 사용 여부
     * @return void
     */
    private function shardConfigEnable(bool $enable)
    {
            // config/sharding.php 파일에서 user.enable 설정을 업데이트
            $shardingConfig = config('sharding');
            $shardingConfig['user']['enable'] = $enable;

            // config 파일 경로
            $configPath = config_path('sharding.php');

            // 현재 config 파일의 내용을 읽어옴
            $currentConfig = file_get_contents($configPath);

            // user.enable 설정 업데이트
            $pattern = "/('enable'\s*=>\s*)(true|false)/";
            $replacement = "$1" . ($enable ? 'true' : 'false');
            $newConfig = preg_replace($pattern, $replacement, $currentConfig);

            // 파일에 저장
            file_put_contents($configPath, $newConfig);

            $this->line("✅ config/sharding.php 파일의 user.enable 설정을 " . ($enable ? 'true' : 'false') . "로 변경 완료");

    }

    /**
     * 데이터베이스 드라이버를 가져옵니다.
     *
     * @return string 데이터베이스 드라이버명
     */
    private function getDatabaseDriver(): string
    {
        return DB::connection()->getDriverName();
    }

    /**
     * 기존 샤드 테이블들을 삭제합니다.
     *
     * @return void
     */
    private function rollbackShards(): void
    {
        $this->info('기존 샤드 테이블들을 찾는 중...');

        // 설정에서 테이블 접두사를 가져옵니다
        $tablePrefix = config('sharding.user.table_prefix', 'users_');

        // 데이터베이스 타입에 따라 다른 쿼리 사용
        $driver = $this->getDatabaseDriver();
        $this->info("데이터베이스 타입: " . strtoupper($driver));

        $shardTables = $this->getShardTables($tablePrefix);

        // 삭제할 테이블이 없으면 종료
        if (empty($shardTables)) {
            $this->warn('삭제할 샤드 테이블이 없습니다.');
            return;
        }

        // 삭제할 테이블 목록을 표시합니다
        $this->info('다음 샤드 테이블들을 삭제합니다:');
        foreach ($shardTables as $table) {
            $this->line("  - {$table->TABLE_NAME}");
        }

        // 사용자에게 삭제 확인을 요청합니다
        if (!$this->confirm('정말로 이 테이블들을 삭제하시겠습니까?')) {
            $this->info('삭제가 취소되었습니다.');
            return;
        }

        // 각 테이블을 삭제합니다
        $deletedCount = 0;
        foreach ($shardTables as $table) {
            $tableName = $table->TABLE_NAME;

            try {
                Schema::dropIfExists($tableName);
                $this->line("✅ [$tableName] 테이블 삭제 완료");
                $deletedCount++;
            } catch (\Exception $e) {
                $this->error("❌ [$tableName] 테이블 삭제 실패: " . $e->getMessage());
            }
        }

        $this->shardConfigEnable(false); // 샤딩 사용 여부를 false로 변경

        $this->info("총 {$deletedCount}개의 샤드 테이블 삭제 완료!");
    }

    /**
     * 샤드 테이블 목록을 가져옵니다.
     *
     * @param string $tablePrefix 테이블 접두사
     * @return array 샤드 테이블 목록
     */
    private function getShardTables(string $tablePrefix): array
    {
        $driver = $this->getDatabaseDriver();

        switch ($driver) {
            case 'sqlite':
                return DB::select("
                    SELECT name as TABLE_NAME
                    FROM sqlite_master
                    WHERE type = 'table'
                    AND name LIKE ?
                    ORDER BY name
                ", [$tablePrefix . '%']);

            case 'mysql':
                return DB::select("
                    SELECT TABLE_NAME
                    FROM INFORMATION_SCHEMA.TABLES
                    WHERE TABLE_SCHEMA = DATABASE()
                    AND TABLE_NAME LIKE ?
                    ORDER BY TABLE_NAME
                ", [$tablePrefix . '%']);

            case 'pgsql':
                return DB::select("
                    SELECT tablename as TABLE_NAME
                    FROM pg_tables
                    WHERE schemaname = 'public'
                    AND tablename LIKE ?
                    ORDER BY tablename
                ", [$tablePrefix . '%']);

            default:
                $this->error("지원하지 않는 데이터베이스 타입입니다: {$driver}");
                exit(1);
        }
    }

    /**
     * users 테이블의 컬럼 정보를 가져옵니다.
     *
     * @return array users 테이블의 컬럼 정보 배열
     * @throws \Exception users 테이블이 존재하지 않을 경우
     */
    private function getUsersTableColumns(): array
    {
        // users 테이블이 존재하는지 확인
        if (!Schema::hasTable('users')) {
            $this->error('users 테이블이 존재하지 않습니다. 먼저 마이그레이션을 실행해주세요.');
            exit(1);
        }

        $driver = $this->getDatabaseDriver();

        switch ($driver) {
            case 'sqlite':
                $columns = $this->getSqliteColumns();
                break;
            case 'mysql':
                $columns = $this->getMysqlColumns();
                break;
            case 'pgsql':
                $columns = $this->getPostgresqlColumns();
                break;
            default:
                $this->error("지원하지 않는 데이터베이스 타입입니다: {$driver}");
                exit(1);
        }

        // UUID 컬럼이 이미 존재하는지 확인
        $hasUuidColumn = collect($columns)->contains('COLUMN_NAME', 'uuid');

        if (!$hasUuidColumn) {
            // UUID 컬럼을 추가 (컬럼 목록의 마지막에 추가)
            $uuidColumn = (object) [
                'COLUMN_NAME' => 'uuid',
                'DATA_TYPE' => 'varchar',
                'IS_NULLABLE' => 'NO',
                'COLUMN_DEFAULT' => null,
                'CHARACTER_MAXIMUM_LENGTH' => 36,
                'COLUMN_KEY' => 'UNI',
                'EXTRA' => '',
                'COLUMN_COMMENT' => '사용자 고유 식별자 (UUID)'
            ];

            $columns[] = $uuidColumn;
            $this->info('UUID 컬럼이 추가되었습니다.');
        }

        return $columns;
    }

    /**
     * SQLite용 컬럼 정보를 가져옵니다.
     *
     * @return array 컬럼 정보 배열
     */
    private function getSqliteColumns(): array
    {
        $columns = DB::select("PRAGMA table_info(users)");

        return array_map(function($column) {
            return (object) [
                'COLUMN_NAME' => $column->name,
                'DATA_TYPE' => $this->mapSqliteType($column->type),
                'IS_NULLABLE' => $column->notnull ? 'NO' : 'YES',
                'COLUMN_DEFAULT' => $column->dflt_value,
                'CHARACTER_MAXIMUM_LENGTH' => null,
                'COLUMN_KEY' => $column->pk ? 'PRI' : '',
                'EXTRA' => $column->pk && strpos(strtolower($column->type), 'integer') !== false ? 'auto_increment' : '',
                'COLUMN_COMMENT' => ''
            ];
        }, $columns);
    }

    /**
     * MySQL용 컬럼 정보를 가져옵니다.
     *
     * @return array 컬럼 정보 배열
     */
    private function getMysqlColumns(): array
    {
        return DB::select("
            SELECT
                COLUMN_NAME,
                DATA_TYPE,
                IS_NULLABLE,
                COLUMN_DEFAULT,
                CHARACTER_MAXIMUM_LENGTH,
                COLUMN_KEY,
                EXTRA,
                COLUMN_COMMENT
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = 'users'
            ORDER BY ORDINAL_POSITION
        ");
    }

    /**
     * PostgreSQL용 컬럼 정보를 가져옵니다.
     *
     * @return array 컬럼 정보 배열
     */
    private function getPostgresqlColumns(): array
    {
        return DB::select("
            SELECT
                column_name as COLUMN_NAME,
                data_type as DATA_TYPE,
                is_nullable as IS_NULLABLE,
                column_default as COLUMN_DEFAULT,
                character_maximum_length as CHARACTER_MAXIMUM_LENGTH,
                CASE
                    WHEN constraint_type = 'PRIMARY KEY' THEN 'PRI'
                    WHEN constraint_type = 'UNIQUE' THEN 'UNI'
                    ELSE ''
                END as COLUMN_KEY,
                CASE
                    WHEN column_default LIKE 'nextval%' THEN 'auto_increment'
                    ELSE ''
                END as EXTRA,
                '' as COLUMN_COMMENT
            FROM information_schema.columns c
            LEFT JOIN information_schema.key_column_usage kcu
                ON c.column_name = kcu.column_name
                AND c.table_name = kcu.table_name
            LEFT JOIN information_schema.table_constraints tc
                ON kcu.constraint_name = tc.constraint_name
            WHERE c.table_name = 'users'
            ORDER BY c.ordinal_position
        ");
    }

    /**
     * SQLite 데이터 타입을 표준 데이터 타입으로 매핑합니다.
     *
     * @param string $sqliteType SQLite 데이터 타입
     * @return string 표준 데이터 타입
     */
    private function mapSqliteType(string $sqliteType): string
    {
        $type = strtolower($sqliteType);

        // INTEGER 타입 (자동 증가 포함)
        if (strpos($type, 'integer') !== false) {
            return 'bigint';
        }

        // VARCHAR 타입
        if (strpos($type, 'varchar') !== false) {
            return 'varchar';
        }

        // TEXT 타입
        if (strpos($type, 'text') !== false) {
            return 'text';
        }

        // REAL/FLOAT 타입
        if (strpos($type, 'real') !== false || strpos($type, 'float') !== false) {
            return 'decimal';
        }

        // BLOB 타입
        if (strpos($type, 'blob') !== false) {
            return 'binary';
        }

        // 기본값
        return 'varchar';
    }

    /**
     * 컬럼 정보를 바탕으로 테이블에 컬럼을 추가합니다.
     *
     * @param Blueprint $table Laravel Blueprint 인스턴스
     * @param object $column 데이터베이스 컬럼 정보 객체
     * @return void
     */
    private function addColumnToTable(Blueprint $table, object $column): void
    {
        // 컬럼 정보를 추출합니다
        $columnName = $column->COLUMN_NAME;
        $dataType = $column->DATA_TYPE;
        $isNullable = $column->IS_NULLABLE === 'YES';
        $columnDefault = $column->COLUMN_DEFAULT;
        $maxLength = $column->CHARACTER_MAXIMUM_LENGTH;
        $columnKey = $column->COLUMN_KEY;
        $extra = $column->EXTRA;

        // 컬럼 타입에 따라 적절한 메서드를 호출합니다
        switch ($dataType) {
            case 'bigint':
                if ($extra === 'auto_increment') {
                    // 자동 증가 Primary Key
                    $table->id();
                } else {
                    // 일반 bigint 컬럼
                    $columnDef = $table->bigInteger($columnName);
                    if ($isNullable) $columnDef->nullable();
                    if ($columnDefault !== null) $columnDef->default($columnDefault);
                }
                break;

            case 'varchar':
                // VARCHAR 컬럼 (길이 제한 있음)
                if ($columnName === 'uuid') {
                    // UUID 컬럼은 특별 처리
                    $columnDef = $table->uuid($columnName);
                    if ($isNullable) $columnDef->nullable();
                    if ($columnDefault !== null) $columnDef->default($columnDefault);
                } else {
                    // 일반 VARCHAR 컬럼
                    $columnDef = $table->string($columnName, $maxLength);
                    if ($isNullable) $columnDef->nullable();
                    if ($columnDefault !== null) $columnDef->default($columnDefault);
                }
                break;

            case 'text':
                // TEXT 컬럼 (길이 제한 없음)
                $columnDef = $table->text($columnName);
                if ($isNullable) $columnDef->nullable();
                break;

            case 'longtext':
                // LONGTEXT 컬럼 (매우 큰 텍스트)
                $columnDef = $table->longText($columnName);
                if ($isNullable) $columnDef->nullable();
                break;

            case 'timestamp':
                // TIMESTAMP 컬럼
                $columnDef = $table->timestamp($columnName);
                if ($isNullable) $columnDef->nullable();
                if ($columnDefault !== null) $columnDef->default($columnDefault);
                break;

            case 'int':
                // INTEGER 컬럼
                $columnDef = $table->integer($columnName);
                if ($isNullable) $columnDef->nullable();
                if ($columnDefault !== null) $columnDef->default($columnDefault);
                break;

            case 'decimal':
                // DECIMAL 컬럼
                $columnDef = $table->decimal($columnName, 8, 2);
                if ($isNullable) $columnDef->nullable();
                if ($columnDefault !== null) $columnDef->default($columnDefault);
                break;

            case 'binary':
                // BINARY 컬럼
                $columnDef = $table->binary($columnName);
                if ($isNullable) $columnDef->nullable();
                break;

            default:
                // 기본적으로 string으로 처리
                $columnDef = $table->string($columnName);
                if ($isNullable) $columnDef->nullable();
                if ($columnDefault !== null) $columnDef->default($columnDefault);
                break;
        }

        // 인덱스 및 제약조건을 추가합니다
        if ($columnKey === 'PRI') {
            // Primary key는 이미 처리됨 (id() 메서드에서 처리)
        } elseif ($columnKey === 'UNI') {
            // Unique 제약조건 추가
            $table->unique($columnName);
        } elseif ($columnKey === 'MUL') {
            // Index 추가
            $table->index($columnName);
        }
    }
}
