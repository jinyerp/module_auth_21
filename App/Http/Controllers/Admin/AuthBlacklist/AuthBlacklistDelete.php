<?php

namespace Jiny\Auth\App\Http\Controllers\Admin\AuthBlacklist;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Jiny\Auth\App\Models\Blacklist;

class AuthBlacklistDelete extends Controller
{
    private $jsonData;

    public function __construct()
    {
        $this->jsonData = $this->loadJsonData();
    }

    protected function loadJsonData()
    {
        $jsonPath = dirname(__FILE__) . '/AuthBlacklist.json';
        
        if (file_exists($jsonPath)) {
            $jsonContent = file_get_contents($jsonPath);
            return json_decode($jsonContent, true);
        }

        return [];
    }

    public function index($id)
    {
        $data = Blacklist::findOrFail($id);

        $this->jsonData['route'] = [
            'name' => 'admin.auth.blacklist',
            'delete' => 'admin.auth.blacklist.delete',
            'destroy' => 'admin.auth.blacklist.destroy'
        ];

        $this->jsonData['template'] = [
            'delete' => 'jiny-auth::admin.auth_blacklist.delete'
        ];

        // controllerClass 설정 (Hook 시스템 활성화)
        $this->jsonData['controllerClass'] = self::class;

        return view('jiny-admin::crud.delete', [
            'jsonData' => $this->jsonData,
            'data' => $data,
            'id' => $id
        ]);
    }

    /**
     * Hook: 삭제 가능 여부 확인
     */
    public function hookCanDelete($wire, $model)
    {
        // 활성 상태이고 최근에 차단된 경우 경고
        if ($model->is_active && $model->last_hit_at) {
            $lastHitTime = $model->last_hit_at->diffForHumans();
            if ($model->last_hit_at->isAfter(now()->subHours(24))) {
                $wire->dispatch('show-warning', [
                    'message' => "이 항목은 {$lastHitTime}에 마지막으로 차단 작동했습니다. 삭제하시겠습니까?"
                ]);
            }
        }

        // 높은 차단 횟수 경고
        if ($model->hit_count > 100) {
            $wire->dispatch('show-warning', [
                'message' => "이 항목은 {$model->hit_count}회 차단되었습니다. 정말 삭제하시겠습니까?"
            ]);
        }

        return true;
    }

    /**
     * Hook: 삭제 전 처리
     */
    public function hookDeleting($wire, $id)
    {
        $model = Blacklist::find($id);
        
        if (!$model) {
            return "블랙리스트 항목을 찾을 수 없습니다.";
        }

        // 삭제 전 백업 (로그로 기록)
        Log::warning('Blacklist entry being deleted', [
            'id' => $model->id,
            'type' => $model->type,
            'value' => $model->value,
            'reason' => $model->reason,
            'hit_count' => $model->hit_count,
            'deleted_by' => auth()->user()->id ?? 'system',
            'deleted_at' => now()->toIsoString(),
            'full_record' => $model->toArray()
        ]);

        // 중요한 블랙리스트인 경우 추가 확인
        $criticalTypes = ['ip', 'email'];
        if (in_array($model->type, $criticalTypes) && $model->hit_count > 50) {
            // 소프트 삭제 권장 (비활성화만)
            $wire->dispatch('confirm-soft-delete', [
                'message' => '차단 횟수가 많은 중요한 항목입니다. 삭제 대신 비활성화를 권장합니다.'
            ]);
        }

        // 관련 데이터 정리 (필요한 경우)
        // 예: 관련 로그 아카이빙
        $this->archiveRelatedData($model);

        return true;
    }

    /**
     * Hook: 삭제 후 처리
     */
    public function hookDeleted($wire, $id)
    {
        // 성공 메시지
        session()->flash('message', '블랙리스트 항목이 성공적으로 삭제되었습니다.');
        
        // 관련 캐시 클리어
        // Cache::forget('blacklist_all');
        
        // 알림 전송 (필요시)
        // Notification::send($admins, new BlacklistEntryDeleted($id));
        
        // 삭제 활동 로그
        Log::info('Blacklist entry deleted', [
            'id' => $id,
            'deleted_by' => auth()->user()->name ?? 'Unknown',
            'deleted_at' => now()->toIsoString()
        ]);
    }

    /**
     * Hook: 소프트 삭제 (비활성화)
     */
    public function hookSoftDeleting($wire, $model)
    {
        // 비활성화만 수행
        $model->is_active = false;
        $model->save();

        // 메타데이터 업데이트
        $meta = $model->meta ?? [];
        $meta['soft_deleted_at'] = now()->toIsoString();
        $meta['soft_deleted_by'] = auth()->user()->id ?? 'system';
        $model->meta = $meta;
        $model->save();

        Log::info('Blacklist entry soft deleted (deactivated)', [
            'id' => $model->id,
            'type' => $model->type,
            'value' => $model->value
        ]);

        session()->flash('message', '블랙리스트 항목이 비활성화되었습니다.');
        
        return false; // 실제 삭제는 수행하지 않음
    }

    /**
     * Hook: 복원
     */
    public function hookRestoring($wire, $model)
    {
        $model->is_active = true;
        $model->save();

        // 메타데이터 업데이트
        $meta = $model->meta ?? [];
        $meta['restored_at'] = now()->toIsoString();
        $meta['restored_by'] = auth()->user()->id ?? 'system';
        unset($meta['soft_deleted_at']);
        unset($meta['soft_deleted_by']);
        $model->meta = $meta;
        $model->save();

        Log::info('Blacklist entry restored', [
            'id' => $model->id,
            'type' => $model->type,
            'value' => $model->value
        ]);

        session()->flash('message', '블랙리스트 항목이 복원되었습니다.');
    }

    /**
     * 관련 데이터 아카이빙
     */
    protected function archiveRelatedData($model)
    {
        // 블랙리스트 히스토리 테이블이 있다면 아카이빙
        // DB::table('blacklist_history')->insert([
        //     'original_id' => $model->id,
        //     'type' => $model->type,
        //     'value' => $model->value,
        //     'reason' => $model->reason,
        //     'hit_count' => $model->hit_count,
        //     'archived_at' => now()
        // ]);
        
        // 또는 JSON 파일로 백업
        $backupPath = storage_path('app/blacklist_archives/' . date('Y-m'));
        if (!file_exists($backupPath)) {
            mkdir($backupPath, 0755, true);
        }
        
        $backupFile = $backupPath . '/' . $model->id . '_' . time() . '.json';
        file_put_contents($backupFile, json_encode($model->toArray(), JSON_PRETTY_PRINT));
    }

    /**
     * Hook: 삭제 확인 메시지
     */
    public function hookDeleteConfirmation($wire, $model)
    {
        $message = "다음 블랙리스트 항목을 삭제하시겠습니까?\n\n";
        $message .= "유형: {$model->type}\n";
        $message .= "값: {$model->value}\n";
        $message .= "사유: {$model->reason}\n";
        
        if ($model->hit_count > 0) {
            $message .= "차단 횟수: {$model->hit_count}회\n";
        }
        
        if ($model->is_active) {
            $message .= "\n⚠️ 현재 활성화된 차단입니다.";
        }
        
        return $message;
    }
}