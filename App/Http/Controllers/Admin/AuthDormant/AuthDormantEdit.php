<?php

namespace Jiny\Auth\App\Http\Controllers\Admin\AuthDormant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jiny\Auth\App\Models\DormantAccount;

class AuthDormantEdit extends Controller
{
    protected $jsonData;

    public function __construct()
    {
        $this->jsonData = $this->loadJsonData();
    }

    /**
     * Load JSON configuration
     */
    protected function loadJsonData()
    {
        $jsonPath = __DIR__ . '/AuthDormant.json';
        if (file_exists($jsonPath)) {
            return json_decode(file_get_contents($jsonPath), true);
        }
        
        return [];
    }

    /**
     * Display the edit form
     */
    public function index($id)
    {
        $this->jsonData['controllerClass'] = self::class;
        
        $dormantAccount = DormantAccount::with('account')->findOrFail($id);
        
        // Set template paths
        $this->jsonData['template'] = [
            'layout' => 'jiny-admin::template.livewire.admin-edit',
            'form' => 'jiny-auth::admin.auth_dormant.edit'
        ];

        return view('jiny-admin::crud.edit', [
            'jsonData' => $this->jsonData,
            'data' => $dormantAccount,
            'id' => $id
        ]);
    }

    /**
     * Hook: Before loading edit form
     */
    public function hookEditing($wire, $model)
    {
        // Add account information
        if ($model->account) {
            $model->account_email = $model->account->email;
            $model->account_name = $model->account->name;
        }
        
        // Format dates for form
        if ($model->scheduled_deletion_at) {
            $model->scheduled_deletion_at = $model->scheduled_deletion_at->format('Y-m-d\TH:i');
        }
        
        // Calculate days
        $model->dormant_days = $model->getDaysSinceDormant();
        $model->deletion_days = $model->getDaysUntilDeletion();
        
        return $model;
    }

    /**
     * Hook: Validate before updating
     */
    public function hookValidating($wire, $form)
    {
        $dormantAccount = DormantAccount::find($wire->dataId);
        
        if (!$dormantAccount) {
            return "휴면계정을 찾을 수 없습니다.";
        }
        
        // Check if status change is valid
        if (isset($form['status'])) {
            if ($form['status'] === 'reactivated' && $dormantAccount->status === 'deleted') {
                return "삭제된 계정은 재활성화할 수 없습니다.";
            }
            
            if ($form['status'] === 'deleted' && !$dormantAccount->backup_data) {
                // Backup data before allowing deletion
                $dormantAccount->backupData();
            }
        }
        
        // Validate scheduled deletion date
        if (isset($form['scheduled_deletion_at']) && $form['scheduled_deletion_at']) {
            $deletionDate = \Carbon\Carbon::parse($form['scheduled_deletion_at']);
            if ($deletionDate->isPast()) {
                return "삭제 예정일은 미래 날짜여야 합니다.";
            }
        }
        
        return $form;
    }

    /**
     * Hook: Before updating dormant account
     */
    public function hookUpdating($wire, $form)
    {
        // Update metadata
        $dormantAccount = DormantAccount::find($wire->dataId);
        $meta = $dormantAccount->meta ?? [];
        $meta['last_updated_by'] = auth()->id();
        $meta['last_updated_at'] = now()->toDateTimeString();
        
        $form['meta'] = $meta;
        
        // Handle status changes
        if (isset($form['status'])) {
            if ($form['status'] === 'reactivated' && $dormantAccount->status !== 'reactivated') {
                $form['reactivated_at'] = now();
                $form['reactivated_by'] = auth()->id();
                $form['reactivation_reason'] = 'admin_manual';
                
                // Reactivate the account
                $dormantAccount->account->update(['status' => 'active']);
            }
            
            if ($form['status'] === 'notified' && $dormantAccount->status !== 'notified') {
                $form['notified_at'] = now();
                $form['notification_count'] = $dormantAccount->notification_count + 1;
            }
        }
        
        return $form;
    }

    /**
     * Hook: After updating dormant account
     */
    public function hookUpdated($wire, $model)
    {
        // Log the update
        $changes = $model->getChanges();
        
        activity()
            ->causedBy(auth()->user())
            ->performedOn($model)
            ->withProperties(['changes' => $changes])
            ->log('휴면계정 정보 수정');
        
        // If status changed to reactivated, clear scheduled deletion
        if (isset($changes['status']) && $changes['status'] === 'reactivated') {
            $model->update(['scheduled_deletion_at' => null]);
        }
    }

    /**
     * Hook: Customize form fields for edit
     */
    public function hookFormFields($wire, $model)
    {
        return [
            'statuses' => [
                'dormant' => '휴면',
                'notified' => '알림발송',
                'reactivated' => '재활성화'
            ],
            'reasons' => [
                'inactivity' => '장기 미접속',
                'request' => '사용자 요청',
                'policy' => '정책 위반',
                'security' => '보안 문제',
                'other' => '기타'
            ],
            'can_reactivate' => $model->canBeReactivated(),
            'can_delete' => $model->status !== 'deleted',
            'days_info' => [
                'dormant_days' => $model->getDaysSinceDormant(),
                'deletion_days' => $model->getDaysUntilDeletion()
            ]
        ];
    }
}