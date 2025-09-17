<?php

namespace Jiny\Auth\App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use Carbon\Carbon;

class AdminBulkController extends Controller
{
    /**
     * 일괄 활성화
     * POST /admin/auth/bulk/activate
     */
    public function activate(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'send_email' => 'boolean'
        ]);
        
        DB::beginTransaction();
        try {
            $activatedCount = 0;
            $users = User::whereIn('id', $request->user_ids)->get();
            
            foreach ($users as $user) {
                // 이미 활성화된 사용자는 건너뛰기
                if ($user->status === 'active' && $user->email_verified_at) {
                    continue;
                }
                
                // 계정 활성화
                $user->status = 'active';
                $user->email_verified_at = $user->email_verified_at ?: now();
                $user->is_dormant = false;
                $user->dormant_at = null;
                $user->save();
                
                $activatedCount++;
                
                // 활동 로그 기록
                DB::table('auth_account_logs')->insert([
                    'user_id' => $user->id,
                    'event' => 'bulk_activated',
                    'description' => '관리자에 의한 일괄 활성화',
                    'performed_by' => auth()->id(),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'created_at' => now()
                ]);
                
                // 이메일 발송
                if ($request->send_email) {
                    Mail::to($user->email)->queue(new \App\Mail\AccountActivated($user));
                }
            }
            
            DB::commit();
            
            activity()
                ->causedBy(auth()->user())
                ->withProperties([
                    'user_ids' => $request->user_ids,
                    'activated_count' => $activatedCount
                ])
                ->log('사용자 일괄 활성화');
            
            return response()->json([
                'success' => true,
                'message' => "{$activatedCount}명의 사용자가 활성화되었습니다.",
                'activated_count' => $activatedCount
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => '활성화 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * 일괄 비활성화
     * POST /admin/auth/bulk/deactivate
     */
    public function deactivate(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'reason' => 'required|string|max:500',
            'until_date' => 'nullable|date|after:now'
        ]);
        
        DB::beginTransaction();
        try {
            $deactivatedCount = 0;
            $users = User::whereIn('id', $request->user_ids)->get();
            
            foreach ($users as $user) {
                // 관리자 계정은 비활성화 불가
                if ($user->is_admin) {
                    continue;
                }
                
                // 계정 비활성화
                $user->status = 'suspended';
                $user->suspended_at = now();
                $user->suspended_until = $request->until_date;
                $user->suspended_reason = $request->reason;
                $user->save();
                
                $deactivatedCount++;
                
                // 활동 로그 기록
                DB::table('auth_account_logs')->insert([
                    'user_id' => $user->id,
                    'event' => 'bulk_deactivated',
                    'description' => '관리자에 의한 일괄 비활성화: ' . $request->reason,
                    'performed_by' => auth()->id(),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'created_at' => now()
                ]);
                
                // 현재 세션 종료
                DB::table('sessions')->where('user_id', $user->id)->delete();
                DB::table('auth_user_sessions')->where('user_id', $user->id)
                    ->whereNull('logged_out_at')
                    ->update(['logged_out_at' => now()]);
            }
            
            DB::commit();
            
            activity()
                ->causedBy(auth()->user())
                ->withProperties([
                    'user_ids' => $request->user_ids,
                    'deactivated_count' => $deactivatedCount,
                    'reason' => $request->reason
                ])
                ->log('사용자 일괄 비활성화');
            
            return response()->json([
                'success' => true,
                'message' => "{$deactivatedCount}명의 사용자가 비활성화되었습니다.",
                'deactivated_count' => $deactivatedCount
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => '비활성화 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * 일괄 삭제
     * POST /admin/auth/bulk/delete
     */
    public function delete(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'delete_type' => 'required|in:soft,hard',
            'backup' => 'boolean',
            'admin_password' => 'required' // 관리자 비밀번호 확인
        ]);
        
        // 관리자 비밀번호 확인
        if (!Hash::check($request->admin_password, auth()->user()->password)) {
            return response()->json([
                'success' => false,
                'message' => '관리자 비밀번호가 일치하지 않습니다.'
            ], 401);
        }
        
        DB::beginTransaction();
        try {
            $deletedCount = 0;
            $backupData = [];
            
            $users = User::whereIn('id', $request->user_ids)
                ->where('is_admin', false) // 관리자는 삭제 불가
                ->get();
            
            foreach ($users as $user) {
                // 백업 옵션이 활성화된 경우
                if ($request->backup) {
                    $backupData[] = $this->backupUserData($user);
                }
                
                if ($request->delete_type === 'soft') {
                    // 소프트 삭제
                    $user->delete();
                } else {
                    // 하드 삭제 - 관련 데이터도 모두 삭제
                    $this->hardDeleteUser($user);
                }
                
                $deletedCount++;
                
                // 활동 로그 기록
                DB::table('auth_account_logs')->insert([
                    'user_id' => $user->id,
                    'event' => 'bulk_deleted',
                    'description' => "관리자에 의한 일괄 삭제 ({$request->delete_type})",
                    'performed_by' => auth()->id(),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'created_at' => now()
                ]);
            }
            
            // 백업 데이터 저장
            if ($request->backup && !empty($backupData)) {
                $this->saveBackupData($backupData);
            }
            
            DB::commit();
            
            activity()
                ->causedBy(auth()->user())
                ->withProperties([
                    'user_ids' => $request->user_ids,
                    'deleted_count' => $deletedCount,
                    'delete_type' => $request->delete_type,
                    'backup' => $request->backup
                ])
                ->log('사용자 일괄 삭제');
            
            return response()->json([
                'success' => true,
                'message' => "{$deletedCount}명의 사용자가 삭제되었습니다.",
                'deleted_count' => $deletedCount
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => '삭제 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * 일괄 내보내기
     * POST /admin/auth/bulk/export
     */
    public function export(Request $request)
    {
        $request->validate([
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'exists:users,id',
            'format' => 'required|in:csv,excel,json',
            'fields' => 'required|array',
            'fields.*' => 'string'
        ]);
        
        // 사용자 데이터 조회
        $query = User::query();
        
        if ($request->has('user_ids')) {
            $query->whereIn('id', $request->user_ids);
        }
        
        // 필터 적용
        if ($request->has('filters')) {
            $this->applyFilters($query, $request->filters);
        }
        
        $users = $query->get();
        
        // 필드 필터링
        $exportData = [];
        foreach ($users as $user) {
            $userData = [];
            foreach ($request->fields as $field) {
                // 개인정보 보호 - 민감한 필드 마스킹
                if (in_array($field, ['password', 'remember_token'])) {
                    continue;
                }
                $userData[$field] = $user->$field;
            }
            $exportData[] = $userData;
        }
        
        // 형식에 따라 내보내기
        switch ($request->format) {
            case 'csv':
                return $this->exportAsCSV($exportData);
            case 'excel':
                return $this->exportAsExcel($exportData);
            case 'json':
                return response()->json($exportData)
                    ->header('Content-Disposition', 'attachment; filename="users_' . date('YmdHis') . '.json"');
        }
    }
    
    /**
     * 일괄 가져오기
     * POST /admin/auth/bulk/import
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx,xls|max:10240',
            'update_existing' => 'boolean'
        ]);
        
        $file = $request->file('file');
        $updateExisting = $request->update_existing ?? false;
        
        DB::beginTransaction();
        try {
            $data = $this->parseImportFile($file);
            $importedCount = 0;
            $failedRows = [];
            
            foreach ($data as $index => $row) {
                try {
                    // 이메일 중복 체크
                    $existingUser = User::where('email', $row['email'])->first();
                    
                    if ($existingUser) {
                        if ($updateExisting) {
                            // 기존 사용자 업데이트
                            $existingUser->update($this->prepareUserData($row));
                            $importedCount++;
                        } else {
                            $failedRows[] = [
                                'row' => $index + 2, // 헤더 제외
                                'email' => $row['email'],
                                'reason' => '이미 존재하는 이메일'
                            ];
                        }
                    } else {
                        // 새 사용자 생성
                        User::create($this->prepareUserData($row));
                        $importedCount++;
                    }
                } catch (\Exception $e) {
                    $failedRows[] = [
                        'row' => $index + 2,
                        'email' => $row['email'] ?? 'N/A',
                        'reason' => $e->getMessage()
                    ];
                }
            }
            
            DB::commit();
            
            activity()
                ->causedBy(auth()->user())
                ->withProperties([
                    'imported_count' => $importedCount,
                    'failed_count' => count($failedRows)
                ])
                ->log('사용자 일괄 가져오기');
            
            return response()->json([
                'success' => true,
                'message' => "{$importedCount}명의 사용자가 가져오기되었습니다.",
                'imported_count' => $importedCount,
                'failed_rows' => $failedRows
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => '가져오기 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * 일괄 이메일 발송
     * POST /admin/auth/bulk/send-email
     */
    public function sendEmail(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'template_id' => 'nullable|exists:auth_email_templates,id',
            'subject' => 'required_without:template_id|string|max:255',
            'content' => 'required_without:template_id|string',
            'schedule_at' => 'nullable|date|after:now'
        ]);
        
        $users = User::whereIn('id', $request->user_ids)->get();
        $sentCount = 0;
        
        foreach ($users as $user) {
            // 이메일 큐에 추가
            $mailData = [
                'user' => $user,
                'subject' => $request->subject,
                'content' => $request->content,
                'template_id' => $request->template_id
            ];
            
            if ($request->schedule_at) {
                // 예약 발송
                \App\Jobs\SendBulkEmail::dispatch($mailData)
                    ->delay(Carbon::parse($request->schedule_at));
            } else {
                // 즉시 발송
                \App\Jobs\SendBulkEmail::dispatch($mailData);
            }
            
            $sentCount++;
        }
        
        activity()
            ->causedBy(auth()->user())
            ->withProperties([
                'user_count' => $sentCount,
                'subject' => $request->subject
            ])
            ->log('일괄 이메일 발송');
        
        return response()->json([
            'success' => true,
            'message' => "{$sentCount}명에게 이메일이 발송되었습니다.",
            'sent_count' => $sentCount
        ]);
    }
    
    /**
     * 일괄 비밀번호 재설정
     * POST /admin/auth/bulk/reset-password
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'send_email' => 'boolean'
        ]);
        
        DB::beginTransaction();
        try {
            $resetCount = 0;
            $users = User::whereIn('id', $request->user_ids)->get();
            
            foreach ($users as $user) {
                // 임시 비밀번호 생성
                $tempPassword = Str::random(12);
                
                // 비밀번호 업데이트
                $user->password = Hash::make($tempPassword);
                $user->password_force_change = true; // 다음 로그인 시 변경 강제
                $user->save();
                
                // 비밀번호 변경 로그
                DB::table('auth_password_logs')->insert([
                    'user_id' => $user->id,
                    'changed_by' => auth()->id(),
                    'change_type' => 'admin_reset',
                    'created_at' => now()
                ]);
                
                // 이메일 발송
                if ($request->send_email ?? true) {
                    Mail::to($user->email)->queue(new \App\Mail\PasswordReset($user, $tempPassword));
                }
                
                $resetCount++;
            }
            
            DB::commit();
            
            activity()
                ->causedBy(auth()->user())
                ->withProperties([
                    'user_ids' => $request->user_ids,
                    'reset_count' => $resetCount
                ])
                ->log('일괄 비밀번호 재설정');
            
            return response()->json([
                'success' => true,
                'message' => "{$resetCount}명의 비밀번호가 재설정되었습니다.",
                'reset_count' => $resetCount
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => '비밀번호 재설정 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * 일괄 등급 변경
     * POST /admin/auth/bulk/change-grade
     */
    public function changeGrade(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'grade_code' => 'required|exists:auth_user_grades,code',
            'reason' => 'required|string|max:500'
        ]);
        
        DB::beginTransaction();
        try {
            $changedCount = 0;
            $grade = DB::table('auth_user_grades')->where('code', $request->grade_code)->first();
            $users = User::whereIn('id', $request->user_ids)->get();
            
            foreach ($users as $user) {
                $oldGrade = $user->grade_code;
                
                // 등급 변경
                $user->grade_code = $request->grade_code;
                $user->save();
                
                // 등급 변경 로그
                DB::table('auth_grade_histories')->insert([
                    'user_id' => $user->id,
                    'old_grade' => $oldGrade,
                    'new_grade' => $request->grade_code,
                    'reason' => $request->reason,
                    'changed_by' => auth()->id(),
                    'created_at' => now()
                ]);
                
                $changedCount++;
            }
            
            DB::commit();
            
            activity()
                ->causedBy(auth()->user())
                ->withProperties([
                    'user_ids' => $request->user_ids,
                    'new_grade' => $request->grade_code,
                    'reason' => $request->reason
                ])
                ->log('일괄 등급 변경');
            
            return response()->json([
                'success' => true,
                'message' => "{$changedCount}명의 등급이 {$grade->name}(으)로 변경되었습니다.",
                'changed_count' => $changedCount
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => '등급 변경 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * 일괄 포인트 지급
     * POST /admin/auth/bulk/add-points
     */
    public function addPoints(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'points' => 'required|integer|min:1',
            'reason' => 'required|string|max:500',
            'expires_at' => 'nullable|date|after:now'
        ]);
        
        DB::beginTransaction();
        try {
            $addedCount = 0;
            $users = User::whereIn('id', $request->user_ids)->get();
            
            foreach ($users as $user) {
                // 포인트 지급
                DB::table('auth_point_wallets')
                    ->where('user_id', $user->id)
                    ->increment('balance', $request->points);
                
                // 포인트 거래 기록
                DB::table('auth_point_transactions')->insert([
                    'user_id' => $user->id,
                    'type' => 'admin_grant',
                    'amount' => $request->points,
                    'balance_after' => DB::table('auth_point_wallets')
                        ->where('user_id', $user->id)
                        ->value('balance'),
                    'description' => $request->reason,
                    'expires_at' => $request->expires_at,
                    'created_by' => auth()->id(),
                    'created_at' => now()
                ]);
                
                $addedCount++;
            }
            
            DB::commit();
            
            activity()
                ->causedBy(auth()->user())
                ->withProperties([
                    'user_ids' => $request->user_ids,
                    'points' => $request->points,
                    'reason' => $request->reason
                ])
                ->log('일괄 포인트 지급');
            
            return response()->json([
                'success' => true,
                'message' => "{$addedCount}명에게 {$request->points} 포인트가 지급되었습니다.",
                'added_count' => $addedCount
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => '포인트 지급 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * 사용자 데이터 백업
     */
    private function backupUserData($user)
    {
        return [
            'user' => $user->toArray(),
            'points' => DB::table('auth_point_wallets')->where('user_id', $user->id)->first(),
            'emoney' => DB::table('auth_emoney_wallets')->where('user_id', $user->id)->first(),
            'messages' => DB::table('auth_messages')->where('sender_id', $user->id)
                ->orWhere('recipient_id', $user->id)->get(),
            'logs' => DB::table('auth_account_logs')->where('user_id', $user->id)->get(),
            'backed_up_at' => now()
        ];
    }
    
    /**
     * 백업 데이터 저장
     */
    private function saveBackupData($data)
    {
        $filename = 'user_backup_' . date('YmdHis') . '.json';
        \Storage::disk('local')->put('backups/' . $filename, json_encode($data));
    }
    
    /**
     * 하드 삭제
     */
    private function hardDeleteUser($user)
    {
        // 관련 데이터 삭제
        DB::table('auth_point_wallets')->where('user_id', $user->id)->delete();
        DB::table('auth_emoney_wallets')->where('user_id', $user->id)->delete();
        DB::table('auth_messages')->where('sender_id', $user->id)
            ->orWhere('recipient_id', $user->id)->delete();
        DB::table('auth_account_logs')->where('user_id', $user->id)->delete();
        DB::table('auth_login_histories')->where('user_id', $user->id)->delete();
        
        // 사용자 삭제
        $user->forceDelete();
    }
    
    /**
     * CSV 내보내기
     */
    private function exportAsCSV($data)
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="users_' . date('YmdHis') . '.csv"',
        ];
        
        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM
            
            // 헤더
            if (!empty($data)) {
                fputcsv($file, array_keys($data[0]));
                
                // 데이터
                foreach ($data as $row) {
                    fputcsv($file, $row);
                }
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
    
    /**
     * 파일 파싱
     */
    private function parseImportFile($file)
    {
        $extension = $file->getClientOriginalExtension();
        
        if ($extension === 'csv') {
            return $this->parseCSV($file);
        } else {
            // Excel 파싱 (별도 패키지 필요)
            return $this->parseExcel($file);
        }
    }
    
    /**
     * CSV 파싱
     */
    private function parseCSV($file)
    {
        $data = [];
        $handle = fopen($file->getRealPath(), 'r');
        $headers = fgetcsv($handle);
        
        while (($row = fgetcsv($handle)) !== FALSE) {
            $data[] = array_combine($headers, $row);
        }
        
        fclose($handle);
        return $data;
    }
    
    /**
     * 사용자 데이터 준비
     */
    private function prepareUserData($row)
    {
        return [
            'name' => $row['name'] ?? '',
            'email' => $row['email'],
            'password' => Hash::make($row['password'] ?? Str::random(12)),
            'phone' => $row['phone'] ?? null,
            'status' => $row['status'] ?? 'active',
            'email_verified_at' => $row['email_verified_at'] ?? now()
        ];
    }
}