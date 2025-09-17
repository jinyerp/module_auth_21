<?php

namespace Jiny\Auth\App\Services\Sharding;

use Jiny\Auth\App\Models\UserShardingConfig;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Log;

/**
 * AuthShardingService
 *
 * 샤딩 시스템을 관리하는 서비스 클래스입니다.
 * 데이터베이스별 분기 처리를 통해 MySQL, PostgreSQL, SQLite를 지원합니다.
 *
 * @package Jiny\Auth\Services
 * @author JinyPHP
 * @version 1.0.0
 * @since 1.0.0
 * @license MIT
 */
class AuthShardingService
{
    /**
     * 지원하는 데이터베이스 드라이버 목록
     */
    private const SUPPORTED_DRIVERS = ['mysql', 'pgsql', 'sqlite'];

    /**
     * 기본 샤드 키
     */
    private const DEFAULT_SHARD_KEY = 'id';

    /**
     * 기본 샤딩 전략
     */
    private const DEFAULT_STRATEGY = 'hash';

    /**
     * 최대 샤드 수
     */
    private const MAX_SHARD_COUNT = 10000;
    /**
     * 현재 데이터베이스 드라이버를 가져옵니다.
     */
    private function getDatabaseDriver(): string
    {
        return DB::connection()->getDriverName();
    }

    /**
     * SQLite 데이터베이스인지 확인합니다.
     */
    private function isSqlite(): bool
    {
        return $this->getDatabaseDriver() === 'sqlite';
    }

    /**
     * MySQL 데이터베이스인지 확인합니다.
     */
    private function isMysql(): bool
    {
        return $this->getDatabaseDriver() === 'mysql';
    }

    /**
     * PostgreSQL 데이터베이스인지 확인합니다.
     */
    private function isPostgresql(): bool
    {
        return $this->getDatabaseDriver() === 'pgsql';
    }

    /**
     * 사용자 샤딩 설정을 생성하고 샤드 테이블들을 생성합니다.
     *
     * @param string $tableName 테이블 이름
     * @param int $shardCount 샤드 수
     * @param string $shardKey 샤드 키 (기본값: 'id')
     * @param string $strategy 샤딩 전략 (기본값: 'hash')
     * @param string|null $description 설명
     * @return UserShardingConfig 생성된 샤딩 설정
     * @throws \InvalidArgumentException 유효하지 않은 매개변수
     * @throws \Exception 샤딩 설정 생성 실패
     */
    public function createUserSharding(string $tableName, int $shardCount, string $shardKey = self::DEFAULT_SHARD_KEY, string $strategy = self::DEFAULT_STRATEGY, string $description = null): UserShardingConfig
    {
        // 입력값 검증
        $this->validateShardingParameters($tableName, $shardCount, $shardKey, $strategy);

        // 기존 설정이 있다면 비활성화
        UserShardingConfig::where('table_name', $tableName)
            ->update(['is_active' => false]);

        // 새로운 사용자 샤딩 설정 생성 (UUID는 모델에서 자동 생성)
        $config = UserShardingConfig::create([
            'table_name' => $tableName,
            'shard_count' => $shardCount,
            'shard_key' => $shardKey,
            'shard_strategy' => $strategy,
            'is_active' => true,
            'description' => $description,
            'created_by' => auth()->id(),
        ]);

        Log::info("새 샤딩 설정 생성: {$tableName}, UUID: {$config->config_uuid}");

        // 샤드 테이블들 생성
        $this->createShardTables($config);

        return $config;
    }

    /**
     * 샤딩 매개변수를 검증합니다.
     *
     * @param string $tableName 테이블 이름
     * @param int $shardCount 샤드 수
     * @param string $shardKey 샤드 키
     * @param string $strategy 샤딩 전략
     * @throws \InvalidArgumentException 유효하지 않은 매개변수
     */
    private function validateShardingParameters(string $tableName, int $shardCount, string $shardKey, string $strategy): void
    {
        if (empty($tableName)) {
            throw new \InvalidArgumentException('테이블 이름은 필수입니다.');
        }

        if ($shardCount < 1 || $shardCount > self::MAX_SHARD_COUNT) {
            throw new \InvalidArgumentException("샤드 수는 1에서 " . self::MAX_SHARD_COUNT . " 사이여야 합니다.");
        }

        if (empty($shardKey)) {
            throw new \InvalidArgumentException('샤드 키는 필수입니다.');
        }

        if (!in_array($strategy, ['hash', 'range'])) {
            throw new \InvalidArgumentException('샤딩 전략은 hash 또는 range여야 합니다.');
        }
    }

    /**
     * 샤드 테이블들을 생성합니다.
     *
     * @param UserShardingConfig $config 샤딩 설정
     * @throws \Exception 샤드 테이블 생성 실패
     */
    public function createShardTables(UserShardingConfig $config): void
    {
        $originalTableName = $config->table_name;

        // 원본 테이블의 구조를 가져옵니다
        $columns = $this->getTableColumns($originalTableName);
        Log::info("샤드 테이블 생성 시작: {$originalTableName}, 샤드 수: {$config->shard_count}, UUID: {$config->config_uuid}");

        // 각 샤드에 대해 테이블 생성
        for ($i = 0; $i < $config->shard_count; $i++) {
            $shardTableName = $config->getShardTableName($i);

            if (!$this->tableExists($shardTableName)) {
                try {
                    Schema::create($shardTableName, function (Blueprint $table) use ($columns, $originalTableName) {
                        $this->createTableStructure($table, $columns, $originalTableName);
                    });
                    Log::info("샤드 테이블 생성 완료: {$shardTableName}");
                } catch (\Exception $e) {
                    Log::error("샤드 테이블 생성 실패: {$shardTableName}, 오류: " . $e->getMessage());
                    throw $e;
                }
            } else {
                Log::info("샤드 테이블이 이미 존재함: {$shardTableName}");
            }
        }
    }

    /**
     * 샤드 테이블들을 삭제합니다.
     *
     * @param UserShardingConfig $config 샤딩 설정
     * @return bool 삭제 성공 여부
     */
    public function dropShardTables(UserShardingConfig $config): bool
    {
        try {
            for ($i = 0; $i < $config->shard_count; $i++) {
                $shardTableName = $config->getShardTableName($i);

                if ($this->tableExists($shardTableName)) {
                    Schema::dropIfExists($shardTableName);
                    Log::info("샤드 테이블 삭제 완료: {$shardTableName}");
                }
            }
            return true;
        } catch (\Exception $e) {
            Log::error("샤드 테이블 삭제 실패: " . $e->getMessage());
            return false;
        }
    }

    /**
     * 샤딩 설정의 유효성을 검사합니다.
     *
     * @param UserShardingConfig $config 샤딩 설정
     * @return array 검사 결과
     */
    public function validateShardingConfig(UserShardingConfig $config): array
    {
        $result = [
            'valid' => true,
            'errors' => [],
            'warnings' => []
        ];

        // 기본 테이블 존재 여부 확인
        if (!$this->tableExists($config->table_name)) {
            $result['valid'] = false;
            $result['errors'][] = "기본 테이블 '{$config->table_name}'이 존재하지 않습니다.";
        }

        // 샤드 테이블 존재 여부 확인
        $missingShards = [];
        for ($i = 0; $i < $config->shard_count; $i++) {
            $shardTableName = $config->getShardTableName($i);
            if (!$this->tableExists($shardTableName)) {
                $missingShards[] = $shardTableName;
            }
        }

        if (!empty($missingShards)) {
            $result['warnings'][] = "누락된 샤드 테이블: " . implode(', ', $missingShards);
        }

        return $result;
    }

    /**
     * 테이블의 컬럼 정보를 가져옵니다.
     */
    public function getTableColumns(string $tableName): array
    {
        if ($this->isSqlite()) {
            return $this->getTableColumnsForSqlite($tableName);
        } elseif ($this->isMysql()) {
            return $this->getTableColumnsForMysql($tableName);
        } elseif ($this->isPostgresql()) {
            return $this->getTableColumnsForPostgresql($tableName);
        } else {
            // 기본적으로 MySQL 방식 사용
            return $this->getTableColumnsForMysql($tableName);
        }
    }

    /**
     * SQLite용 테이블 컬럼 정보를 가져옵니다.
     */
    private function getTableColumnsForSqlite(string $tableName): array
    {
        $columns = [];
        $columnList = DB::select("PRAGMA table_info({$tableName})");

        foreach ($columnList as $column) {
            $columns[] = [
                'name' => $column->name,
                'type' => $column->type,
                'null' => $column->notnull ? 'NO' : 'YES',
                'key' => $column->pk ? 'PRI' : '',
                'default' => $column->dflt_value,
                'extra' => $column->pk ? 'auto_increment' : '',
            ];
        }

        return $columns;
    }

    /**
     * MySQL용 테이블 컬럼 정보를 가져옵니다.
     */
    private function getTableColumnsForMysql(string $tableName): array
    {
        $columns = [];
        $columnList = DB::select("DESCRIBE {$tableName}");

        foreach ($columnList as $column) {
            $columns[] = [
                'name' => $column->Field,
                'type' => $column->Type,
                'null' => $column->Null,
                'key' => $column->Key,
                'default' => $column->Default,
                'extra' => $column->Extra,
            ];
        }

        return $columns;
    }

    /**
     * PostgreSQL용 테이블 컬럼 정보를 가져옵니다.
     */
    private function getTableColumnsForPostgresql(string $tableName): array
    {
        $columns = [];
        $columnList = DB::select("
            SELECT
                column_name as name,
                data_type as type,
                is_nullable as null,
                column_default as default,
                '' as key,
                '' as extra
            FROM information_schema.columns
            WHERE table_name = ?
            ORDER BY ordinal_position
        ", [$tableName]);

        foreach ($columnList as $column) {
            $columns[] = [
                'name' => $column->name,
                'type' => $column->type,
                'null' => $column->null === 'YES' ? 'YES' : 'NO',
                'key' => $column->key,
                'default' => $column->default,
                'extra' => $column->extra,
            ];
        }

        return $columns;
    }

    /**
     * 테이블 구조를 생성합니다.
     */
    private function createTableStructure(Blueprint $table, array $columns, string $originalTableName): void
    {
        foreach ($columns as $column) {
            $columnName = $column['name'];
            $columnType = $column['type'];
            $isNullable = $column['null'] === 'YES';
            $isPrimary = $column['key'] === 'PRI';
            $defaultValue = $column['default'];
            $extra = $column['extra'];

            // 컬럼 타입을 Laravel Blueprint 형식으로 변환
            $blueprintColumn = $this->createColumn($table, $columnName, $columnType, $isNullable, $defaultValue, $extra);

            // Primary Key 설정
            if ($isPrimary) {
                $blueprintColumn->primary();
            }
        }

        // UUID 컬럼 추가 (중복 방지용)
        $table->uuid('shard_uuid')->unique()->after('id');

        // 인덱스 추가 (users 테이블 기준)
        if ($originalTableName === 'users') {
            $table->index('email');
            $table->index('created_at');
            $table->index('updated_at');
            $table->index('shard_uuid');
        }
    }

        /**
     * 컬럼을 생성합니다.
     */
    private function createColumn(Blueprint $table, string $name, string $type, bool $nullable, $default, string $extra)
    {
        // 데이터베이스 타입을 Laravel Blueprint 타입으로 변환
        $blueprintType = $this->convertMySqlTypeToBlueprint($type);

        // varchar 길이 처리
        if (str_contains(strtolower($type), 'varchar')) {
            preg_match('/varchar\((\d+)\)/', $type, $matches);
            $length = $matches[1] ?? 255;
            $column = $table->string($name, $length);
        }
        // UUID 타입 처리
        elseif ($blueprintType === 'uuid') {
            $column = $table->uuid($name);
        }
        // decimal 타입 처리
        elseif (str_contains($blueprintType, 'decimal')) {
            preg_match('/decimal\((\d+),(\d+)\)/', $type, $matches);
            $precision = $matches[1] ?? 8;
            $scale = $matches[2] ?? 2;
            $column = $table->decimal($name, $precision, $scale);
        }
        // 기타 타입 처리
        else {
            switch ($blueprintType) {
                case 'bigInteger':
                    $column = $table->bigInteger($name);
                    break;
                case 'integer':
                    $column = $table->integer($name);
                    break;
                case 'smallInteger':
                    $column = $table->smallInteger($name);
                    break;
                case 'tinyInteger':
                    $column = $table->tinyInteger($name);
                    break;
                case 'text':
                    $column = $table->text($name);
                    break;
                case 'longText':
                    $column = $table->longText($name);
                    break;
                case 'mediumText':
                    $column = $table->mediumText($name);
                    break;
                case 'dateTime':
                    $column = $table->dateTime($name);
                    break;
                case 'timestamp':
                    $column = $table->timestamp($name);
                    break;
                case 'date':
                    $column = $table->date($name);
                    break;
                case 'time':
                    $column = $table->time($name);
                    break;
                case 'float':
                    $column = $table->float($name);
                    break;
                case 'double':
                    $column = $table->double($name);
                    break;
                case 'json':
                    $column = $table->json($name);
                    break;
                case 'binary':
                    $column = $table->binary($name);
                    break;
                default:
                    $column = $table->string($name);
                    break;
            }
        }

        if ($nullable) {
            $column->nullable();
        }

        if ($default !== null && $default !== 'NULL') {
            $column->default($default);
        }

        if ($extra === 'auto_increment') {
            $column->autoIncrement();
        }

        return $column;
    }

    /**
     * 데이터베이스 타입을 Laravel Blueprint 타입으로 변환합니다.
     */
    private function convertMySqlTypeToBlueprint(string $databaseType): string
    {
        if ($this->isSqlite()) {
            return $this->convertSqliteTypeToBlueprint($databaseType);
        } elseif ($this->isMysql()) {
            return $this->convertMysqlTypeToBlueprint($databaseType);
        } elseif ($this->isPostgresql()) {
            return $this->convertPostgresqlTypeToBlueprint($databaseType);
        } else {
            // 기본적으로 MySQL 방식 사용
            return $this->convertMysqlTypeToBlueprint($databaseType);
        }
    }

    /**
     * SQLite 타입을 Laravel Blueprint 타입으로 변환합니다.
     */
    private function convertSqliteTypeToBlueprint(string $sqliteType): string
    {
        $type = strtolower($sqliteType);

        if (str_contains($type, 'int')) {
            if (str_contains($type, 'bigint')) return 'bigInteger';
            if (str_contains($type, 'tinyint')) return 'tinyInteger';
            if (str_contains($type, 'smallint')) return 'smallInteger';
            return 'integer';
        }

        if (str_contains($type, 'text')) {
            if (str_contains($type, 'longtext')) return 'longText';
            if (str_contains($type, 'mediumtext')) return 'mediumText';
            return 'text';
        }

        if (str_contains($type, 'real')) return 'float';
        if (str_contains($type, 'blob')) return 'binary';
        if (str_contains($type, 'datetime')) return 'dateTime';
        if (str_contains($type, 'timestamp')) return 'timestamp';
        if (str_contains($type, 'date')) return 'date';
        if (str_contains($type, 'time')) return 'time';

        return 'string';
    }



    /**
     * PostgreSQL 타입을 Laravel Blueprint 타입으로 변환합니다.
     */
    private function convertPostgresqlTypeToBlueprint(string $postgresqlType): string
    {
        $type = strtolower($postgresqlType);

        if (str_contains($type, 'int')) {
            if (str_contains($type, 'bigint')) return 'bigInteger';
            if (str_contains($type, 'smallint')) return 'smallInteger';
            return 'integer';
        }

        if (str_contains($type, 'varchar')) {
            preg_match('/varchar\((\d+)\)/', $type, $matches);
            $length = $matches[1] ?? 255;
            return "string";
        }

        if (str_contains($type, 'text')) {
            return 'text';
        }

        if (str_contains($type, 'timestamp')) return 'timestamp';
        if (str_contains($type, 'date')) return 'date';
        if (str_contains($type, 'time')) return 'time';

        if (str_contains($type, 'numeric')) {
            preg_match('/numeric\((\d+),(\d+)\)/', $type, $matches);
            $precision = $matches[1] ?? 8;
            $scale = $matches[2] ?? 2;
            return "decimal('{$name}', {$precision}, {$scale})";
        }

        if (str_contains($type, 'real')) return 'float';
        if (str_contains($type, 'double')) return 'double';

        if (str_contains($type, 'json')) return 'json';
        if (str_contains($type, 'bytea')) return 'binary';

        return 'string';
    }

    /**
     * 데이터베이스별 테이블 존재 여부를 확인합니다.
     */
    public function tableExists(string $tableName): bool
    {
        if ($this->isSqlite()) {
            return $this->tableExistsForSqlite($tableName);
        } elseif ($this->isMysql()) {
            return $this->tableExistsForMysql($tableName);
        } elseif ($this->isPostgresql()) {
            return $this->tableExistsForPostgresql($tableName);
        } else {
            // 기본적으로 MySQL 방식 사용
            return $this->tableExistsForMysql($tableName);
        }
    }

    /**
     * SQLite용 테이블 존재 여부 확인
     */
    private function tableExistsForSqlite(string $tableName): bool
    {
        $result = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name=?", [$tableName]);
        return !empty($result);
    }

    /**
     * MySQL용 테이블 존재 여부 확인
     */
    private function tableExistsForMysql(string $tableName): bool
    {
        $result = DB::select("SHOW TABLES LIKE ?", [$tableName]);
        return !empty($result);
    }

    /**
     * PostgreSQL용 테이블 존재 여부 확인
     */
    private function tableExistsForPostgresql(string $tableName): bool
    {
        $result = DB::select("SELECT tablename FROM pg_tables WHERE tablename = ?", [$tableName]);
        return !empty($result);
    }

    /**
     * 데이터베이스별 테이블 레코드 수를 가져옵니다.
     */
    public function getTableRecordCount(string $tableName): int
    {
        if ($this->isSqlite()) {
            return $this->getTableRecordCountForSqlite($tableName);
        } elseif ($this->isMysql()) {
            return $this->getTableRecordCountForMysql($tableName);
        } elseif ($this->isPostgresql()) {
            return $this->getTableRecordCountForPostgresql($tableName);
        } else {
            // 기본적으로 MySQL 방식 사용
            return $this->getTableRecordCountForMysql($tableName);
        }
    }

    /**
     * SQLite용 테이블 레코드 수 가져오기
     */
    private function getTableRecordCountForSqlite(string $tableName): int
    {
        $result = DB::select("SELECT COUNT(*) as count FROM {$tableName}");
        return $result[0]->count ?? 0;
    }

    /**
     * MySQL용 테이블 레코드 수 가져오기
     */
    private function getTableRecordCountForMysql(string $tableName): int
    {
        $result = DB::select("SELECT COUNT(*) as count FROM {$tableName}");
        return $result[0]->count ?? 0;
    }

    /**
     * PostgreSQL용 테이블 레코드 수 가져오기
     */
    private function getTableRecordCountForPostgresql(string $tableName): int
    {
        $result = DB::select("SELECT COUNT(*) as count FROM {$tableName}");
        return $result[0]->count ?? 0;
    }

    /**
     * 사용자 샤딩 설정을 가져옵니다.
     */
    public function getUserShardingConfig(string $tableName): ?UserShardingConfig
    {
        return UserShardingConfig::getUserShardingConfig($tableName);
    }

        /**
     * 샤드 테이블 이름을 가져옵니다.
     */
    public function getShardTableName(string $tableName, $value): ?string
    {
        $config = $this->getUserShardingConfig($tableName);

        if (!$config) {
            return $tableName; // 샤딩이 설정되지 않은 경우 원본 테이블 반환
        }

        $shardId = $config->calculateShardId($value);
        return $config->getShardTableName($shardId);
    }

    /**
     * 사용자 샤딩 설정을 비활성화합니다.
     */
    public function disableUserSharding(string $tableName): bool
    {
        return UserShardingConfig::where('table_name', $tableName)
            ->update(['is_active' => false]) > 0;
    }

    /**
     * 사용자 샤딩 설정 목록을 가져옵니다.
     */
    public function getUserShardingConfigs(): \Illuminate\Database\Eloquent\Collection
    {
        return UserShardingConfig::orderBy('table_name')->get();
    }

    /**
     * 사용 가능한 샤드 목록을 가져옵니다.
     */
    public function getAvailableShards(): array
    {
        $configs = $this->getUserShardingConfigs();
        $shards = [];

        foreach ($configs as $config) {
            if ($config->is_active) {
                for ($i = 0; $i < $config->shard_count; $i++) {
                    $shards[] = "shard{$i}";
                }
            }
        }

        return array_unique($shards);
    }

    /**
     * 샤드 ID를 계산합니다.
     */
    public function calculateShardId(string $tableName, $value): int
    {
        $config = $this->getUserShardingConfig($tableName);

        if (!$config) {
            return 0; // 샤딩이 설정되지 않은 경우 기본값
        }

        return $config->calculateShardId($value);
    }

    /**
     * 샤드 테이블에서 사용자를 생성합니다.
     *
     * @param array $userData 사용자 데이터
     * @return array ['success' => bool, 'message' => string] 성공 여부와 메시지
     */
    public function createUser(array $userData): array
    {
        $email = $userData['email'];
        $shardTableName = $this->getShardTableName('users', $email);

        // 이메일 중복 확인
        $existingUser = $this->findUserByEmail($email);
        if ($existingUser) {
            return [
                'success' => false,
                'message' => "이미 등록된 이메일입니다: {$email}"
            ];
        }

        // UUID 자동 생성
        if (!isset($userData['id'])) {
            $userData['id'] = (string) \Illuminate\Support\Str::uuid();
        }

        // 샤드 UUID 자동 생성 (중복 방지용)
        $userData['shard_uuid'] = (string) \Illuminate\Support\Str::uuid();

        try {
            DB::table($shardTableName)->insert($userData);
            return [
                'success' => true,
                'message' => '사용자가 성공적으로 생성되었습니다.'
            ];
        } catch (\Exception $e) {
            \Log::error("사용자 생성 실패: " . $e->getMessage());
            return [
                'success' => false,
                'message' => "사용자 생성 중 오류가 발생했습니다: {$e->getMessage()}"
            ];
        }
    }

    /**
     * 이메일로 사용자를 찾습니다.
     */
    public function findUserByEmail(string $email): ?object
    {
        $shardTableName = $this->getShardTableName('users', $email);

        try {
            return DB::table($shardTableName)->where('email', $email)->first();
        } catch (\Exception $e) {
            \Log::error("사용자 조회 실패: " . $e->getMessage());
            return null;
        }
    }

    /**
     * UUID로 사용자를 찾습니다.
     */
    public function findUserById(string $id): ?object
    {
        // UUID로 샤드 계산 (UUID의 일부를 해시로 사용)
        $shardId = $this->calculateShardId('users', $id);
        $config = $this->getUserShardingConfig('users');

        if (!$config) {
            return DB::table('users')->where('id', $id)->first();
        }

        $shardTableName = $config->getShardTableName($shardId);

        try {
            return DB::table($shardTableName)->where('id', $id)->first();
        } catch (\Exception $e) {
            \Log::error("사용자 조회 실패: " . $e->getMessage());
            return null;
        }
    }

    /**
     * 사용자 비밀번호를 업데이트합니다.
     */
    public function updateUserPassword(string $email, string $newPassword): bool
    {
        $shardTableName = $this->getShardTableName('users', $email);

        try {
            DB::table($shardTableName)
                ->where('email', $email)
                ->update(['password' => bcrypt($newPassword)]);
            return true;
        } catch (\Exception $e) {
            \Log::error("비밀번호 업데이트 실패: " . $e->getMessage());
            return false;
        }
    }

    /**
     * 사용자 이메일 인증 상태를 업데이트합니다.
     */
    public function updateUserEmailVerified(string $email, bool $verified = true): bool
    {
        $shardTableName = $this->getShardTableName('users', $email);

        try {
            DB::table($shardTableName)
                ->where('email', $email)
                ->update(['email_verified_at' => $verified ? now() : null]);
            return true;
        } catch (\Exception $e) {
            \Log::error("이메일 인증 상태 업데이트 실패: " . $e->getMessage());
            return false;
        }
    }

    /**
     * 사용자를 삭제합니다.
     */
    public function deleteUser(string $email): bool
    {
        $shardTableName = $this->getShardTableName('users', $email);

        try {
            DB::table($shardTableName)->where('email', $email)->delete();
            return true;
        } catch (\Exception $e) {
            \Log::error("사용자 삭제 실패: " . $e->getMessage());
            return false;
        }
    }

    /**
     * 특정 샤드에서 사용자를 삭제합니다.
     */
    public function deleteUserFromShard(string $userId, string $shard): bool
    {
        $shardId = (int) str_replace('shard', '', $shard);
        $shardTableName = "users_shard_{$shardId}";

        try {
            DB::table($shardTableName)->where('id', $userId)->delete();
            return true;
        } catch (\Exception $e) {
            \Log::error("샤드에서 사용자 삭제 실패: " . $e->getMessage());
            return false;
        }
    }

    /**
     * 사용자의 마지막 로그인 시간을 업데이트합니다.
     */
    public function updateUserLastLogin(string $email): bool
    {
        $shardTableName = $this->getShardTableName('users', $email);

        try {
            DB::table($shardTableName)
                ->where('email', $email)
                ->update([
                    'last_login_at' => now(),
                    'last_login_ip' => request()->ip(),
                    'login_count' => DB::raw('login_count + 1')
                ]);
            return true;
        } catch (\Exception $e) {
            \Log::error("마지막 로그인 시간 업데이트 실패: " . $e->getMessage());
            return false;
        }
    }

    /**
     * 특정 샤드에서 이메일로 사용자를 찾습니다.
     */
    public function findUserByEmailInShard(string $email, string $shard): ?object
    {
        $shardId = (int) str_replace('shard', '', $shard);
        $shardTableName = "users_shard_{$shardId}";

        try {
            return DB::table($shardTableName)->where('email', $email)->first();
        } catch (\Exception $e) {
            \Log::error("샤드에서 사용자 조회 실패: " . $e->getMessage());
            return null;
        }
    }

    /**
     * 특정 샤드에서 사용자 목록을 가져옵니다.
     */
    public function getUsersFromShard(string $shard, string $status = null): \Illuminate\Support\Collection
    {
        $shardId = (int) str_replace('shard', '', $shard);
        $shardTableName = "users_shard_{$shardId}";

        try {
            $query = DB::table($shardTableName);

            if ($status) {
                switch ($status) {
                    case 'active':
                        $query->where('is_active', true);
                        break;
                    case 'inactive':
                        $query->where('is_active', false);
                        break;
                    case 'dormant':
                        $query->where('last_login_at', '<', now()->subDays(365));
                        break;
                }
            }

            return $query->get();
        } catch (\Exception $e) {
            \Log::error("샤드에서 사용자 목록 조회 실패: " . $e->getMessage());
            return collect();
        }
    }

    /**
     * 특정 샤드에서 사용자 정보를 업데이트합니다.
     */
    public function updateUserInShard(string $userId, array $updateData, string $shard): bool
    {
        $shardId = (int) str_replace('shard', '', $shard);
        $shardTableName = "users_shard_{$shardId}";

        try {
            DB::table($shardTableName)
                ->where('id', $userId)
                ->update($updateData);
            return true;
        } catch (\Exception $e) {
            \Log::error("샤드에서 사용자 업데이트 실패: " . $e->getMessage());
            return false;
        }
    }

    /**
     * 특정 샤드의 인증 통계를 가져옵니다.
     */
    public function getAuthStats(string $shard): array
    {
        $shardId = (int) str_replace('shard', '', $shard);
        $shardTableName = "users_shard_{$shardId}";

        try {
            $stats = [
                'total_users' => DB::table($shardTableName)->count(),
                'active_users' => DB::table($shardTableName)->where('is_active', true)->count(),
                'inactive_users' => DB::table($shardTableName)->where('is_active', false)->count(),
                'admin_users' => DB::table($shardTableName)->where('is_admin', true)->count(),
                'verified_users' => DB::table($shardTableName)->whereNotNull('email_verified_at')->count(),
                'recent_logins' => DB::table($shardTableName)
                    ->where('last_login_at', '>=', now()->subDay())
                    ->count(),
                'shard_info' => [
                    'name' => $shard,
                    'database' => config("database.connections.{$shard}.database"),
                    'connection' => $shard
                ]
            ];

            return $stats;
        } catch (\Exception $e) {
            \Log::error("샤드 통계 조회 실패: " . $e->getMessage());
            return [
                'total_users' => 0,
                'active_users' => 0,
                'inactive_users' => 0,
                'admin_users' => 0,
                'verified_users' => 0,
                'recent_logins' => 0,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * 모든 샤드에서 사용자를 검색합니다.
     */
    public function searchUsers(array $conditions = []): array
    {
        $config = $this->getUserShardingConfig('users');

        if (!$config) {
            // 샤딩이 설정되지 않은 경우 원본 테이블에서 검색
            $query = DB::table('users');
            foreach ($conditions as $key => $value) {
                $query->where($key, $value);
            }
            return $query->get()->toArray();
        }

        $allUsers = [];

        // 모든 샤드에서 검색
        for ($i = 0; $i < $config->shard_count; $i++) {
            $shardTableName = $config->getShardTableName($i);

            if (Schema::hasTable($shardTableName)) {
                try {
                    $query = DB::table($shardTableName);
                    foreach ($conditions as $key => $value) {
                        $query->where($key, $value);
                    }
                    $users = $query->get();
                    $allUsers = array_merge($allUsers, $users->toArray());
                } catch (\Exception $e) {
                    \Log::error("샤드 {$shardTableName} 검색 실패: " . $e->getMessage());
                }
            }
        }

        return $allUsers;
    }

    /**
     * 휴면 사용자를 찾습니다.
     */
    public function findDormantUsers(int $days = 365): array
    {
        $config = $this->getUserShardingConfig('users');

        if (!$config) {
            // 샤딩이 설정되지 않은 경우 원본 테이블에서 검색
            return DB::table('users')
                ->where('last_login_at', '<', now()->subDays($days))
                ->orWhereNull('last_login_at')
                ->get()
                ->toArray();
        }

        $dormantUsers = [];

        // 모든 샤드에서 휴면 사용자 검색
        for ($i = 0; $i < $config->shard_count; $i++) {
            $shardTableName = $config->getShardTableName($i);

            if (Schema::hasTable($shardTableName)) {
                try {
                    $users = DB::table($shardTableName)
                        ->where('last_login_at', '<', now()->subDays($days))
                        ->orWhereNull('last_login_at')
                        ->get();
                    $dormantUsers = array_merge($dormantUsers, $users->toArray());
                } catch (\Exception $e) {
                    \Log::error("샤드 {$shardTableName} 휴면 사용자 검색 실패: " . $e->getMessage());
                }
            }
        }

        return $dormantUsers;
    }

    /**
     * 기본 테이블 상태를 확인합니다.
     */
    public function checkBaseTableStatus(string $tableName): array
    {
        $status = [
            'table_exists' => false,
            'record_count' => 0,
            'has_sharding_config' => false,
            'sharding_active' => false,
            'shard_tables_exist' => [],
            'can_migrate' => false,
        ];

        // 기본 테이블 존재 여부 확인 (데이터베이스별 분기 처리)
        $status['table_exists'] = $this->tableExists($tableName);

        if ($status['table_exists']) {
            // 레코드 수 확인 (데이터베이스별 분기 처리)
            $status['record_count'] = $this->getTableRecordCount($tableName);

            // 샤딩 설정 확인
            $config = $this->getUserShardingConfig($tableName);
            $status['has_sharding_config'] = $config !== null;
            $status['sharding_active'] = $config && $config->is_active;

            if ($config) {
                // 샤드 테이블 존재 여부 확인 (데이터베이스별 분기 처리)
                for ($i = 0; $i < $config->shard_count; $i++) {
                    $shardTableName = $config->getShardTableName($i);
                    $status['shard_tables_exist'][$shardTableName] = $this->tableExists($shardTableName);
                }
            }

            // 마이그레이션 가능 여부 확인
            $status['can_migrate'] = $status['table_exists'] &&
                                   $status['has_sharding_config'] &&
                                   $status['sharding_active'] &&
                                   $status['record_count'] > 0;
        }

        return $status;
    }

    /**
     * 기존 데이터를 샤드 테이블로 마이그레이션합니다.
     */
    public function migrateExistingData(string $tableName, callable $progressCallback = null): array
    {
        $status = $this->checkBaseTableStatus($tableName);

        if (!$status['can_migrate']) {
            return [
                'success' => false,
                'message' => '마이그레이션을 수행할 수 없습니다. 기본 테이블 상태를 확인하세요.',
                'status' => $status
            ];
        }

        $config = $this->getUserShardingConfig($tableName);
        $migratedCount = 0;
        $errorCount = 0;
        $errors = [];

        try {
            // 기존 데이터를 배치로 처리
            DB::table($tableName)->orderBy('id')->chunk(1000, function ($records) use ($config, &$migratedCount, &$errorCount, &$errors, $progressCallback) {
                foreach ($records as $record) {
                    try {
                        // 샤드 ID 계산
                        $shardId = $config->calculateShardId($record->id);
                        $shardTableName = $config->getShardTableName($shardId);

                        // 레코드 데이터 준비 (UUID 추가)
                        $recordData = (array) $record;
                        $recordData['shard_uuid'] = (string) \Illuminate\Support\Str::uuid();

                        // 샤드 테이블에 데이터 삽입
                        DB::table($shardTableName)->insert($recordData);
                        $migratedCount++;

                        if ($progressCallback) {
                            $progressCallback($migratedCount);
                        }
                    } catch (\Exception $e) {
                        $errorCount++;
                        $errors[] = "레코드 ID {$record->id}: " . $e->getMessage();
                    }
                }
            });

            return [
                'success' => true,
                'migrated_count' => $migratedCount,
                'error_count' => $errorCount,
                'errors' => $errors,
                'message' => "총 {$migratedCount}개 레코드가 마이그레이션되었습니다."
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => '마이그레이션 중 오류가 발생했습니다: ' . $e->getMessage(),
                'migrated_count' => $migratedCount,
                'error_count' => $errorCount,
                'errors' => $errors
            ];
        }
    }

    /**
     * 샤딩 활성화 후 기본 테이블을 백업 테이블로 변경합니다.
     */
    public function backupBaseTable(string $tableName): bool
    {
        $backupTableName = $tableName . '_backup_' . date('Y_m_d_H_i_s');

        try {
            if ($this->isSqlite()) {
                return $this->backupTableForSqlite($tableName, $backupTableName);
            } elseif ($this->isMysql()) {
                return $this->backupTableForMysql($tableName, $backupTableName);
            } elseif ($this->isPostgresql()) {
                return $this->backupTableForPostgresql($tableName, $backupTableName);
            } else {
                // 기본적으로 MySQL 방식 사용
                return $this->backupTableForMysql($tableName, $backupTableName);
            }
        } catch (\Exception $e) {
            \Log::error("테이블 백업 실패: " . $e->getMessage());
            return false;
        }
    }

    /**
     * SQLite용 테이블 백업을 수행합니다.
     */
    private function backupTableForSqlite(string $tableName, string $backupTableName): bool
    {
        try {
            DB::statement("CREATE TABLE {$backupTableName} AS SELECT * FROM {$tableName}");
            \Log::info("SQLite 테이블 {$tableName}이 {$backupTableName}으로 백업되었습니다.");
            return true;
        } catch (\Exception $e) {
            \Log::error("SQLite 테이블 백업 실패: " . $e->getMessage());
            return false;
        }
    }

    /**
     * MySQL용 테이블 백업을 수행합니다.
     */
    private function backupTableForMysql(string $tableName, string $backupTableName): bool
    {
        try {
            DB::statement("CREATE TABLE {$backupTableName} LIKE {$tableName}");
            DB::statement("INSERT INTO {$backupTableName} SELECT * FROM {$tableName}");
            \Log::info("MySQL 테이블 {$tableName}이 {$backupTableName}으로 백업되었습니다.");
            return true;
        } catch (\Exception $e) {
            \Log::error("MySQL 테이블 백업 실패: " . $e->getMessage());
            return false;
        }
    }

    /**
     * PostgreSQL용 테이블 백업을 수행합니다.
     */
    private function backupTableForPostgresql(string $tableName, string $backupTableName): bool
    {
        try {
            DB::statement("CREATE TABLE {$backupTableName} AS SELECT * FROM {$tableName}");
            \Log::info("PostgreSQL 테이블 {$tableName}이 {$backupTableName}으로 백업되었습니다.");
            return true;
        } catch (\Exception $e) {
            \Log::error("PostgreSQL 테이블 백업 실패: " . $e->getMessage());
            return false;
        }
    }

    /**
     * 샤딩 설정을 완전히 활성화합니다 (기본 테이블 백업 포함).
     */
    public function activateShardingWithMigration(string $tableName, int $shardCount, string $shardKey = 'id', string $strategy = 'hash', string $description = null): array
    {
        // 1. 샤딩 설정 생성
        $config = $this->createUserSharding($tableName, $shardCount, $shardKey, $strategy, $description);

        // 2. 기본 테이블 상태 확인
        $status = $this->checkBaseTableStatus($tableName);

        if (!$status['table_exists'] || $status['record_count'] === 0) {
            return [
                'success' => true,
                'message' => '샤딩이 설정되었습니다. 기본 테이블에 데이터가 없어 마이그레이션이 필요하지 않습니다.',
                'config' => $config
            ];
        }

        // 3. 기존 데이터 마이그레이션
        $migrationResult = $this->migrateExistingData($tableName);

        if (!$migrationResult['success']) {
            // 마이그레이션 실패 시 샤딩 비활성화
            $this->disableUserSharding($tableName);
            return [
                'success' => false,
                'message' => '마이그레이션 실패로 샤딩이 비활성화되었습니다.',
                'migration_result' => $migrationResult
            ];
        }

        // 4. 기본 테이블 백업
        $backupSuccess = $this->backupBaseTable($tableName);

        return [
            'success' => true,
            'message' => '샤딩이 성공적으로 활성화되었습니다.',
            'config' => $config,
            'migration_result' => $migrationResult,
            'backup_created' => $backupSuccess
        ];
    }

    /**
     * 샤딩 설정을 비활성화하고 기본 테이블로 복원합니다.
     */
    public function deactivateShardingAndRestore(string $tableName): array
    {
        $config = $this->getUserShardingConfig($tableName);

        if (!$config) {
            return [
                'success' => false,
                'message' => '활성화된 샤딩 설정이 없습니다.'
            ];
        }

        try {
            // 모든 샤드 데이터를 기본 테이블로 복원
            $restoredCount = 0;

            for ($i = 0; $i < $config->shard_count; $i++) {
                $shardTableName = $config->getShardTableName($i);

                if (Schema::hasTable($shardTableName)) {
                    $count = DB::table($shardTableName)->count();
                    DB::table($tableName)->insertUsing(
                        DB::table($shardTableName)->get()->map(function ($item) {
                            return (array) $item;
                        })->toArray()
                    );
                    $restoredCount += $count;
                }
            }

            // 샤딩 비활성화
            $this->disableUserSharding($tableName);

            return [
                'success' => true,
                'message' => "샤딩이 비활성화되었습니다. {$restoredCount}개 레코드가 기본 테이블로 복원되었습니다.",
                'restored_count' => $restoredCount
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => '샤딩 비활성화 중 오류가 발생했습니다: ' . $e->getMessage()
            ];
        }
    }
}
