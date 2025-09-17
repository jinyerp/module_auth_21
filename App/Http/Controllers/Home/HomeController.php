<?php

namespace Jiny\Auth\App\Http\Controllers\Home;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * 사용자 홈 대시보드 컨트롤러
 * 인증된 사용자의 메인 대시보드 관리
 */
class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * 사용자 대시보드 표시
     * GET /home
     */
    public function index()
    {
        $user = Auth::user();
        
        // 최근 활동 내역
        $recentActivities = DB::table('account_logs')
            ->where('account_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // 로그인 통계
        $loginStats = [
            'today' => DB::table('login_histories')
                ->where('account_id', $user->id)
                ->whereDate('login_at', Carbon::today())
                ->count(),
            'this_week' => DB::table('login_histories')
                ->where('account_id', $user->id)
                ->whereBetween('login_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
                ->count(),
            'this_month' => DB::table('login_histories')
                ->where('account_id', $user->id)
                ->whereMonth('login_at', Carbon::now()->month)
                ->count(),
        ];

        // 계정 상태 정보
        $accountInfo = [
            'email_verified' => !is_null($user->email_verified_at),
            'two_factor_enabled' => DB::table('two_factor_auths')
                ->where('account_id', $user->id)
                ->where('enabled', true)
                ->exists(),
            'active_sessions' => DB::table('sessions')
                ->where('user_id', $user->id)
                ->count(),
            'last_password_change' => $user->password_changed_at ?? $user->created_at,
            'account_age' => $user->created_at->diffForHumans(),
        ];

        // 보안 알림
        $securityAlerts = [];
        
        // 비밀번호 변경 권장 (90일 이상)
        if ($user->password_changed_at) {
            $daysSinceChange = Carbon::parse($user->password_changed_at)->diffInDays(now());
            if ($daysSinceChange > 90) {
                $securityAlerts[] = [
                    'type' => 'warning',
                    'message' => "비밀번호를 변경한지 {$daysSinceChange}일이 경과했습니다. 보안을 위해 비밀번호 변경을 권장합니다."
                ];
            }
        }

        // 이메일 미인증
        if (is_null($user->email_verified_at)) {
            $securityAlerts[] = [
                'type' => 'info',
                'message' => '이메일 인증이 완료되지 않았습니다. 이메일을 인증해주세요.'
            ];
        }

        // 2FA 미설정
        if (!$accountInfo['two_factor_enabled']) {
            $securityAlerts[] = [
                'type' => 'info',
                'message' => '2단계 인증이 설정되지 않았습니다. 계정 보안을 위해 2FA 설정을 권장합니다.'
            ];
        }

        return view('jiny-auth::home.dashboard', compact(
            'user',
            'recentActivities',
            'loginStats',
            'accountInfo',
            'securityAlerts'
        ));
    }

    /**
     * 사용자 프로필 조회
     * GET /home/profile
     */
    public function profile()
    {
        $user = Auth::user();
        
        // 추가 프로필 정보
        $profile = [
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'avatar' => $user->avatar,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
            'email_verified_at' => $user->email_verified_at,
            'last_login_at' => $user->last_login_at,
            'login_count' => $user->login_count ?? 0,
        ];

        // 연결된 소셜 계정
        $socialAccounts = DB::table('social_accounts')
            ->where('account_id', $user->id)
            ->get();

        return view('jiny-auth::home.profile', compact('user', 'profile', 'socialAccounts'));
    }

    /**
     * 프로필 수정 폼
     * GET /home/profile/edit
     */
    public function editProfile()
    {
        $user = Auth::user();
        return view('jiny-auth::home.profile-edit', compact('user'));
    }

    /**
     * 프로필 수정 처리
     * PUT /home/profile
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|regex:/^[0-9-+()]+$/',
            'avatar' => 'nullable|image|max:2048', // 2MB 제한
        ]);

        // 아바타 업로드 처리
        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $validated['avatar'] = $avatarPath;
            
            // 기존 아바타 삭제
            if ($user->avatar && \Storage::disk('public')->exists($user->avatar)) {
                \Storage::disk('public')->delete($user->avatar);
            }
        }

        $user->update($validated);

        // 활동 로그 기록
        DB::table('account_logs')->insert([
            'account_id' => $user->id,
            'action' => 'profile_update',
            'description' => '프로필 정보 수정',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now()
        ]);

        return redirect()->route('home.profile')
            ->with('success', '프로필이 성공적으로 수정되었습니다.');
    }

    /**
     * 계정 설정 페이지
     * GET /home/settings
     */
    public function settings()
    {
        $user = Auth::user();
        
        // 계정 설정 정보
        $settings = [
            'email_notifications' => $user->email_notifications ?? true,
            'sms_notifications' => $user->sms_notifications ?? false,
            'two_factor_enabled' => DB::table('two_factor_auths')
                ->where('account_id', $user->id)
                ->where('enabled', true)
                ->exists(),
            'login_alerts' => $user->login_alerts ?? true,
            'newsletter' => $user->newsletter ?? false,
        ];

        return view('jiny-auth::home.settings', compact('user', 'settings'));
    }

    /**
     * 계정 설정 수정
     * PUT /home/settings
     */
    public function updateSettings(Request $request)
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'email_notifications' => 'boolean',
            'sms_notifications' => 'boolean',
            'login_alerts' => 'boolean',
            'newsletter' => 'boolean',
        ]);

        $user->update($validated);

        // 활동 로그 기록
        DB::table('account_logs')->insert([
            'account_id' => $user->id,
            'action' => 'settings_update',
            'description' => '계정 설정 변경',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now()
        ]);

        return redirect()->route('home.settings')
            ->with('success', '설정이 성공적으로 저장되었습니다.');
    }

    /**
     * 계정 삭제 페이지
     * GET /home/account/delete
     */
    public function deleteForm()
    {
        return view('jiny-auth::home.account-delete');
    }

    /**
     * 계정 삭제 처리
     * DELETE /home/account
     */
    public function deleteAccount(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'password' => 'required|current_password',
            'confirm' => 'required|in:DELETE',
        ]);

        // 활동 로그 기록
        DB::table('account_logs')->insert([
            'account_id' => $user->id,
            'action' => 'account_delete',
            'description' => '계정 삭제',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now()
        ]);

        // 로그아웃
        Auth::logout();
        
        // 계정 삭제 (소프트 삭제)
        $user->delete();

        return redirect('/')->with('success', '계정이 성공적으로 삭제되었습니다.');
    }
}