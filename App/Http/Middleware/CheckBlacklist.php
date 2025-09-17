<?php

namespace Jiny\Auth\App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CheckBlacklist
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // IP 체크
        $clientIp = $request->ip();
        if ($this->isBlacklistedIp($clientIp)) {
            $this->logBlockedAccess('ip', $clientIp, $request);
            return $this->blockResponse('Your IP address has been blocked.');
        }
        
        // 인증된 사용자의 이메일 체크
        if (Auth::check()) {
            $email = Auth::user()->email;
            if ($this->isBlacklistedEmail($email)) {
                $this->logBlockedAccess('email', $email, $request);
                Auth::logout();
                return $this->blockResponse('Your account has been blocked.');
            }
        }
        
        // 로그인/회원가입 시도 시 이메일 체크
        if ($request->has('email')) {
            $email = $request->input('email');
            if ($this->isBlacklistedEmail($email)) {
                $this->logBlockedAccess('email', $email, $request);
                return $this->blockResponse('This email address is not allowed.');
            }
            
            // 이메일 도메인 체크
            $domain = substr(strrchr($email, "@"), 1);
            if ($domain && $this->isBlacklistedDomain($domain)) {
                $this->logBlockedAccess('domain', $domain, $request);
                return $this->blockResponse('Email addresses from this domain are not allowed.');
            }
        }
        
        // 전화번호 체크 (회원가입 또는 프로필 업데이트)
        if ($request->has('phone')) {
            $phone = $request->input('phone');
            if ($this->isBlacklistedPhone($phone)) {
                $this->logBlockedAccess('phone', $phone, $request);
                return $this->blockResponse('This phone number is not allowed.');
            }
        }
        
        // 키워드 체크 (사용자명, 콘텐츠 등)
        $fieldsToCheck = ['username', 'name', 'content', 'message', 'comment'];
        foreach ($fieldsToCheck as $field) {
            if ($request->has($field)) {
                $value = $request->input($field);
                if ($this->containsBlacklistedKeyword($value)) {
                    $this->logBlockedAccess('keyword', $value, $request);
                    return $this->blockResponse('Your input contains prohibited content.');
                }
            }
        }
        
        return $next($request);
    }
    
    /**
     * IP가 블랙리스트에 있는지 확인
     */
    private function isBlacklistedIp($ip)
    {
        // 먼저 화이트리스트 체크
        if ($this->isWhitelisted('ip', $ip)) {
            return false;
        }
        
        $blacklists = DB::table('blacklists')
            ->where('type', 'ip')
            ->where('is_whitelist', false)
            ->where('is_active', true)
            ->where(function($query) {
                $query->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
            })
            ->get();
        
        foreach ($blacklists as $blacklist) {
            if ($this->matchesIp($ip, $blacklist->value)) {
                // 매칭 카운트 증가
                $this->incrementMatchCount($blacklist->id);
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * 이메일이 블랙리스트에 있는지 확인
     */
    private function isBlacklistedEmail($email)
    {
        // 먼저 화이트리스트 체크
        if ($this->isWhitelisted('email', $email)) {
            return false;
        }
        
        $exists = DB::table('blacklists')
            ->where('type', 'email')
            ->where('value', $email)
            ->where('is_whitelist', false)
            ->where('is_active', true)
            ->where(function($query) {
                $query->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
            })
            ->first();
        
        if ($exists) {
            $this->incrementMatchCount($exists->id);
            return true;
        }
        
        return false;
    }
    
    /**
     * 도메인이 블랙리스트에 있는지 확인
     */
    private function isBlacklistedDomain($domain)
    {
        // 먼저 화이트리스트 체크
        if ($this->isWhitelisted('domain', $domain)) {
            return false;
        }
        
        $exists = DB::table('blacklists')
            ->where('type', 'domain')
            ->where('value', $domain)
            ->where('is_whitelist', false)
            ->where('is_active', true)
            ->where(function($query) {
                $query->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
            })
            ->first();
        
        if ($exists) {
            $this->incrementMatchCount($exists->id);
            return true;
        }
        
        return false;
    }
    
    /**
     * 전화번호가 블랙리스트에 있는지 확인
     */
    private function isBlacklistedPhone($phone)
    {
        // 전화번호 정규화 (공백, 하이픈 제거)
        $normalizedPhone = preg_replace('/[\s\-\(\)]/', '', $phone);
        
        $exists = DB::table('blacklists')
            ->where('type', 'phone')
            ->where('value', $normalizedPhone)
            ->where('is_whitelist', false)
            ->where('is_active', true)
            ->where(function($query) {
                $query->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
            })
            ->first();
        
        if ($exists) {
            $this->incrementMatchCount($exists->id);
            return true;
        }
        
        return false;
    }
    
    /**
     * 텍스트에 블랙리스트 키워드가 포함되어 있는지 확인
     */
    private function containsBlacklistedKeyword($text)
    {
        $keywords = DB::table('blacklists')
            ->where('type', 'keyword')
            ->where('is_whitelist', false)
            ->where('is_active', true)
            ->where(function($query) {
                $query->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
            })
            ->pluck('value');
        
        $lowerText = strtolower($text);
        
        foreach ($keywords as $keyword) {
            if (stripos($lowerText, $keyword) !== false) {
                $blacklist = DB::table('blacklists')
                    ->where('type', 'keyword')
                    ->where('value', $keyword)
                    ->first();
                
                if ($blacklist) {
                    $this->incrementMatchCount($blacklist->id);
                }
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * 화이트리스트 체크
     */
    private function isWhitelisted($type, $value)
    {
        if ($type === 'ip') {
            $whitelists = DB::table('blacklists')
                ->where('type', 'ip')
                ->where('is_whitelist', true)
                ->where('is_active', true)
                ->get();
            
            foreach ($whitelists as $whitelist) {
                if ($this->matchesIp($value, $whitelist->value)) {
                    return true;
                }
            }
        } else {
            return DB::table('blacklists')
                ->where('type', $type)
                ->where('value', $value)
                ->where('is_whitelist', true)
                ->where('is_active', true)
                ->exists();
        }
        
        return false;
    }
    
    /**
     * IP 매칭 확인 (단일 IP, CIDR, 범위 지원)
     */
    private function matchesIp($ip, $pattern)
    {
        // 단일 IP
        if ($ip === $pattern) {
            return true;
        }
        
        // CIDR 표기법
        if (strpos($pattern, '/') !== false) {
            list($subnet, $bits) = explode('/', $pattern);
            $ip_long = ip2long($ip);
            $subnet_long = ip2long($subnet);
            $mask = -1 << (32 - $bits);
            $subnet_long &= $mask;
            return ($ip_long & $mask) == $subnet_long;
        }
        
        // IP 범위
        if (strpos($pattern, '-') !== false) {
            list($start, $end) = explode('-', $pattern);
            $ip_long = ip2long($ip);
            $start_long = ip2long($start);
            $end_long = ip2long($end);
            return $ip_long >= $start_long && $ip_long <= $end_long;
        }
        
        return false;
    }
    
    /**
     * 매칭 카운트 증가
     */
    private function incrementMatchCount($blacklistId)
    {
        DB::table('blacklists')
            ->where('id', $blacklistId)
            ->increment('match_count');
        
        DB::table('blacklists')
            ->where('id', $blacklistId)
            ->update(['last_matched_at' => now()]);
    }
    
    /**
     * 차단된 접근 로그 기록
     */
    private function logBlockedAccess($type, $value, Request $request)
    {
        DB::table('blacklist_logs')->insert([
            'action' => 'blocked',
            'type' => $type,
            'value' => $value,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'user_id' => Auth::check() ? Auth::id() : null,
            'context' => json_encode([
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'route' => $request->route() ? $request->route()->getName() : null,
            ]),
            'created_at' => now()
        ]);
    }
    
    /**
     * 차단 응답
     */
    private function blockResponse($message)
    {
        if (request()->expectsJson()) {
            return response()->json([
                'error' => $message
            ], 403);
        }
        
        return response()->view('jiny-auth::errors.blocked', [
            'message' => $message
        ], 403);
    }
}