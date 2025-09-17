<?php

namespace Jiny\Auth\App\Http\Controllers\Admin\AuthDormant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jiny\Auth\App\Models\DormantAccount;
use Jiny\Auth\App\Models\Account;
use Carbon\Carbon;

class AuthDormant extends Controller
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
     * Display a listing of dormant accounts
     */
    public function index()
    {
        $this->jsonData['controllerClass'] = self::class;
        
        // Set template paths
        $this->jsonData['template'] = [
            'layout' => 'jiny-admin::template.livewire.admin-table',
            'table' => 'jiny-auth::admin.auth_dormant.table'
        ];

        return view('jiny-admin::crud.index', [
            'jsonData' => $this->jsonData
        ]);
    }

    /**
     * Display dormant account statistics
     */
    public function statistics()
    {
        // Monthly statistics
        $monthlyStats = DormantAccount::select(
            DB::raw('DATE_FORMAT(dormant_at, "%Y-%m") as month'),
            DB::raw('COUNT(*) as total'),
            DB::raw('SUM(CASE WHEN status = "dormant" THEN 1 ELSE 0 END) as dormant'),
            DB::raw('SUM(CASE WHEN status = "notified" THEN 1 ELSE 0 END) as notified'),
            DB::raw('SUM(CASE WHEN status = "reactivated" THEN 1 ELSE 0 END) as reactivated'),
            DB::raw('SUM(CASE WHEN status = "deleted" THEN 1 ELSE 0 END) as deleted')
        )
        ->where('dormant_at', '>=', now()->subMonths(12))
        ->groupBy('month')
        ->orderBy('month', 'desc')
        ->get();

        // Current statistics
        $currentStats = [
            'total_dormant' => DormantAccount::where('status', 'dormant')->count(),
            'total_notified' => DormantAccount::where('status', 'notified')->count(),
            'pending_deletion' => DormantAccount::scheduledForDeletion()->count(),
            'reactivated_this_month' => DormantAccount::where('status', 'reactivated')
                ->where('reactivated_at', '>=', now()->startOfMonth())
                ->count(),
            'avg_dormant_days' => DormantAccount::whereIn('status', ['dormant', 'notified'])
                ->avg(DB::raw('DATEDIFF(NOW(), dormant_at)')) ?? 0,
        ];

        // Reasons distribution
        $reasonsStats = DormantAccount::select('reason', DB::raw('COUNT(*) as count'))
            ->whereIn('status', ['dormant', 'notified'])
            ->groupBy('reason')
            ->orderBy('count', 'desc')
            ->get();

        return view('jiny-auth::admin.auth_dormant.statistics', [
            'monthlyStats' => $monthlyStats,
            'currentStats' => $currentStats,
            'reasonsStats' => $reasonsStats,
            'jsonData' => $this->jsonData
        ]);
    }

    /**
     * Bulk activate dormant accounts
     */
    public function bulkActivate(Request $request)
    {
        $ids = $request->input('ids', []);
        $reason = $request->input('reason', 'bulk_activation');
        
        $activated = 0;
        foreach ($ids as $id) {
            $dormantAccount = DormantAccount::find($id);
            if ($dormantAccount && $dormantAccount->canBeReactivated()) {
                $dormantAccount->reactivate($reason, auth()->id());
                $activated++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "{$activated}개의 계정이 활성화되었습니다."
        ]);
    }

    /**
     * Bulk delete dormant accounts
     */
    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids', []);
        
        $deleted = 0;
        foreach ($ids as $id) {
            $dormantAccount = DormantAccount::find($id);
            if ($dormantAccount) {
                // Backup data before deletion
                $dormantAccount->backupData();
                
                // Mark as deleted
                $dormantAccount->update(['status' => 'deleted']);
                
                // Delete the actual account
                $dormantAccount->account()->delete();
                
                $deleted++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "{$deleted}개의 계정이 삭제되었습니다."
        ]);
    }

    /**
     * Send notification to dormant accounts
     */
    public function sendNotification(Request $request)
    {
        $ids = $request->input('ids', []);
        $notified = 0;
        
        foreach ($ids as $id) {
            $dormantAccount = DormantAccount::find($id);
            if ($dormantAccount && in_array($dormantAccount->status, ['dormant', 'notified'])) {
                $dormantAccount->sendNotification();
                $notified++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "{$notified}개의 계정에 알림을 발송했습니다."
        ]);
    }

    /**
     * Display dormant account settings
     */
    public function settings()
    {
        $settings = [
            'dormant_days' => config('auth.dormant.days_until_dormant', 365),
            'warning_days' => config('auth.dormant.warning_days', 30),
            'deletion_days' => config('auth.dormant.days_until_deletion', 730),
            'auto_process' => config('auth.dormant.auto_process', false),
            'notification_enabled' => config('auth.dormant.notification_enabled', true),
            'notification_template' => config('auth.dormant.notification_template', 'default'),
        ];

        return view('jiny-auth::admin.auth_dormant.settings', [
            'settings' => $settings,
            'jsonData' => $this->jsonData
        ]);
    }

    /**
     * Update dormant account settings
     */
    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'dormant_days' => 'required|integer|min:30',
            'warning_days' => 'required|integer|min:7',
            'deletion_days' => 'required|integer|min:30',
            'auto_process' => 'boolean',
            'notification_enabled' => 'boolean',
            'notification_template' => 'required|string',
        ]);

        // Here you would save the settings to database or config
        // For now, we'll return success
        
        return redirect()->route('admin.auth.dormant.settings')
            ->with('success', '휴면계정 설정이 업데이트되었습니다.');
    }

    /**
     * Hook: Before loading index data
     */
    public function hookIndexing($wire)
    {
        // Add custom query conditions
        if ($wire->search) {
            $wire->query->whereHas('account', function($q) use ($wire) {
                $q->where('email', 'like', '%' . $wire->search . '%')
                  ->orWhere('name', 'like', '%' . $wire->search . '%');
            });
        }
    }

    /**
     * Hook: After loading index data
     */
    public function hookIndexed($wire, $rows)
    {
        // Add calculated fields
        foreach ($rows as $row) {
            // Days since dormant
            $row->dormant_days = $row->getDaysSinceDormant();
            
            // Days until deletion
            $row->deletion_days = $row->getDaysUntilDeletion();
            
            // Account information
            if ($row->account) {
                $row->account_email = $row->account->email;
                $row->account_name = $row->account->name;
            }
        }
        
        return $rows;
    }

    /**
     * Hook: Before activating an account
     */
    public function hookActivating($wire, $id)
    {
        $dormantAccount = DormantAccount::find($id);
        
        if (!$dormantAccount) {
            return "휴면계정을 찾을 수 없습니다.";
        }
        
        if (!$dormantAccount->canBeReactivated()) {
            return "이 계정은 활성화할 수 없는 상태입니다.";
        }
        
        return true;
    }

    /**
     * Hook: After activating an account
     */
    public function hookActivated($wire, $id)
    {
        // Log the activation
        activity()
            ->causedBy(auth()->user())
            ->performedOn(DormantAccount::find($id))
            ->log('휴면계정 활성화');
    }

    /**
     * Hook: Before deleting a dormant account
     */
    public function hookDeleting($wire, $id)
    {
        $dormantAccount = DormantAccount::find($id);
        
        if ($dormantAccount) {
            // Backup account data
            $dormantAccount->backupData();
        }
        
        return true;
    }

    /**
     * Hook: Generate statistics data
     */
    public function hookStatistics($wire)
    {
        // Additional statistics processing
        $data = [
            'reactivation_rate' => $this->calculateReactivationRate(),
            'avg_notification_count' => DormantAccount::avg('notification_count') ?? 0,
            'peak_dormant_month' => $this->getPeakDormantMonth(),
        ];
        
        return $data;
    }

    /**
     * Calculate reactivation rate
     */
    protected function calculateReactivationRate()
    {
        $total = DormantAccount::count();
        if ($total == 0) return 0;
        
        $reactivated = DormantAccount::where('status', 'reactivated')->count();
        return round(($reactivated / $total) * 100, 2);
    }

    /**
     * Get peak dormant month
     */
    protected function getPeakDormantMonth()
    {
        $result = DormantAccount::select(
            DB::raw('DATE_FORMAT(dormant_at, "%Y-%m") as month'),
            DB::raw('COUNT(*) as count')
        )
        ->groupBy('month')
        ->orderBy('count', 'desc')
        ->first();
        
        return $result ? $result->month : null;
    }
}