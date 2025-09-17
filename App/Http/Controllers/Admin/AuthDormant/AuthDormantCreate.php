<?php

namespace Jiny\Auth\App\Http\Controllers\Admin\AuthDormant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jiny\Auth\App\Models\DormantAccount;
use Jiny\Auth\App\Models\Account;

class AuthDormantCreate extends Controller
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
     * Display the create form for manual dormant processing
     */
    public function index()
    {
        $this->jsonData['controllerClass'] = self::class;
        
        // Get active accounts that are not already dormant
        $availableAccounts = Account::whereNotIn('id', function($query) {
            $query->select('account_id')
                  ->from('dormant_accounts')
                  ->whereIn('status', ['dormant', 'notified']);
        })
        ->where('status', 'active')
        ->get();
        
        // Set template paths
        $this->jsonData['template'] = [
            'layout' => 'jiny-admin::template.livewire.admin-create',
            'form' => 'jiny-auth::admin.auth_dormant.create'
        ];

        return view('jiny-admin::crud.create', [
            'jsonData' => $this->jsonData,
            'availableAccounts' => $availableAccounts
        ]);
    }

    /**
     * Hook: Before creating form initialization
     */
    public function hookCreating($wire, $value)
    {
        // Set default values
        $value['scheduled_deletion_at'] = now()->addDays(365)->format('Y-m-d');
        $value['send_notification'] = true;
        $value['reason'] = 'inactivity';
        
        return $value;
    }

    /**
     * Hook: Form field validation before storing
     */
    public function hookValidating($wire, $form)
    {
        // Check if account is already dormant
        $existing = DormantAccount::where('account_id', $form['account_id'])
            ->whereIn('status', ['dormant', 'notified'])
            ->first();
            
        if ($existing) {
            return "이미 휴면 처리된 계정입니다.";
        }
        
        // Validate account exists and is active
        $account = Account::find($form['account_id']);
        if (!$account) {
            return "존재하지 않는 계정입니다.";
        }
        
        if ($account->status !== 'active') {
            return "활성 상태의 계정만 휴면 처리할 수 있습니다.";
        }
        
        return $form;
    }

    /**
     * Hook: Before storing dormant account
     */
    public function hookStoring($wire, $form)
    {
        $account = Account::find($form['account_id']);
        
        // Prepare data for dormant account
        $dormantData = [
            'account_id' => $form['account_id'],
            'last_activity_at' => $account->last_login_at ?? $account->created_at,
            'dormant_at' => now(),
            'scheduled_deletion_at' => $form['scheduled_deletion_at'] ?? now()->addDays(365),
            'status' => 'dormant',
            'reason' => $form['reason'],
            'notification_count' => 0,
            'meta' => [
                'processed_by' => auth()->id(),
                'processed_at' => now()->toDateTimeString(),
                'reason_detail' => $form['reason_detail'] ?? null,
                'manual_process' => true
            ]
        ];
        
        // Update account status to dormant
        $account->update(['status' => 'dormant']);
        
        return $dormantData;
    }

    /**
     * Hook: After storing dormant account
     */
    public function hookStored($wire, $model)
    {
        // Send notification if requested
        if ($wire->form['send_notification'] ?? false) {
            $model->sendNotification();
        }
        
        // Log the manual dormant processing
        activity()
            ->causedBy(auth()->user())
            ->performedOn($model)
            ->withProperties([
                'account_id' => $model->account_id,
                'reason' => $model->reason
            ])
            ->log('수동 휴면 처리');
    }

    /**
     * Hook: Customize form fields
     */
    public function hookFormFields($wire)
    {
        // Get available accounts for selection
        $accounts = Account::whereNotIn('id', function($query) {
            $query->select('account_id')
                  ->from('dormant_accounts')
                  ->whereIn('status', ['dormant', 'notified']);
        })
        ->where('status', 'active')
        ->pluck('email', 'id');
        
        return [
            'accounts' => $accounts,
            'default_deletion_days' => 365,
            'reasons' => [
                'inactivity' => '장기 미접속',
                'request' => '사용자 요청',
                'policy' => '정책 위반',
                'security' => '보안 문제',
                'other' => '기타'
            ]
        ];
    }

    /**
     * Hook: Set default values
     */
    public function hookDefaults($wire)
    {
        return [
            'reason' => 'inactivity',
            'send_notification' => true,
            'scheduled_deletion_at' => now()->addDays(365)->format('Y-m-d')
        ];
    }
}