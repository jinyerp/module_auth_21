<?php

namespace Jiny\Auth\App\Http\Controllers\Admin\AuthDormant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jiny\Auth\App\Models\DormantAccount;
use Jiny\Auth\App\Models\AccountLog;
use Jiny\Auth\App\Models\LoginHistory;

class AuthDormantShow extends Controller
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
     * Display dormant account details
     */
    public function index($id)
    {
        $this->jsonData['controllerClass'] = self::class;
        
        $dormantAccount = DormantAccount::with(['account', 'reactivatedByAccount'])->findOrFail($id);
        
        // Set template paths
        $this->jsonData['template'] = [
            'layout' => 'jiny-admin::template.livewire.admin-show',
            'view' => 'jiny-auth::admin.auth_dormant.show'
        ];

        return view('jiny-admin::crud.show', [
            'jsonData' => $this->jsonData,
            'data' => $dormantAccount,
            'id' => $id
        ]);
    }

    /**
     * Hook: Before showing dormant account
     */
    public function hookShowing($wire, $id)
    {
        // Check permissions
        if (!auth()->user()->can('admin.auth.dormant.view')) {
            abort(403, '권한이 없습니다.');
        }
    }

    /**
     * Hook: After loading dormant account
     */
    public function hookShowed($wire, $record)
    {
        // Add calculated fields
        $record->dormant_days = $record->getDaysSinceDormant();
        $record->deletion_days = $record->getDaysUntilDeletion();
        
        // Account information
        if ($record->account) {
            $record->account_info = [
                'id' => $record->account->id,
                'email' => $record->account->email,
                'name' => $record->account->name,
                'phone' => $record->account->phone,
                'status' => $record->account->status,
                'created_at' => $record->account->created_at,
                'last_login_at' => $record->account->last_login_at,
                'grade' => $record->account->grade ? $record->account->grade->name : null,
                'roles' => $record->account->roles->pluck('name')->toArray()
            ];
        }
        
        // Reactivated by information
        if ($record->reactivatedByAccount) {
            $record->reactivated_by_info = [
                'id' => $record->reactivatedByAccount->id,
                'email' => $record->reactivatedByAccount->email,
                'name' => $record->reactivatedByAccount->name
            ];
        }
        
        // Status badge color
        $record->status_color = match($record->status) {
            'dormant' => 'gray',
            'notified' => 'yellow',
            'reactivated' => 'green',
            'deleted' => 'red',
            default => 'gray'
        };
        
        // Status label
        $record->status_label = match($record->status) {
            'dormant' => '휴면',
            'notified' => '알림발송',
            'reactivated' => '재활성화',
            'deleted' => '삭제됨',
            default => $record->status
        };
        
        // Timeline data
        $record->timeline = $this->buildTimeline($record);
        
        // Activity logs
        $record->activity_logs = $this->getActivityLogs($record);
        
        // Login history
        $record->login_history = $this->getLoginHistory($record);
        
        return $record;
    }

    /**
     * Hook: Customize detail fields
     */
    public function hookDetailFields($wire)
    {
        return [
            'basic' => [
                'title' => '기본 정보',
                'fields' => [
                    'id' => 'ID',
                    'status_label' => '상태',
                    'reason' => '휴면 사유',
                    'dormant_days' => '휴면 기간 (일)',
                    'deletion_days' => '삭제까지 남은 기간 (일)'
                ]
            ],
            'dates' => [
                'title' => '날짜 정보',
                'fields' => [
                    'last_activity_at' => '마지막 활동',
                    'dormant_at' => '휴면 처리일',
                    'notified_at' => '알림 발송일',
                    'scheduled_deletion_at' => '삭제 예정일',
                    'reactivated_at' => '재활성화일'
                ]
            ],
            'notification' => [
                'title' => '알림 정보',
                'fields' => [
                    'notification_count' => '알림 발송 횟수',
                    'notified_at' => '마지막 알림 발송일'
                ]
            ],
            'account' => [
                'title' => '계정 정보',
                'fields' => [
                    'account_info.email' => '이메일',
                    'account_info.name' => '이름',
                    'account_info.phone' => '전화번호',
                    'account_info.status' => '계정 상태',
                    'account_info.grade' => '등급',
                    'account_info.roles' => '역할'
                ]
            ]
        ];
    }

    /**
     * Hook: Load related data
     */
    public function hookRelatedData($wire, $model)
    {
        return [
            'account_logs' => AccountLog::where('account_id', $model->account_id)
                ->latest()
                ->limit(10)
                ->get(),
            'login_history' => LoginHistory::where('account_id', $model->account_id)
                ->latest()
                ->limit(10)
                ->get(),
            'activity_logs' => activity()
                ->forSubject($model)
                ->latest()
                ->limit(10)
                ->get()
        ];
    }

    /**
     * Build timeline data
     */
    protected function buildTimeline($record)
    {
        $timeline = [];
        
        // Account created
        if ($record->account) {
            $timeline[] = [
                'date' => $record->account->created_at,
                'event' => '계정 생성',
                'type' => 'info'
            ];
        }
        
        // Last activity
        if ($record->last_activity_at) {
            $timeline[] = [
                'date' => $record->last_activity_at,
                'event' => '마지막 활동',
                'type' => 'default'
            ];
        }
        
        // Became dormant
        $timeline[] = [
            'date' => $record->dormant_at,
            'event' => '휴면 처리',
            'type' => 'warning'
        ];
        
        // Notifications sent
        if ($record->notified_at) {
            $timeline[] = [
                'date' => $record->notified_at,
                'event' => '알림 발송 (' . $record->notification_count . '회)',
                'type' => 'info'
            ];
        }
        
        // Reactivated
        if ($record->reactivated_at) {
            $timeline[] = [
                'date' => $record->reactivated_at,
                'event' => '재활성화',
                'type' => 'success'
            ];
        }
        
        // Scheduled for deletion
        if ($record->scheduled_deletion_at && $record->status !== 'reactivated') {
            $timeline[] = [
                'date' => $record->scheduled_deletion_at,
                'event' => '삭제 예정',
                'type' => 'danger',
                'future' => true
            ];
        }
        
        // Sort by date
        usort($timeline, function($a, $b) {
            return $a['date'] <=> $b['date'];
        });
        
        return $timeline;
    }

    /**
     * Get activity logs
     */
    protected function getActivityLogs($record)
    {
        return activity()
            ->forSubject($record)
            ->latest()
            ->limit(20)
            ->get()
            ->map(function($log) {
                return [
                    'created_at' => $log->created_at,
                    'description' => $log->description,
                    'causer' => $log->causer ? $log->causer->name : 'System',
                    'properties' => $log->properties
                ];
            });
    }

    /**
     * Get login history
     */
    protected function getLoginHistory($record)
    {
        if (!$record->account_id) {
            return collect([]);
        }
        
        return LoginHistory::where('account_id', $record->account_id)
            ->latest()
            ->limit(10)
            ->get()
            ->map(function($login) {
                return [
                    'logged_in_at' => $login->logged_in_at,
                    'ip_address' => $login->ip_address,
                    'user_agent' => $login->user_agent,
                    'location' => $login->location
                ];
            });
    }
}