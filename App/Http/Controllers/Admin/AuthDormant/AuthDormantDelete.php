<?php

namespace Jiny\Auth\App\Http\Controllers\Admin\AuthDormant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jiny\Auth\App\Models\DormantAccount;

class AuthDormantDelete extends Controller
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
     * Display the activation/deletion confirmation page
     */
    public function index($id)
    {
        $this->jsonData['controllerClass'] = self::class;
        
        $dormantAccount = DormantAccount::with('account')->findOrFail($id);
        
        // Determine action type based on route
        $action = request()->route()->getName() === 'admin.auth.dormant.activate' ? 'activate' : 'delete';
        
        // Set template paths
        $this->jsonData['template'] = [
            'layout' => 'jiny-admin::template.livewire.admin-delete',
            'confirm' => 'jiny-auth::admin.auth_dormant.delete'
        ];
        
        $this->jsonData['action'] = $action;

        return view('jiny-admin::crud.delete', [
            'jsonData' => $this->jsonData,
            'data' => $dormantAccount,
            'id' => $id,
            'action' => $action
        ]);
    }

    /**
     * Hook: Before deleting/activating
     */
    public function hookDeleting($wire, $id)
    {
        $dormantAccount = DormantAccount::find($id);
        
        if (!$dormantAccount) {
            return "휴면계정을 찾을 수 없습니다.";
        }
        
        $action = $wire->action ?? 'delete';
        
        if ($action === 'activate') {
            // Check if account can be reactivated
            if (!$dormantAccount->canBeReactivated()) {
                return "이 계정은 활성화할 수 없는 상태입니다.";
            }
        } else {
            // Backup data before deletion
            if (!$dormantAccount->backup_data) {
                $dormantAccount->backupData();
            }
        }
        
        return true;
    }

    /**
     * Hook: Process deletion/activation
     */
    public function hookDelete($wire, $id)
    {
        $dormantAccount = DormantAccount::find($id);
        
        if (!$dormantAccount) {
            return false;
        }
        
        $action = $wire->action ?? 'delete';
        
        if ($action === 'activate') {
            // Reactivate the account
            $result = $dormantAccount->reactivate('admin_activation', auth()->id());
            
            if ($result) {
                // Log the activation
                activity()
                    ->causedBy(auth()->user())
                    ->performedOn($dormantAccount)
                    ->withProperties([
                        'account_id' => $dormantAccount->account_id,
                        'account_email' => $dormantAccount->account->email
                    ])
                    ->log('휴면계정 활성화');
                
                return true;
            }
        } else {
            // Perform deletion
            
            // Mark as deleted in dormant_accounts
            $dormantAccount->update([
                'status' => 'deleted',
                'meta' => array_merge($dormantAccount->meta ?? [], [
                    'deleted_by' => auth()->id(),
                    'deleted_at' => now()->toDateTimeString()
                ])
            ]);
            
            // Delete the actual account (soft delete if enabled)
            $dormantAccount->account()->delete();
            
            // Log the deletion
            activity()
                ->causedBy(auth()->user())
                ->performedOn($dormantAccount)
                ->withProperties([
                    'account_id' => $dormantAccount->account_id,
                    'backup_data' => $dormantAccount->backup_data
                ])
                ->log('휴면계정 삭제');
            
            return true;
        }
        
        return false;
    }

    /**
     * Hook: After deletion/activation
     */
    public function hookDeleted($wire, $id)
    {
        $action = $wire->action ?? 'delete';
        
        if ($action === 'activate') {
            session()->flash('success', '휴면계정이 성공적으로 활성화되었습니다.');
        } else {
            session()->flash('success', '휴면계정이 성공적으로 삭제되었습니다.');
        }
    }

    /**
     * Hook: Check if can delete
     */
    public function hookCanDelete($wire, $model)
    {
        // Already deleted accounts cannot be deleted again
        if ($model->status === 'deleted') {
            return false;
        }
        
        // Check permissions
        if (!auth()->user()->can('admin.auth.dormant.delete')) {
            return false;
        }
        
        return true;
    }

    /**
     * Hook: Soft delete handling
     */
    public function hookSoftDeleting($wire, $model)
    {
        // For dormant accounts, we don't use traditional soft delete
        // Instead, we change status to 'deleted' and keep the record
        $model->status = 'deleted';
        $model->save();
        
        return false; // Prevent actual deletion
    }

    /**
     * Hook: Restore handling (for reactivation)
     */
    public function hookRestoring($wire, $model)
    {
        if ($model->status === 'deleted') {
            return "삭제된 계정은 복원할 수 없습니다.";
        }
        
        // Reactivate the account
        $model->reactivate('admin_restore', auth()->id());
        
        return true;
    }
}