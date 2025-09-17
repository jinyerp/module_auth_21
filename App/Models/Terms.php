<?php

namespace Jiny\Auth\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

// 관계 모델들
use Jiny\Auth\App\Models\UserTermLog;
use Jiny\Auth\App\Models\User;

/**
 * 사용자 약관 모델
 *
 * 이 모델은 시스템의 모든 약관 정보를 관리합니다.
 *
 * 도메인 지식:
 * - 이용약관, 개인정보처리방침, 마케팅 수신 동의 등 다양한 약관 관리
 * - 약관 버전 관리 및 시행일/만료일 관리로 법적 요구사항 준수
 * - 필수/선택 약관 분류로 사용자 경험 최적화
 * - 약관 활성화 상태 및 표시 순서 관리
 * - 약관 동의 통계 정보로 분석 및 보고 지원
 * - GDPR, 개인정보보호법, 전자상거래법 등 법적 준수
 * - 약관 변경 시 사용자 재동의 요구사항 대응
 * - 다국어 지원을 위한 메타데이터 구조
 */
class UserTerms extends Model
{
    use HasFactory;

    protected $table = 'user_terms';

    protected $fillable = [
        'title',                    // 약관 제목
        'slug',                     // 약관 슬러그
        'content',                  // 약관 내용
        'description',              // 약관 설명
        'type',                     // 약관 타입 (required, optional)
        'version',                  // 약관 버전
        'is_active',                // 활성화 여부
        'display_order',            // 표시 순서
        'effective_date',           // 시행일
        'expiry_date',              // 만료일
        'manager_id',               // 운영 책임자 ID
        'blade',                    // 약관 뷰 파일명
        'users',                    // 동의한 회원 수
        'enable',                   // 활성화 여부 (legacy)
        'required',                 // 필수 동의 여부 (legacy)
        'pos',                      // 약관 순서 (legacy)
        'metadata'                  // 추가 메타데이터
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'display_order' => 'integer',
        'effective_date' => 'datetime',
        'expiry_date' => 'datetime',
        'metadata' => 'array',
        'enable' => 'boolean',
        'required' => 'boolean',
        'users' => 'integer',
        'pos' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * 활성화된 약관 조회
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                    ->where('enable', 1)
                    ->where(function($q) {
                        $q->whereNull('effective_date')
                          ->orWhere('effective_date', '<=', now());
                    })
                    ->where(function($q) {
                        $q->whereNull('expiry_date')
                          ->orWhere('expiry_date', '>=', now());
                    });
    }

    /**
     * 필수 약관 조회
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRequired($query)
    {
        return $query->where('type', 'required')->orWhere('required', 1);
    }

    /**
     * 선택 약관 조회
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOptional($query)
    {
        return $query->where('type', 'optional')->orWhere('required', 0);
    }

    /**
     * 특정 유형의 약관 조회
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * 최신 버전의 약관만 조회
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeLatestVersion($query)
    {
        return $query->whereIn('id', function($subQuery) {
            $subQuery->selectRaw('MAX(id)')
                    ->from('user_terms')
                    ->groupBy('slug');
        });
    }

    /**
     * 특정 버전의 약관 조회
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
     * 약관 동의 로그 관계
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function agreementLogs()
    {
        return $this->hasMany(UserTermLog::class, 'term_id');
    }

    /**
     * 운영 책임자 관계
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    /**
     * 동일한 슬러그의 모든 버전 조회
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function versions()
    {
        return static::where('slug', $this->slug)->orderBy('version', 'desc');
    }

    /**
     * 이전 버전 조회
     *
     * @return static|null
     */
    public function previousVersion()
    {
        return static::where('slug', $this->slug)
                    ->where('version', '<', $this->version)
                    ->orderBy('version', 'desc')
                    ->first();
    }

    /**
     * 다음 버전 조회
     *
     * @return static|null
     */
    public function nextVersion()
    {
        return static::where('slug', $this->slug)
                    ->where('version', '>', $this->version)
                    ->orderBy('version', 'asc')
                    ->first();
    }

    /**
     * 최신 버전 조회
     *
     * @return static|null
     */
    public function latestVersion()
    {
        return static::where('slug', $this->slug)
                    ->orderBy('version', 'desc')
                    ->first();
    }

    /**
     * 슬러그 생성
     *
     * @param string $title
     * @return string
     */
    public static function generateSlug($title)
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $counter = 1;

        while (self::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * 버전 업데이트
     *
     * @return void
     */
    public function updateVersion()
    {
        $currentVersion = $this->version ?? '1.0';
        $versionParts = explode('.', $currentVersion);
        $versionParts[1] = (int)($versionParts[1] ?? 0) + 1;
        $this->version = implode('.', $versionParts);
        $this->save();
    }

    /**
     * 새 버전 생성
     *
     * @param array $data
     * @return static
     */
    public function createNewVersion($data = [])
    {
        $newVersion = $this->replicate();
        $newVersion->version = $this->incrementVersion();
        $newVersion->is_active = false; // 새 버전은 기본적으로 비활성화
        $newVersion->effective_date = null; // 시행일은 별도 설정 필요

        // 새 데이터로 업데이트
        foreach ($data as $key => $value) {
            if (in_array($key, $this->fillable)) {
                $newVersion->$key = $value;
            }
        }

        $newVersion->save();
        return $newVersion;
    }

    /**
     * 버전 증가
     *
     * @return string
     */
    private function incrementVersion()
    {
        $currentVersion = $this->version ?? '1.0';
        $versionParts = explode('.', $currentVersion);
        $versionParts[1] = (int)($versionParts[1] ?? 0) + 1;
        return implode('.', $versionParts);
    }

    /**
     * 약관 상태 업데이트
     *
     * @return void
     */
    public function updateStatus()
    {
        $now = now();

        if ($this->effective_date && $this->effective_date > $now) {
            $this->is_active = false;
        } elseif ($this->expiry_date && $this->expiry_date < $now) {
            $this->is_active = false;
        } else {
            $this->is_active = true;
        }

        $this->save();
    }

    /**
     * 최근 동의한 사용자 조회
     *
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function recentConsents($limit = 10)
    {
        return $this->agreementLogs()
                   ->where('agreed', true)
                   ->orderBy('agreed_at', 'desc')
                   ->limit($limit);
    }

    /**
     * 동의 통계
     *
     * @return array
     */
    public function getConsentStats()
    {
        $total = $this->agreementLogs()->count();
        $consented = $this->agreementLogs()->where('agreed', true)->count();
        $withdrawn = $this->agreementLogs()->whereNotNull('withdrawn_at')->count();

        return [
            'total' => $total,
            'consented' => $consented,
            'withdrawn' => $withdrawn,
            'consent_rate' => $total > 0 ? round(($consented / $total) * 100, 2) : 0
        ];
    }

    /**
     * 사용자가 최신 버전에 동의했는지 확인
     *
     * @param int $userId
     * @return bool
     */
    public function hasUserConsentedToLatestVersion($userId)
    {
        $latestVersion = $this->latestVersion();
        if (!$latestVersion) {
            return false;
        }

        return $latestVersion->agreementLogs()
                            ->where('user_id', $userId)
                            ->where('agreed', true)
                            ->where('version', $latestVersion->version)
                            ->whereNull('withdrawn_at')
                            ->exists();
    }

    /**
     * 사용자가 동의해야 하는 약관 목록 조회
     *
     * @param int $userId
     * @return \Illuminate\Support\Collection
     */
    public static function getRequiredConsentsForUser($userId)
    {
        $requiredTerms = static::active()->required()->latestVersion()->get();
        $termsNeedingConsent = [];

        foreach ($requiredTerms as $term) {
            if (!$term->hasUserConsentedToLatestVersion($userId)) {
                $termsNeedingConsent[] = $term;
            }
        }

        return collect($termsNeedingConsent);
    }

    /**
     * 약관 유형 라벨 반환
     *
     * @return string
     */
    public function getTypeLabelAttribute()
    {
        return [
            'required' => '필수',
            'optional' => '선택'
        ][$this->type] ?? '기타';
    }

    /**
     * 활성화 상태 라벨 반환
     *
     * @return string
     */
    public function getIsActiveLabelAttribute()
    {
        return $this->is_active ? '활성' : '비활성';
    }

    /**
     * 필수 여부 라벨 반환
     *
     * @return string
     */
    public function getRequiredLabelAttribute()
    {
        return $this->required ? '필수' : '선택';
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
            if (empty($model->slug)) {
                $model->slug = self::generateSlug($model->title);
            }
            if (empty($model->version)) {
                $model->version = '1.0';
            }
            if (empty($model->display_order)) {
                $model->display_order = self::max('display_order') + 1;
            }
        });

        static::updating(function ($model) {
            if ($model->isDirty('title') && empty($model->slug)) {
                $model->slug = self::generateSlug($model->title);
            }
        });
    }
}
