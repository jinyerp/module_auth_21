<?php

namespace Jiny\Auth\App\Http\Controllers\Home;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Laravel\Socialite\Facades\Socialite;

class SocialAccountController extends Controller
{
    /**
     * 연결된 소셜 계정 목록
     * GET /home/account/social
     */
    public function index(Request $request)
    {
        $socialAccounts = DB::table('social_accounts')
            ->where('user_id', Auth::id())
            ->get();
        
        // 연결 가능한 공급자
        $availableProviders = DB::table('oauth_providers')
            ->where('enabled', true)
            ->orderBy('priority')
            ->get();
        
        $connectedProviders = $socialAccounts->pluck('provider')->toArray();
        
        return view('jiny-auth::home.social.index', compact(
            'socialAccounts',
            'availableProviders',
            'connectedProviders'
        ));
    }
    
    /**
     * 소셜 계정 연결
     * POST /home/account/social/{provider}/connect
     */
    public function connect(Request $request, $provider)
    {
        // 공급자 활성화 확인
        $providerConfig = DB::table('oauth_providers')
            ->where('name', $provider)
            ->where('enabled', true)
            ->first();
        
        if (!$providerConfig) {
            return redirect()->route('home.account.social')
                ->with('error', '해당 소셜 로그인은 지원되지 않습니다.');
        }
        
        // 이미 연결된지 확인
        $existing = DB::table('social_accounts')
            ->where('user_id', Auth::id())
            ->where('provider', $provider)
            ->exists();
        
        if ($existing) {
            return redirect()->route('home.account.social')
                ->with('info', '이미 연결된 계정입니다.');
        }
        
        // 연결을 위해 OAuth 리다이렉트
        session(['social_connect' => true, 'social_provider' => $provider]);
        return Socialite::driver($provider)->redirect();
    }
    
    /**
     * 소셜 계정 연결 해제
     * DELETE /home/account/social/{provider}/disconnect
     */
    public function disconnect(Request $request, $provider)
    {
        $user = Auth::user();
        
        // 최소 1개의 로그인 방법 확인
        $socialCount = DB::table('social_accounts')
            ->where('user_id', $user->id)
            ->count();
        
        if ($socialCount <= 1 && !$user->password) {
            return response()->json([
                'success' => false,
                'message' => '최소 하나의 로그인 방법이 필요합니다.'
            ], 400);
        }
        
        $deleted = DB::table('social_accounts')
            ->where('user_id', $user->id)
            ->where('provider', $provider)
            ->delete();
        
        if (!$deleted) {
            return response()->json([
                'success' => false,
                'message' => '연결된 계정을 찾을 수 없습니다.'
            ], 404);
        }
        
        // 로그 기록
        DB::table('social_login_logs')->insert([
            'user_id' => $user->id,
            'provider' => $provider,
            'action' => 'disconnect',
            'status' => 'success',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);
        
        return response()->json([
            'success' => true,
            'message' => ucfirst($provider) . ' 계정 연결이 해제되었습니다.'
        ]);
    }
}