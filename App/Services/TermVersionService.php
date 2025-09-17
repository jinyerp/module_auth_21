<?php

namespace Jiny\Auth\App\Services;

use Jiny\Auth\App\Models\UserTerms;
use Jiny\Auth\App\Models\UserTermLog;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TermVersionService
{
    /**
     * 새 버전의 약관 생성
     */
    public function createNewVersion($termId, $data = [])
    {
        $originalTerm = UserTerms::findOrFail($termId);

        DB::beginTransaction();
        try {
            // 새 버전 생성
            $newVersion = $originalTerm->createNewVersion($data);

            // 기존 버전을 비활성화 (선택사항)
            if (isset($data['deactivate_previous']) && $data['deactivate_previous']) {
                $originalTerm->update(['is_active' => false]);
            }

            DB::commit();
            return $newVersion;
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * 약관 버전 활성화
     */
    public function activateVersion($termId)
    {
        $term = UserTerms::findOrFail($termId);

        DB::beginTransaction();
        try {
            // 동일한 슬러그의 다른 버전들을 비활성화
            UserTerms::where('slug', $term->slug)
                    ->where('id', '!=', $termId)
                    ->update(['is_active' => false]);

            // 현재 버전을 활성화
            $term->update(['is_active' => true]);

            DB::commit();
            return $term;
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * 사용자가 동의해야 하는 약관 목록 조회
     */
    public function getRequiredConsentsForUser($userId)
    {
        return UserTerms::getRequiredConsentsForUser($userId);
    }

    /**
     * 사용자의 약관 동의 처리
     */
    public function processUserConsent($userId, $termId, $agreed = true, $data = [])
    {
        $term = UserTerms::findOrFail($termId);

        DB::beginTransaction();
        try {
            // 동의 기록 생성
            $consent = UserTermLog::createConsent($termId, $userId, array_merge($data, [
                'agreed' => $agreed,
                'version' => $term->version,
                'consent_type' => $agreed ? 'initial' : 'rejection'
            ]));

            // 약관의 동의 통계 업데이트
            $this->updateTermConsentStats($term);

            DB::commit();
            return $consent;
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * 사용자의 약관 동의 철회
     */
    public function withdrawUserConsent($userId, $termId)
    {
        $latestConsent = UserTermLog::getLatestConsentForUser($userId, $termId);

        if (!$latestConsent || !$latestConsent->agreed || $latestConsent->withdrawn_at) {
            throw new \Exception('동의 철회할 수 있는 기록이 없습니다.');
        }

        DB::beginTransaction();
        try {
            $latestConsent->withdraw();

            // 약관의 동의 통계 업데이트
            $term = UserTerms::find($termId);
            if ($term) {
                $this->updateTermConsentStats($term);
            }

            DB::commit();
            return $latestConsent;
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * 약관의 동의 통계 업데이트
     */
    private function updateTermConsentStats($term)
    {
        $totalConsents = $term->agreementLogs()->where('agreed', true)->count();
        $term->update(['users' => $totalConsents]);
    }

    /**
     * 약관 버전 이력 조회
     */
    public function getVersionHistory($termId)
    {
        $term = UserTerms::findOrFail($termId);
        return $term->versions()->get();
    }

    /**
     * 사용자의 약관 동의 이력 조회
     */
    public function getUserConsentHistory($userId, $termId = null)
    {
        $query = UserTermLog::with(['term', 'user'])
                           ->where('user_id', $userId)
                           ->orderBy('agreed_at', 'desc');

        if ($termId) {
            $query->where('term_id', $termId);
        }

        return $query->get();
    }

    /**
     * 약관 버전별 동의 통계 조회
     */
    public function getVersionConsentStats($termId)
    {
        $term = UserTerms::findOrFail($termId);
        $versions = $term->versions()->get();
        $stats = [];

        foreach ($versions as $version) {
            $consentStats = $version->getConsentStats();
            $stats[] = [
                'version' => $version->version,
                'title' => $version->title,
                'is_active' => $version->is_active,
                'effective_date' => $version->effective_date,
                'stats' => $consentStats
            ];
        }

        return $stats;
    }

    /**
     * 약관 버전 비교
     */
    public function compareVersions($termId1, $termId2)
    {
        $term1 = UserTerms::findOrFail($termId1);
        $term2 = UserTerms::findOrFail($termId2);

        if ($term1->slug !== $term2->slug) {
            throw new \Exception('동일한 약관의 버전만 비교할 수 있습니다.');
        }

        return [
            'term1' => $term1,
            'term2' => $term2,
            'differences' => $this->getDifferences($term1, $term2)
        ];
    }

    /**
     * 버전 간 차이점 분석
     */
    private function getDifferences($term1, $term2)
    {
        $differences = [];

        $fields = ['title', 'content', 'description', 'type', 'required'];

        foreach ($fields as $field) {
            if ($term1->$field !== $term2->$field) {
                $differences[$field] = [
                    'old' => $term1->$field,
                    'new' => $term2->$field
                ];
            }
        }

        return $differences;
    }

    /**
     * 약관 버전 마이그레이션 (사용자 동의 이력 마이그레이션)
     */
    public function migrateUserConsents($oldTermId, $newTermId)
    {
        $oldTerm = UserTerms::findOrFail($oldTermId);
        $newTerm = UserTerms::findOrFail($newTermId);

        if ($oldTerm->slug !== $newTerm->slug) {
            throw new \Exception('동일한 약관의 버전만 마이그레이션할 수 있습니다.');
        }

        DB::beginTransaction();
        try {
            // 기존 동의 기록들을 새 버전으로 복사
            $oldConsents = UserTermLog::where('term_id', $oldTermId)
                                    ->where('agreed', true)
                                    ->whereNull('withdrawn_at')
                                    ->get();

            foreach ($oldConsents as $oldConsent) {
                // 새 버전에 대한 동의 기록이 없는 경우에만 생성
                $existingConsent = UserTermLog::where('user_id', $oldConsent->user_id)
                                            ->where('term_id', $newTermId)
                                            ->where('agreed', true)
                                            ->whereNull('withdrawn_at')
                                            ->first();

                if (!$existingConsent) {
                    UserTermLog::create([
                        'user_id' => $oldConsent->user_id,
                        'term_id' => $newTermId,
                        'agreed' => true,
                        'agreed_at' => $oldConsent->agreed_at,
                        'ip_address' => $oldConsent->ip_address,
                        'user_agent' => $oldConsent->user_agent,
                        'version' => $newTerm->version,
                        'consent_type' => 'migrated',
                        'consent_method' => $oldConsent->consent_method,
                        'metadata' => $oldConsent->metadata
                    ]);
                }
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }
}
