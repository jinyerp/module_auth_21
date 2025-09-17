<?php

namespace Jiny\Auth\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

// 관계 모델들
use Jiny\Auth\App\Models\UserTerms;
use Jiny\Auth\App\Models\User;

/**
 * 사용자 약관 동의 로그 모델
 *
 * 이 모델은 사용자별 약관 동의 이력을 관리합니다.
 *
 * 도메인 지식:
 * - 사용자별 약관 동의 이력 관리 (법적 요구사항 및 증거 보존)
 * - 동의 시점 및 IP 주소 기록 (법적 증거 및 보안 추적)
 * - 약관 변경 시 동의 여부 추적 (버전 관리 및 재동의 요구사항)
 * - GDPR, 개인정보보호법, 전자상거래법 등 법적 준수
 * - 동의 철회 및 거부 처리 (사용자 권리 보장)
 * - 약관 버전별 동의 이력 관리 (변경 이력 추적)
 * - 다양한 동의 방법 지원 (웹, 모바일, API, 관리자)
 * - 동의 유형별 분류 (최초 동의, 재동의, 철회)
 * - 메타데이터를 통한 확장 정보 저장 (브라우저 정보, 디바이스 정보 등)
 */
class UserTermLog extends Model
{
    use HasFactory;

    protected $table = 'user_term_logs';

    protected $fillable = [
        'user_id',                   // 사용자 ID
        'term_id',                   // 약관 ID
        'agreed',                    // 동의 여부
        'agreed_at',                 // 동의 시점
        'version',                   // 동의한 약관 버전
        'consent_type',              // 동의 유형 (initial, reconsent, withdrawal)
        'consent_method',            // 동의 방법 (web, mobile, api, admin)
        'withdrawn_at',              // 동의 철회 시점
        'ip_address',                // IP 주소
        'user_agent',                // 사용자 에이전트
        'metadata'                   // 추가 메타데이터
    ];

    protected $casts = [
        'agreed' => 'boolean',
        'agreed_at' => 'datetime',
        'withdrawn_at' => 'datetime',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * 약관 관계
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function term()
    {
        return $this->belongsTo(UserTerms::class, 'term_id');
    }

    /**
     * 사용자 관계
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * 동의한 로그만 조회
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAgreed($query)
    {
        return $query->where('agreed', true);
    }

    /**
     * 미동의한 로그만 조회
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNotAgreed($query)
    {
        return $query->where('agreed', false);
    }

    /**
     * 동의 철회한 로그만 조회
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithdrawn($query)
    {
        return $query->whereNotNull('withdrawn_at');
    }

    /**
     * 특정 동의 유형 조회
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfConsentType($query, $type)
    {
        return $query->where('consent_type', $type);
    }

    /**
     * 특정 동의 방법 조회
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $method
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfConsentMethod($query, $method)
    {
        return $query->where('consent_method', $method);
    }

    /**
     * 최근 동의 로그 조회
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $days
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('agreed_at', '>=', now()->subDays($days));
    }

    /**
     * 특정 버전의 동의 로그 조회
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $version
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfVersion($query, $version)
    {
        return $query->where('version', $version);
    }

    /**
     * 동의 기록 생성
     *
     * @param int $termId
     * @param int $userId
     * @param array $data
     * @return static
     */
    public static function createConsent($termId, $userId, $data = [])
    {
        // 기존 동의 기록이 있는지 확인
        $existingConsent = self::where('user_id', $userId)
                              ->where('term_id', $termId)
                              ->where('agreed', true)
                              ->whereNull('withdrawn_at')
                              ->first();

        if ($existingConsent) {
            // 기존 동의를 철회 처리
            $existingConsent->withdraw();
        }

        return self::create([
            'term_id' => $termId,
            'user_id' => $userId,
            'agreed' => $data['agreed'] ?? true,
            'agreed_at' => $data['agreed_at'] ?? now(),
            'version' => $data['version'] ?? null,
            'consent_type' => $data['consent_type'] ?? 'initial',
            'ip_address' => $data['ip_address'] ?? request()->ip(),
            'user_agent' => $data['user_agent'] ?? request()->userAgent(),
            'consent_method' => $data['consent_method'] ?? 'web',
            'metadata' => $data['metadata'] ?? null,
        ]);
    }

    /**
     * 동의 철회
     *
     * @return void
     */
    public function withdraw()
    {
        $this->update([
            'agreed' => false,
            'withdrawn_at' => now(),
            'consent_type' => 'withdrawal'
        ]);
    }

    /**
     * 재동의
     *
     * @return void
     */
    public function reconsent()
    {
        $this->update([
            'agreed' => true,
            'agreed_at' => now(),
            'consent_type' => 'reconsent',
            'withdrawn_at' => null
        ]);
    }

    /**
     * 사용자의 특정 약관에 대한 최신 동의 기록 조회
     *
     * @param int $userId
     * @param int $termId
     * @return static|null
     */
    public static function getLatestConsentForUser($userId, $termId)
    {
        return self::where('user_id', $userId)
                   ->where('term_id', $termId)
                   ->orderBy('agreed_at', 'desc')
                   ->first();
    }

    /**
     * 사용자의 특정 약관에 대한 모든 동의 이력 조회
     *
     * @param int $userId
     * @param int $termId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getConsentHistoryForUser($userId, $termId)
    {
        return self::where('user_id', $userId)
                   ->where('term_id', $termId)
                   ->orderBy('agreed_at', 'desc')
                   ->get();
    }

    /**
     * 사용자가 특정 버전에 동의했는지 확인
     *
     * @param int $userId
     * @param int $termId
     * @param string $version
     * @return bool
     */
    public static function hasUserConsentedToVersion($userId, $termId, $version)
    {
        return self::where('user_id', $userId)
                   ->where('term_id', $termId)
                   ->where('version', $version)
                   ->where('agreed', true)
                   ->whereNull('withdrawn_at')
                   ->exists();
    }

    /**
     * 동의 유형 라벨 반환
     *
     * @return string
     */
    public function getConsentTypeLabelAttribute()
    {
        return [
            'initial' => '최초 동의',
            'reconsent' => '재동의',
            'withdrawal' => '동의 철회'
        ][$this->consent_type] ?? '알 수 없음';
    }

    /**
     * 동의 방법 라벨 반환
     *
     * @return string
     */
    public function getConsentMethodLabelAttribute()
    {
        return [
            'web' => '웹',
            'mobile' => '모바일',
            'api' => 'API',
            'admin' => '관리자'
        ][$this->consent_method] ?? '알 수 없음';
    }

    /**
     * 동의 상태 라벨 반환
     *
     * @return string
     */
    public function getConsentStatusLabelAttribute()
    {
        if ($this->agreed) {
            return '동의함';
        } else {
            return $this->withdrawn_at ? '철회함' : '미동의';
        }
    }

    /**
     * 동의 기간 계산
     *
     * @return string|null
     */
    public function getConsentDurationAttribute()
    {
        if (!$this->agreed_at) {
            return null;
        }

        $endDate = $this->withdrawn_at ?? now();
        return $this->agreed_at->diffForHumans($endDate, true);
    }

    /**
     * 부트 메서드
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->agreed_at) && $model->agreed) {
                $model->agreed_at = now();
            }

            // 버전 정보가 없으면 약관의 현재 버전을 사용
            if (empty($model->version) && $model->term_id) {
                $term = UserTerms::find($model->term_id);
                if ($term) {
                    $model->version = $term->version;
                }
            }
        });
    }
}
