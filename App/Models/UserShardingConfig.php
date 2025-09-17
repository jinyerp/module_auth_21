<?php

namespace Jiny\Auth\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

// 관계 모델들
use Jiny\Auth\App\Models\User;

class UserShardingConfig extends Model
{
    use HasFactory;

    /**
     * 사용자 샤딩 설정 테이블명
     */
    protected $table = 'user_sharding_configs';

    protected $fillable = [
        'table_name',
        'shard_count',
        'shard_key',
        'shard_strategy',
        'is_active',
        'description',
        'created_by',
        'updated_by',
        'config_uuid'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'shard_count' => 'integer',
    ];

    /**
     * 모델 생성 시 UUID 자동 생성
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->config_uuid)) {
                $model->config_uuid = Str::uuid();
            }
        });
    }

    /**
     * 사용자 샤딩 설정을 가져옵니다.
     */
    public static function getUserShardingConfig(string $tableName): ?self
    {
        return static::where('table_name', $tableName)
            ->where('table_name', $tableName)
            ->where('is_active', true)
            ->first();
    }

    /**
     * 샤딩 테이블 이름을 생성합니다.
     */
    public function getShardTableName(int $shardId): string
    {
        return $this->table_name . '_shard_' . $shardId;
    }

    /**
     * 샤드 ID를 계산합니다.
     */
    public function calculateShardId($value): int
    {
        if ($this->shard_strategy === 'hash') {
            return abs(crc32($value)) % $this->shard_count;
        } elseif ($this->shard_strategy === 'range') {
            // 범위 기반 샤딩 (예: ID 기반)
            return (int)($value % $this->shard_count);
        }

        return 0;
    }

    /**
     * 모든 샤드 테이블 이름을 가져옵니다.
     */
    public function getAllShardTableNames(): array
    {
        $tableNames = [];
        for ($i = 0; $i < $this->shard_count; $i++) {
            $tableNames[] = $this->getShardTableName($i);
        }
        return $tableNames;
    }

    /**
     * 샤딩 설정의 상태를 확인합니다.
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * 샤딩 전략의 설명을 가져옵니다.
     */
    public function getStrategyDescription(): string
    {
        return match($this->shard_strategy) {
            'hash' => '해시 기반 (균등 분배)',
            'range' => '범위 기반 (순차 분배)',
            default => '알 수 없음'
        };
    }

    /**
     * 샤딩 설정의 요약 정보를 가져옵니다.
     */
    public function getSummary(): string
    {
        return "{$this->table_name} 테이블을 {$this->shard_count}개 샤드로 분할 ({$this->getStrategyDescription()})";
    }
}
