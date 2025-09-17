<?php

namespace Jiny\Auth\App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class AdminSocialController extends Controller
{
    /**
     * 소셜 로그인 설정
     * GET /admin/auth/social
     */
    public function index(Request $request)
    {
        $providers = DB::table('oauth_providers')
            ->orderBy('priority')
            ->get();
        
        // 통계
        $statistics = [];
        foreach ($providers as $provider) {
            $statistics[$provider->name] = [
                'total_users' => DB::table('social_accounts')
                    ->where('provider', $provider->name)
                    ->count(),
                'active_users' => DB::table('social_accounts')
                    ->join('users', 'social_accounts.user_id', '=', 'users.id')
                    ->where('social_accounts.provider', $provider->name)
                    ->where('users.is_active', true)
                    ->count(),
                'recent_logins' => DB::table('social_login_logs')
                    ->where('provider', $provider->name)
                    ->where('action', 'login')
                    ->where('created_at', '>=', now()->subDays(7))
                    ->count(),
            ];
        }
        
        return view('jiny-auth::admin.social.index', compact('providers', 'statistics'));
    }
    
    /**
     * OAuth 공급자 관리
     * GET /admin/auth/oauth
     */
    public function oauth(Request $request)
    {
        $providers = DB::table('oauth_providers')
            ->orderBy('priority')
            ->get();
        
        return view('jiny-auth::admin.social.oauth', compact('providers'));
    }
    
    /**
     * OAuth 공급자 업데이트
     * PUT /admin/auth/oauth/{id}
     */
    public function updateProvider(Request $request, $id)
    {
        $request->validate([
            'enabled' => 'required|boolean',
            'client_id' => 'nullable|string|max:255',
            'client_secret' => 'nullable|string|max:255',
            'redirect_uri' => 'nullable|url',
            'scopes' => 'nullable|array',
            'priority' => 'required|integer|min:0',
        ]);
        
        $provider = DB::table('oauth_providers')->where('id', $id)->first();
        
        if (!$provider) {
            return back()->with('error', '공급자를 찾을 수 없습니다.');
        }
        
        DB::table('oauth_providers')
            ->where('id', $id)
            ->update([
                'enabled' => $request->enabled,
                'client_id' => $request->client_id,
                'client_secret' => $request->client_secret,
                'redirect_uri' => $request->redirect_uri,
                'scopes' => json_encode($request->scopes),
                'priority' => $request->priority,
                'updated_at' => now(),
            ]);
        
        return back()->with('success', ucfirst($provider->name) . ' 설정이 업데이트되었습니다.');
    }
    
    /**
     * 소셜 로그인 사용자 목록
     * GET /admin/auth/oauth/users/{provider}
     */
    public function users(Request $request, $provider)
    {
        $query = DB::table('social_accounts')
            ->join('users', 'social_accounts.user_id', '=', 'users.id')
            ->where('social_accounts.provider', $provider)
            ->select(
                'social_accounts.*',
                'users.name as user_name',
                'users.email as user_email',
                'users.is_active',
                'users.created_at as user_created_at'
            );
        
        // 검색
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('users.name', 'like', "%{$search}%")
                  ->orWhere('users.email', 'like', "%{$search}%")
                  ->orWhere('social_accounts.name', 'like', "%{$search}%")
                  ->orWhere('social_accounts.email', 'like', "%{$search}%");
            });
        }
        
        $users = $query->orderBy('social_accounts.created_at', 'desc')
            ->paginate(20);
        
        // 공급자 정보
        $providerInfo = DB::table('oauth_providers')
            ->where('name', $provider)
            ->first();
        
        return view('jiny-auth::admin.social.users', compact('users', 'provider', 'providerInfo'));
    }
    
    /**
     * 소셜 계정 상세 정보
     * GET /admin/auth/social/accounts/{id}
     */
    public function accountDetails(Request $request, $id)
    {
        $account = DB::table('social_accounts')
            ->join('users', 'social_accounts.user_id', '=', 'users.id')
            ->where('social_accounts.id', $id)
            ->select(
                'social_accounts.*',
                'users.name as user_name',
                'users.email as user_email',
                'users.is_active'
            )
            ->first();
        
        if (!$account) {
            return redirect()->route('admin.auth.social')
                ->with('error', '소셜 계정을 찾을 수 없습니다.');
        }
        
        // 로그인 이력
        $loginHistory = DB::table('social_login_logs')
            ->where('user_id', $account->user_id)
            ->where('provider', $account->provider)
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();
        
        return view('jiny-auth::admin.social.account-details', compact('account', 'loginHistory'));
    }
    
    /**
     * 소셜 계정 연결 해제
     * DELETE /admin/auth/social/accounts/{id}
     */
    public function disconnectAccount(Request $request, $id)
    {
        $account = DB::table('social_accounts')->where('id', $id)->first();
        
        if (!$account) {
            return response()->json([
                'success' => false,
                'message' => '소셜 계정을 찾을 수 없습니다.'
            ], 404);
        }
        
        // 사용자의 로그인 방법 확인
        $user = User::find($account->user_id);
        $socialCount = DB::table('social_accounts')
            ->where('user_id', $account->user_id)
            ->count();
        
        if ($socialCount <= 1 && !$user->password) {
            return response()->json([
                'success' => false,
                'message' => '사용자에게 최소 하나의 로그인 방법이 필요합니다.'
            ], 400);
        }
        
        // 연결 해제
        DB::table('social_accounts')->where('id', $id)->delete();
        
        // 로그 기록
        DB::table('social_login_logs')->insert([
            'user_id' => $account->user_id,
            'provider' => $account->provider,
            'action' => 'disconnect',
            'status' => 'success',
            'metadata' => json_encode(['admin_id' => Auth::id()]),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);
        
        return response()->json([
            'success' => true,
            'message' => '소셜 계정 연결이 해제되었습니다.'
        ]);
    }
    
    /**
     * 소셜 로그인 통계
     * GET /admin/auth/social/statistics
     */
    public function statistics(Request $request)
    {
        // 전체 통계
        $totalStats = [
            'total_social_users' => DB::table('social_accounts')
                ->distinct('user_id')
                ->count('user_id'),
            'total_connections' => DB::table('social_accounts')->count(),
            'recent_registrations' => DB::table('social_login_logs')
                ->where('action', 'register')
                ->where('created_at', '>=', now()->subDays(30))
                ->count(),
            'recent_logins' => DB::table('social_login_logs')
                ->where('action', 'login')
                ->where('created_at', '>=', now()->subDays(7))
                ->count(),
        ];
        
        // 공급자별 통계
        $providerStats = DB::table('oauth_providers')
            ->leftJoin('social_accounts', 'oauth_providers.name', '=', 'social_accounts.provider')
            ->select(
                'oauth_providers.name',
                'oauth_providers.display_name',
                'oauth_providers.enabled',
                DB::raw('COUNT(DISTINCT social_accounts.user_id) as user_count'),
                DB::raw('COUNT(social_accounts.id) as connection_count')
            )
            ->groupBy('oauth_providers.name', 'oauth_providers.display_name', 'oauth_providers.enabled')
            ->get();
        
        // 월별 트렌드 (최근 12개월)
        $monthlyTrends = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthKey = $month->format('Y-m');
            
            $monthlyTrends[$monthKey] = [
                'registrations' => DB::table('social_login_logs')
                    ->where('action', 'register')
                    ->whereYear('created_at', $month->year)
                    ->whereMonth('created_at', $month->month)
                    ->count(),
                'logins' => DB::table('social_login_logs')
                    ->where('action', 'login')
                    ->whereYear('created_at', $month->year)
                    ->whereMonth('created_at', $month->month)
                    ->count(),
                'connections' => DB::table('social_login_logs')
                    ->where('action', 'connect')
                    ->whereYear('created_at', $month->year)
                    ->whereMonth('created_at', $month->month)
                    ->count(),
            ];
        }
        
        // 최근 오류
        $recentErrors = DB::table('social_login_logs')
            ->where('status', 'error')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();
        
        return view('jiny-auth::admin.social.statistics', compact(
            'totalStats',
            'providerStats',
            'monthlyTrends',
            'recentErrors'
        ));
    }
}