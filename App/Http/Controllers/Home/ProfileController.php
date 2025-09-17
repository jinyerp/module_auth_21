<?php

namespace Jiny\Auth\App\Http\Controllers\Home;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use Carbon\Carbon;

class ProfileController extends Controller
{
    /**
     * 프로필 대시보드
     * GET /home/profile
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // 프로필 완성도 계산
        $completeness = $this->calculateProfileCompleteness($user);
        
        // 최근 활동
        $recentActivities = DB::table('user_activity_logs')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        // 주소록
        $addresses = DB::table('user_addresses')
            ->where('user_id', $user->id)
            ->get();
        
        // 소셜 계정
        $socialAccounts = DB::table('social_accounts')
            ->where('user_id', $user->id)
            ->get();
        
        return view('jiny-auth::home.profile.index', compact(
            'user', 
            'completeness', 
            'recentActivities',
            'addresses',
            'socialAccounts'
        ));
    }
    
    /**
     * 프로필 편집 폼
     * GET /home/profile/edit
     */
    public function edit(Request $request)
    {
        $user = Auth::user();
        
        // 국가 목록
        $countries = DB::table('countries')->orderBy('name')->get();
        
        // 언어 목록
        $languages = DB::table('languages')->orderBy('name')->get();
        
        return view('jiny-auth::home.profile.edit', compact('user', 'countries', 'languages'));
    }
    
    /**
     * 프로필 업데이트
     * PUT /home/profile
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'birthdate' => 'nullable|date|before:today',
            'gender' => 'nullable|in:male,female,other',
            'bio' => 'nullable|string|max:500',
            'country_id' => 'nullable|exists:countries,id',
            'language' => 'nullable|string|max:5',
            'timezone' => 'nullable|string|max:50',
        ]);
        
        $user->update($request->only([
            'name', 'phone', 'birthdate', 'gender', 
            'bio', 'country_id', 'language', 'timezone'
        ]));
        
        // 활동 로그 기록
        $this->logActivity($user->id, 'profile_updated', '프로필 정보 업데이트');
        
        return redirect()->route('home.profile')
            ->with('success', '프로필이 업데이트되었습니다.');
    }
    
    /**
     * 아바타 관리 페이지
     * GET /home/profile/avatar
     */
    public function avatar(Request $request)
    {
        $user = Auth::user();
        
        // 아바타 변경 이력
        $avatarHistory = DB::table('user_avatar_history')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        return view('jiny-auth::home.profile.avatar', compact('user', 'avatarHistory'));
    }
    
    /**
     * 아바타 업로드/수정
     * POST /home/profile/avatar
     */
    public function updateAvatar(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);
        
        // 이전 아바타 이력에 저장
        if ($user->avatar) {
            DB::table('user_avatar_history')->insert([
                'user_id' => $user->id,
                'avatar_path' => $user->avatar,
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
        
        // 활동 로그 기록
        $this->logActivity($user->id, 'avatar_updated', '아바타 이미지 변경');
        
        return back()->with('success', '아바타가 업데이트되었습니다.');
    }
    
    /**
     * 아바타 변경 이력
     * GET /home/profile/avatar/history
     */
    public function avatarHistory(Request $request)
    {
        $user = Auth::user();
        
        $history = DB::table('user_avatar_history')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        return view('jiny-auth::home.profile.avatar-history', compact('history'));
    }
    
    /**
     * 주소록 관리
     * GET /home/profile/addresses
     */
    public function addresses(Request $request)
    {
        $user = Auth::user();
        
        $addresses = DB::table('user_addresses')
            ->where('user_id', $user->id)
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('jiny-auth::home.profile.addresses', compact('addresses'));
    }
    
    /**
     * 주소 추가
     * POST /home/profile/addresses
     */
    public function addAddress(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'type' => 'required|in:home,office,shipping,billing,other',
            'name' => 'required|string|max:100',
            'phone' => 'required|string|max:20',
            'address_line_1' => 'required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'required|string|max:20',
            'country' => 'required|string|max:2',
            'is_default' => 'boolean'
        ]);
        
        // 기본 주소 설정 시 기존 기본 주소 해제
        if ($request->is_default) {
            DB::table('user_addresses')
                ->where('user_id', $user->id)
                ->update(['is_default' => false]);
        }
        
        DB::table('user_addresses')->insert([
            'user_id' => $user->id,
            'type' => $request->type,
            'name' => $request->name,
            'phone' => $request->phone,
            'address_line_1' => $request->address_line_1,
            'address_line_2' => $request->address_line_2,
            'city' => $request->city,
            'state' => $request->state,
            'postal_code' => $request->postal_code,
            'country' => $request->country,
            'is_default' => $request->is_default ?? false,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        return back()->with('success', '주소가 추가되었습니다.');
    }
    
    /**
     * 주소 수정
     * PUT /home/profile/addresses/{id}
     */
    public function updateAddress(Request $request, $id)
    {
        $user = Auth::user();
        
        // 사용자 소유 확인
        $address = DB::table('user_addresses')
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->first();
        
        if (!$address) {
            return back()->with('error', '주소를 찾을 수 없습니다.');
        }
        
        $request->validate([
            'type' => 'required|in:home,office,shipping,billing,other',
            'name' => 'required|string|max:100',
            'phone' => 'required|string|max:20',
            'address_line_1' => 'required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'required|string|max:20',
            'country' => 'required|string|max:2',
            'is_default' => 'boolean'
        ]);
        
        // 기본 주소 설정 시 기존 기본 주소 해제
        if ($request->is_default) {
            DB::table('user_addresses')
                ->where('user_id', $user->id)
                ->where('id', '!=', $id)
                ->update(['is_default' => false]);
        }
        
        DB::table('user_addresses')
            ->where('id', $id)
            ->update([
                'type' => $request->type,
                'name' => $request->name,
                'phone' => $request->phone,
                'address_line_1' => $request->address_line_1,
                'address_line_2' => $request->address_line_2,
                'city' => $request->city,
                'state' => $request->state,
                'postal_code' => $request->postal_code,
                'country' => $request->country,
                'is_default' => $request->is_default ?? false,
                'updated_at' => now()
            ]);
        
        return back()->with('success', '주소가 수정되었습니다.');
    }
    
    /**
     * 주소 삭제
     * DELETE /home/profile/addresses/{id}
     */
    public function deleteAddress(Request $request, $id)
    {
        $user = Auth::user();
        
        // 사용자 소유 확인
        $deleted = DB::table('user_addresses')
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->delete();
        
        if (!$deleted) {
            return back()->with('error', '주소를 찾을 수 없습니다.');
        }
        
        return back()->with('success', '주소가 삭제되었습니다.');
    }
    
    /**
     * 보안 설정
     * GET /home/profile/security
     */
    public function security(Request $request)
    {
        $user = Auth::user();
        
        // 로그인 기록
        $loginHistory = DB::table('login_history')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        // 활성 세션
        $activeSessions = DB::table('user_sessions')
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->get();
        
        return view('jiny-auth::home.profile.security', compact('user', 'loginHistory', 'activeSessions'));
    }
    
    /**
     * 2FA 설정
     * POST /home/profile/security/2fa
     */
    public function enable2FA(Request $request)
    {
        // 2FA 컨트롤러로 위임
        return redirect()->route('2fa.setup');
    }
    
    /**
     * 소셜 계정 관리
     * GET /home/profile/social
     */
    public function socialAccounts(Request $request)
    {
        $user = Auth::user();
        
        $socialAccounts = DB::table('social_accounts')
            ->where('user_id', $user->id)
            ->get();
        
        // 연결 가능한 소셜 플랫폼
        $availableProviders = ['google', 'facebook', 'github', 'twitter', 'linkedin'];
        
        $connectedProviders = $socialAccounts->pluck('provider')->toArray();
        $disconnectedProviders = array_diff($availableProviders, $connectedProviders);
        
        return view('jiny-auth::home.profile.social', compact(
            'socialAccounts', 
            'disconnectedProviders'
        ));
    }
    
    /**
     * 소셜 계정 연결 해제
     * DELETE /home/profile/social/{provider}
     */
    public function disconnectSocial(Request $request, $provider)
    {
        $user = Auth::user();
        
        // 최소 1개의 로그인 방법 확인
        $loginMethods = 0;
        if ($user->password) $loginMethods++;
        
        $socialCount = DB::table('social_accounts')
            ->where('user_id', $user->id)
            ->count();
        
        if ($socialCount <= 1 && !$user->password) {
            return back()->with('error', '최소 하나의 로그인 방법이 필요합니다.');
        }
        
        $deleted = DB::table('social_accounts')
            ->where('user_id', $user->id)
            ->where('provider', $provider)
            ->delete();
        
        if (!$deleted) {
            return back()->with('error', '연결된 계정을 찾을 수 없습니다.');
        }
        
        // 활동 로그 기록
        $this->logActivity($user->id, 'social_disconnected', "{$provider} 계정 연결 해제");
        
        return back()->with('success', "{$provider} 계정 연결이 해제되었습니다.");
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
     * 활동 로그 기록
     */
    private function logActivity($userId, $action, $description)
    {
        DB::table('user_activity_logs')->insert([
            'user_id' => $userId,
            'action' => $action,
            'description' => $description,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now()
        ]);
    }
}