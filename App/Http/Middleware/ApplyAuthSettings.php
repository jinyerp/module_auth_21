<?php

namespace Jiny\Auth\App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Jiny\Auth\App\Services\AuthSettingsService;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Log;

class ApplyAuthSettings
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // 로그인 경로인 경우
        if ($request->routeIs('login', 'login.post')) {
            $this->applyLoginSettings($request);
        }
        
        // 회원가입 경로인 경우
        if ($request->routeIs('register', 'register.post')) {
            $this->applyRegistrationSettings($request);
        }
        
        // 보안 설정 적용
        $this->applySecuritySettings($request);
        
        return $next($request);
    }
    
    /**
     * 로그인 설정 적용
     */
    private function applyLoginSettings(Request $request)
    {
        $settings = AuthSettingsService::getLoginSettings();
        
        // 세션 수명 설정
        config(['session.lifetime' => $settings['session_lifetime']]);
        
        // 로그인 유지 기능 비활성화
        if (!$settings['enable_remember_me']) {
            $request->merge(['remember' => false]);
        }
        
        // 무차별 대입 공격 방어
        if ($settings['max_attempts'] > 0) {
            $key = 'login.' . $request->ip();
            $attempts = RateLimiter::attempts($key);
            
            if ($attempts >= $settings['max_attempts']) {
                // 계정 잠금
                RateLimiter::hit($key, $settings['lockout_duration'] * 60);
                
                Log::warning('로그인 시도 초과', [
                    'ip' => $request->ip(),
                    'email' => $request->email,
                    'attempts' => $attempts
                ]);
                
                abort(429, "너무 많은 로그인 시도입니다. {$settings['lockout_duration']}분 후에 다시 시도해주세요.");
            }
        }
    }
    
    /**
     * 회원가입 설정 적용
     */
    private function applyRegistrationSettings(Request $request)
    {
        $settings = AuthSettingsService::getRegistrationSettings();
        
        // 회원가입 비활성화
        if (!$settings['enable_registration']) {
            abort(403, '현재 회원가입이 비활성화되어 있습니다.');
        }
        
        // 이메일 도메인 확인
        if ($request->has('email')) {
            $emailDomain = substr(strrchr($request->email, "@"), 1);
            
            // 허용된 도메인 확인
            if (!empty($settings['allowed_domains']) && !in_array($emailDomain, $settings['allowed_domains'])) {
                abort(403, '허용되지 않은 이메일 도메인입니다.');
            }
            
            // 차단된 도메인 확인
            if (in_array($emailDomain, $settings['blocked_domains'])) {
                abort(403, '차단된 이메일 도메인입니다.');
            }
        }
    }
    
    /**
     * 보안 설정 적용
     */
    private function applySecuritySettings(Request $request)
    {
        $settings = AuthSettingsService::getSecuritySettings();
        
        // IP 화이트리스트 확인
        if ($settings['enable_ip_whitelist']) {
            $userIp = $request->ip();
            
            // 관리자 영역인 경우만 IP 화이트리스트 체크
            if ($request->is('admin/*')) {
                $isWhitelisted = \DB::table('auth_ip_whitelist')
                    ->where('ip_address', $userIp)
                    ->where('is_active', true)
                    ->exists();
                
                if (!$isWhitelisted) {
                    Log::warning('IP 화이트리스트 차단', [
                        'ip' => $userIp,
                        'path' => $request->path()
                    ]);
                    
                    abort(403, '접근이 거부되었습니다. IP가 화이트리스트에 없습니다.');
                }
            }
        }
        
        // 지역 차단
        if ($settings['enable_geo_blocking'] && !empty($settings['blocked_countries'])) {
            // GeoIP를 사용하여 국가 확인 (별도 패키지 필요)
            // $country = geoip($request->ip())->country;
            // if (in_array($country, $settings['blocked_countries'])) {
            //     abort(403, '귀하의 지역에서는 접근할 수 없습니다.');
            // }
        }
        
        // 의심스러운 로그인 감지
        if ($settings['enable_suspicious_login_detection'] && auth()->check()) {
            $this->detectSuspiciousLogin($request);
        }
    }
    
    /**
     * 의심스러운 로그인 감지
     */
    private function detectSuspiciousLogin(Request $request)
    {
        $user = auth()->user();
        $currentIp = $request->ip();
        $currentUserAgent = $request->userAgent();
        
        // 마지막 로그인 정보와 비교
        $lastLogin = \DB::table('auth_login_histories')
            ->where('user_id', $user->id)
            ->where('status', 'success')
            ->orderBy('created_at', 'desc')
            ->skip(1) // 현재 로그인 제외
            ->first();
        
        if ($lastLogin) {
            $suspicious = false;
            $reasons = [];
            
            // IP 변경 확인
            if ($lastLogin->ip_address !== $currentIp) {
                // IP 대역 변경 확인 (같은 /24 대역이 아닌 경우)
                $lastIpParts = explode('.', $lastLogin->ip_address);
                $currentIpParts = explode('.', $currentIp);
                
                if (count($lastIpParts) === 4 && count($currentIpParts) === 4) {
                    if ($lastIpParts[0] !== $currentIpParts[0] || 
                        $lastIpParts[1] !== $currentIpParts[1] || 
                        $lastIpParts[2] !== $currentIpParts[2]) {
                        $suspicious = true;
                        $reasons[] = 'IP 대역 변경';
                    }
                }
            }
            
            // User Agent 변경 확인
            if ($lastLogin->user_agent !== $currentUserAgent) {
                $suspicious = true;
                $reasons[] = 'User Agent 변경';
            }
            
            // 짧은 시간 내 다른 위치에서 로그인
            $timeDiff = now()->diffInMinutes($lastLogin->created_at);
            if ($timeDiff < 30 && $lastLogin->ip_address !== $currentIp) {
                $suspicious = true;
                $reasons[] = '짧은 시간 내 위치 변경';
            }
            
            if ($suspicious) {
                // 의심스러운 로그인 기록
                \DB::table('auth_login_histories')->insert([
                    'user_id' => $user->id,
                    'ip_address' => $currentIp,
                    'user_agent' => $currentUserAgent,
                    'status' => 'suspicious',
                    'failure_reason' => implode(', ', $reasons),
                    'created_at' => now()
                ]);
                
                // 사용자에게 이메일 알림
                // Mail::to($user->email)->queue(new SuspiciousLoginDetected($user, $currentIp, $reasons));
                
                Log::warning('의심스러운 로그인 감지', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'ip' => $currentIp,
                    'reasons' => $reasons
                ]);
            }
        }
    }
}