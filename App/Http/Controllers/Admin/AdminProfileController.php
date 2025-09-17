<?php

namespace Jiny\Auth\App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use Carbon\Carbon;

class AdminProfileController extends Controller
{
    /**
     * 사용자 프로필 조회
     * GET /admin/auth/users/{id}/profile
     */
    public function show(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        // 프로필 완성도
        $completeness = $this->calculateProfileCompleteness($user);
        
        // 주소록
        $addresses = DB::table('user_addresses')
            ->where('user_id', $user->id)
            ->get();
        
        // 소셜 계정
        $socialAccounts = DB::table('social_accounts')
            ->where('user_id', $user->id)
            ->get();
        
        // 최근 활동
        $activities = DB::table('user_activity_logs')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();
        
        // 아바타 변경 이력
        $avatarHistory = DB::table('user_avatar_history')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        return view('jiny-auth::admin.profile.show', compact(
            'user',
            'completeness',
            'addresses',
            'socialAccounts',
            'activities',
            'avatarHistory'
        ));
    }
    
    /**
     * 사용자 프로필 수정
     * PUT /admin/auth/users/{id}/profile
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'birthdate' => 'nullable|date|before:today',
            'gender' => 'nullable|in:male,female,other',
            'bio' => 'nullable|string|max:500',
            'country_id' => 'nullable|exists:countries,id',
            'language' => 'nullable|string|max:5',
            'timezone' => 'nullable|string|max:50',
            'is_active' => 'boolean',
            'is_admin' => 'boolean',
            'email_verified_at' => 'nullable|date',
        ]);
        
        $user->update($request->only([
            'name', 'email', 'phone', 'birthdate', 'gender',
            'bio', 'country_id', 'language', 'timezone',
            'is_active', 'is_admin'
        ]));
        
        // 이메일 인증 처리
        if ($request->has('email_verified_at')) {
            $user->email_verified_at = $request->email_verified_at;
            $user->save();
        }
        
        // 관리자 활동 로그
        $this->logAdminActivity(
            $user->id,
            'profile_updated_by_admin',
            '관리자가 프로필 수정',
            $request
        );
        
        return redirect()->route('admin.auth.users.profile', $id)
            ->with('success', '사용자 프로필이 업데이트되었습니다.');
    }
    
    /**
     * 사용자 아바타 업로드
     * POST /admin/auth/users/{id}/avatar
     */
    public function uploadAvatar(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);
        
        // 이전 아바타 이력에 저장
        if ($user->avatar) {
            DB::table('user_avatar_history')->insert([
                'user_id' => $user->id,
                'avatar_path' => $user->avatar,
                'changed_by' => Auth::id(),
                'created_at' => now()
            ]);
        }
        
        // 새 아바타 저장
        $path = $request->file('avatar')->store('avatars', 'public');
        
        // 이전 아바타 파일 삭제
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }
        
        $user->update(['avatar' => $path]);
        
        // 관리자 활동 로그
        $this->logAdminActivity(
            $user->id,
            'avatar_updated_by_admin',
            '관리자가 아바타 변경',
            $request
        );
        
        return back()->with('success', '아바타가 업데이트되었습니다.');
    }
    
    /**
     * 사용자 아바타 삭제
     * DELETE /admin/auth/users/{id}/avatar
     */
    public function deleteAvatar(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        if (!$user->avatar) {
            return back()->with('error', '아바타가 없습니다.');
        }
        
        // 이전 아바타 이력에 저장
        DB::table('user_avatar_history')->insert([
            'user_id' => $user->id,
            'avatar_path' => $user->avatar,
            'changed_by' => Auth::id(),
            'deleted' => true,
            'created_at' => now()
        ]);
        
        // 아바타 파일 삭제
        Storage::disk('public')->delete($user->avatar);
        
        $user->update(['avatar' => null]);
        
        // 관리자 활동 로그
        $this->logAdminActivity(
            $user->id,
            'avatar_deleted_by_admin',
            '관리자가 아바타 삭제',
            $request
        );
        
        return back()->with('success', '아바타가 삭제되었습니다.');
    }
    
    /**
     * 프로필 변경 이력
     * GET /admin/auth/users/{id}/profile/history
     */
    public function history(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        // 프로필 변경 이력
        $profileHistory = DB::table('user_activity_logs')
            ->where('user_id', $user->id)
            ->whereIn('action', ['profile_updated', 'profile_updated_by_admin'])
            ->orderBy('created_at', 'desc')
            ->paginate(50);
        
        // 아바타 변경 이력
        $avatarHistory = DB::table('user_avatar_history')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();
        
        // 주소 변경 이력
        $addressHistory = DB::table('user_activity_logs')
            ->where('user_id', $user->id)
            ->whereIn('action', ['address_added', 'address_updated', 'address_deleted'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('jiny-auth::admin.profile.history', compact(
            'user',
            'profileHistory',
            'avatarHistory',
            'addressHistory'
        ));
    }
    
    /**
     * 추가정보 조회
     * GET /admin/auth/users/{id}/additional
     */
    public function additional(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        // 추가 정보 조회
        $additionalInfo = DB::table('user_additional_info')
            ->where('user_id', $user->id)
            ->first();
        
        // 커스텀 필드
        $customFields = DB::table('user_custom_fields')
            ->where('user_id', $user->id)
            ->get();
        
        return view('jiny-auth::admin.profile.additional', compact(
            'user',
            'additionalInfo',
            'customFields'
        ));
    }
    
    /**
     * 추가정보 수정
     * PUT /admin/auth/users/{id}/additional
     */
    public function updateAdditional(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        $request->validate([
            'company' => 'nullable|string|max:100',
            'job_title' => 'nullable|string|max:100',
            'department' => 'nullable|string|max:100',
            'website' => 'nullable|url|max:255',
            'social_media' => 'nullable|json',
            'preferences' => 'nullable|json',
            'notes' => 'nullable|string',
        ]);
        
        DB::table('user_additional_info')->updateOrInsert(
            ['user_id' => $user->id],
            array_merge(
                $request->only([
                    'company', 'job_title', 'department',
                    'website', 'social_media', 'preferences', 'notes'
                ]),
                ['updated_at' => now()]
            )
        );
        
        // 커스텀 필드 처리
        if ($request->has('custom_fields')) {
            foreach ($request->custom_fields as $key => $value) {
                DB::table('user_custom_fields')->updateOrInsert(
                    ['user_id' => $user->id, 'field_key' => $key],
                    ['field_value' => $value, 'updated_at' => now()]
                );
            }
        }
        
        // 관리자 활동 로그
        $this->logAdminActivity(
            $user->id,
            'additional_info_updated_by_admin',
            '관리자가 추가정보 수정',
            $request
        );
        
        return back()->with('success', '추가정보가 업데이트되었습니다.');
    }
    
    /**
     * 프로필 완성도 계산
     */
    private function calculateProfileCompleteness($user)
    {
        $fields = [
            'name' => 20,
            'email' => 20,
            'phone' => 10,
            'avatar' => 10,
            'birthdate' => 10,
            'gender' => 5,
            'bio' => 10,
            'country_id' => 5,
            'language' => 5,
            'timezone' => 5,
        ];
        
        $completed = 0;
        foreach ($fields as $field => $weight) {
            if (!empty($user->$field)) {
                $completed += $weight;
            }
        }
        
        return $completed;
    }
    
    /**
     * 관리자 활동 로그 기록
     */
    private function logAdminActivity($userId, $action, $description, $request)
    {
        DB::table('user_activity_logs')->insert([
            'user_id' => $userId,
            'action' => $action,
            'description' => $description,
            'admin_id' => Auth::id(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now()
        ]);
    }
}