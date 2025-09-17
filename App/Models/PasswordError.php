<?php

namespace Jiny\Auth\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

/**
 * UserPasswordError 모델
 *
 * 사용자 비밀번호 오류 시도를 추적하고 관리하는 모델
 * Brute Force 공격 방지를 위한 오류 횟수 제한과 계정 잠금 기능을 지원합니다.
 *
 * @package Jiny\Auth\App\Models
 * @author JinyPHP
 * @version 1.0.0
 * @since 1.0.0
 * @license MIT
 */
class UserPasswordError extends Model
{
    use HasFactory;

    /**
     * 팩토리 네임스페이스
     */
    protected static function newFactory()
    {
        return \Database\Factories\UserPasswordErrorFactory::new();
    }

    /**
     * 테이블명
     */
    protected $table = 'user_password_errors';

    /**
     * 대량 할당 가능한 속성들
     */
    protected $fillable = [
        'user_id',
        'email',
        'ip_address',
        'user_agent',
        'location',
        'error_type',
        'error_message',
        'consecutive_errors',
        'total_errors',
        'locked_at',
        'unlocked_at',
        'unlocked_by',
        'unlock_reason',
        'status',
        'metadata'
    ];

    /**
     * 캐스팅할 속성들
     */
    protected $casts = [
        'locked_at' => 'datetime',
        'unlocked_at' => 'datetime',
        'metadata' => 'array',
        'consecutive_errors' => 'integer',
        'total_errors' => 'integer'
    ];

    /**
     * 오류 유형 상수들
     */
    const ERROR_TYPE_WRONG_PASSWORD = 'wrong_password';
    const ERROR_TYPE_ACCOUNT_NOT_FOUND = 'account_not_found';
    const ERROR_TYPE_ACCOUNT_LOCKED = 'account_locked';
    const ERROR_TYPE_ACCOUNT_DISABLED = 'account_disabled';
    const ERROR_TYPE_EMAIL_NOT_VERIFIED = 'email_not_verified';
    const ERROR_TYPE_APPROVAL_PENDING = 'approval_pending';

    /**
     * 상태 상수들
     */
    const STATUS_ACTIVE = 'active';
    const STATUS_LOCKED = 'locked';
    const STATUS_UNLOCKED = 'unlocked';

    /**
     * 사용자와의 관계
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 잠금 해제한 관리자와의 관계
     */
    public function unlockedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'unlocked_by');
    }

    /**
     * 계정이 잠금되었는지 확인
     */
    public function isLocked(): bool
    {
        return $this->status === self::STATUS_LOCKED && $this->locked_at !== null;
    }

    /**
     * 계정이 영구 잠금되었는지 확인
     */
    public function isPermanentlyLocked(): bool
    {
        return $this->status === self::STATUS_LOCKED &&
               $this->consecutive_errors >= config('admin.auth.login.permanent_lockout_attempts', 25);
    }

    /**
     * 잠금 시간이 만료되었는지 확인
     */
    public function isLockoutExpired(): bool
    {
        if (!$this->locked_at) {
            return true;
        }

        $lockoutTime = config('admin.auth.login.lockout_time', 15);
        return $this->locked_at->addMinutes($lockoutTime)->isPast();
    }

    /**
     * 잠금 해제 가능한지 확인
     */
    public function canBeUnlocked(): bool
    {
        return $this->status === self::STATUS_LOCKED && !$this->isPermanentlyLocked();
    }

    /**
     * 특정 이메일의 최근 오류 기록 조회
     */
    public static function getRecentErrorsByEmail(string $email, int $hours = 24): \Illuminate\Database\Eloquent\Collection
    {
        return self::where('email', $email)
            ->where('created_at', '>=', Carbon::now()->subHours($hours))
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * 특정 IP의 최근 오류 기록 조회
     */
    public static function getRecentErrorsByIp(string $ip, int $hours = 24): \Illuminate\Database\Eloquent\Collection
    {
        return self::where('ip_address', $ip)
            ->where('created_at', '>=', Carbon::now()->subHours($hours))
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * 특정 이메일의 연속 오류 횟수 조회
     */
    public static function getConsecutiveErrorsByEmail(string $email): int
    {
        $latestError = self::where('email', $email)
            ->where('created_at', '>=', Carbon::now()->subHours(24))
            ->orderBy('created_at', 'desc')
            ->first();

        return $latestError ? $latestError->consecutive_errors : 0;
    }

    /**
     * 특정 이메일의 총 오류 횟수 조회
     */
    public static function getTotalErrorsByEmail(string $email, int $hours = 24): int
    {
        return self::where('email', $email)
            ->where('created_at', '>=', Carbon::now()->subHours($hours))
            ->sum('total_errors');
    }

    /**
     * 잠금된 계정 목록 조회
     */
    public static function getLockedAccounts(): \Illuminate\Database\Eloquent\Collection
    {
        return self::where('status', self::STATUS_LOCKED)
            ->whereNotNull('locked_at')
            ->with('user')
            ->orderBy('locked_at', 'desc')
            ->get();
    }

    /**
     * 영구 잠금된 계정 목록 조회
     */
    public static function getPermanentlyLockedAccounts(): \Illuminate\Database\Eloquent\Collection
    {
        return self::where('status', self::STATUS_LOCKED)
            ->where('consecutive_errors', '>=', config('admin.auth.login.permanent_lockout_attempts', 25))
            ->with('user')
            ->orderBy('locked_at', 'desc')
            ->get();
    }

    /**
     * 계정 잠금
     */
    public function lockAccount(?string $reason = null): void
    {
        $this->update([
            'status' => self::STATUS_LOCKED,
            'locked_at' => Carbon::now(),
            'error_message' => $reason ?? '로그인 시도 횟수 초과로 인한 계정 잠금'
        ]);
    }

    /**
     * 계정 잠금 해제
     */
    public function unlockAccount(int $unlockedBy, ?string $reason = null): void
    {
        $this->update([
            'status' => self::STATUS_UNLOCKED,
            'unlocked_at' => Carbon::now(),
            'unlocked_by' => $unlockedBy,
            'unlock_reason' => $reason ?? '관리자에 의한 잠금 해제'
        ]);
    }

    /**
     * 오류 시도 기록 생성
     */
    public static function recordError(array $data): self
    {
        $email = $data['email'];
        $ip = $data['ip_address'];

        // 기존 오류 기록 조회 (24시간 이내)
        $latestError = self::where('email', $email)
            ->where('created_at', '>=', Carbon::now()->subHours(24))
            ->orderBy('created_at', 'desc')
            ->first();

        $consecutiveErrors = $latestError ? $latestError->consecutive_errors + 1 : 1;

        // 이미 잠금된 상태라면 기존 기록 업데이트
        if ($latestError && $latestError->status === self::STATUS_LOCKED) {
            $latestError->update([
                'consecutive_errors' => $consecutiveErrors,
                'updated_at' => Carbon::now()
            ]);
            return $latestError;
        }

        // 오류 기록 생성
        return self::create([
            'user_id' => $data['user_id'] ?? null,
            'email' => $email,
            'ip_address' => $ip,
            'user_agent' => $data['user_agent'] ?? null,
            'location' => $data['location'] ?? null,
            'error_type' => $data['error_type'] ?? self::ERROR_TYPE_WRONG_PASSWORD,
            'error_message' => $data['error_message'] ?? '잘못된 비밀번호',
            'consecutive_errors' => $consecutiveErrors,
            'total_errors' => $data['total_errors'] ?? 1,
            'status' => self::STATUS_ACTIVE,
            'metadata' => $data['metadata'] ?? []
        ]);
    }

    /**
     * 스코프: 잠금된 계정만
     */
    public function scopeLocked($query)
    {
        return $query->where('status', self::STATUS_LOCKED);
    }

    /**
     * 스코프: 영구 잠금된 계정만
     */
    public function scopePermanentlyLocked($query)
    {
        return $query->where('status', self::STATUS_LOCKED)
            ->where('consecutive_errors', '>=', config('admin.auth.login.permanent_lockout_attempts', 25));
    }

    /**
     * 스코프: 최근 24시간 내 오류만
     */
    public function scopeRecent($query, $hours = 24)
    {
        return $query->where('created_at', '>=', Carbon::now()->subHours($hours));
    }
}
