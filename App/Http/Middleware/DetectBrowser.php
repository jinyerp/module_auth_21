<?php

namespace Jiny\Auth\App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Jenssegers\Agent\Agent;

class DetectBrowser
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
        // 이미 감지된 세션인지 확인
        if (session()->has('browser_detected')) {
            return $next($request);
        }
        
        $agent = new Agent();
        $agent->setUserAgent($request->userAgent());
        $agent->setHttpHeaders($request->headers->all());
        
        // 브라우저 정보 수집
        $browserData = [
            'user_id' => Auth::check() ? Auth::id() : null,
            'session_id' => session()->getId(),
            'browser_name' => $agent->browser(),
            'browser_version' => $agent->version($agent->browser()),
            'browser_engine' => $this->getBrowserEngine($agent),
            'platform_name' => $agent->platform(),
            'platform_version' => $agent->version($agent->platform()),
            'device_type' => $this->getDeviceType($agent),
            'device_brand' => $agent->device(),
            'device_model' => $this->getDeviceModel($agent),
            'is_mobile' => $agent->isMobile(),
            'is_tablet' => $agent->isTablet(),
            'is_desktop' => $agent->isDesktop(),
            'is_bot' => $agent->isRobot(),
            'detected_language' => $this->detectLanguage($request),
            'accept_languages' => json_encode($this->parseAcceptLanguage($request)),
            'detected_timezone' => $this->detectTimezone($request),
            'timezone_offset' => $request->header('X-Timezone-Offset'),
            'detected_country' => $this->detectCountry($request),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'raw_data' => json_encode([
                'languages' => $agent->languages(),
                'robot' => $agent->robot(),
                'headers' => $request->headers->all(),
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ];
        
        // 데이터베이스에 저장
        DB::table('browser_detections')->insert($browserData);
        
        // 세션에 표시
        session()->put('browser_detected', true);
        session()->put('browser_info', [
            'browser' => $browserData['browser_name'],
            'platform' => $browserData['platform_name'],
            'device_type' => $browserData['device_type'],
            'language' => $browserData['detected_language'],
            'timezone' => $browserData['detected_timezone'],
            'country' => $browserData['detected_country'],
        ]);
        
        // 사용자 언어 자동 설정 (로그인한 사용자이고 설정이 없는 경우)
        if (Auth::check()) {
            $this->setUserLanguageSettings($browserData);
        }
        
        return $next($request);
    }
    
    /**
     * 브라우저 엔진 감지
     */
    private function getBrowserEngine($agent)
    {
        $userAgent = strtolower($agent->getUserAgent());
        
        if (strpos($userAgent, 'gecko') !== false) {
            return 'Gecko';
        } elseif (strpos($userAgent, 'webkit') !== false) {
            return 'WebKit';
        } elseif (strpos($userAgent, 'trident') !== false) {
            return 'Trident';
        } elseif (strpos($userAgent, 'presto') !== false) {
            return 'Presto';
        } elseif (strpos($userAgent, 'blink') !== false) {
            return 'Blink';
        }
        
        return null;
    }
    
    /**
     * 디바이스 타입 감지
     */
    private function getDeviceType($agent)
    {
        if ($agent->isTablet()) {
            return 'tablet';
        } elseif ($agent->isMobile()) {
            return 'mobile';
        } elseif ($agent->isDesktop()) {
            return 'desktop';
        } elseif ($agent->isRobot()) {
            return 'bot';
        }
        
        return 'unknown';
    }
    
    /**
     * 디바이스 모델 감지
     */
    private function getDeviceModel($agent)
    {
        $userAgent = $agent->getUserAgent();
        
        // iPhone 모델 감지
        if (preg_match('/iPhone(\d+),(\d+)/', $userAgent, $matches)) {
            return 'iPhone ' . $matches[1] . ',' . $matches[2];
        }
        
        // Android 모델 감지
        if (preg_match('/Android.*;\s*([^;]+)\s*Build/', $userAgent, $matches)) {
            return trim($matches[1]);
        }
        
        return null;
    }
    
    /**
     * 언어 감지
     */
    private function detectLanguage($request)
    {
        $acceptLanguage = $request->header('Accept-Language');
        
        if (!$acceptLanguage) {
            return null;
        }
        
        // 첫 번째 언어 코드 추출
        $languages = $this->parseAcceptLanguage($request);
        if (!empty($languages)) {
            $firstLang = array_keys($languages)[0];
            
            // 언어 코드 정규화 (en-US -> en)
            if (strpos($firstLang, '-') !== false) {
                $parts = explode('-', $firstLang);
                return $parts[0];
            }
            
            return $firstLang;
        }
        
        return null;
    }
    
    /**
     * Accept-Language 헤더 파싱
     */
    private function parseAcceptLanguage($request)
    {
        $acceptLanguage = $request->header('Accept-Language');
        
        if (!$acceptLanguage) {
            return [];
        }
        
        $languages = [];
        $parts = explode(',', $acceptLanguage);
        
        foreach ($parts as $part) {
            $part = trim($part);
            
            if (preg_match('/^([a-zA-Z\-]+)(?:;q=([0-9.]+))?$/', $part, $matches)) {
                $lang = $matches[1];
                $quality = isset($matches[2]) ? (float)$matches[2] : 1.0;
                $languages[$lang] = $quality;
            }
        }
        
        arsort($languages);
        
        return $languages;
    }
    
    /**
     * 시간대 감지
     */
    private function detectTimezone($request)
    {
        // JavaScript에서 전송한 시간대 정보
        $timezone = $request->header('X-Timezone');
        if ($timezone) {
            return $timezone;
        }
        
        // IP 기반 시간대 감지 (GeoIP 사용 시)
        // 구현 예정
        
        return null;
    }
    
    /**
     * 국가 감지
     */
    private function detectCountry($request)
    {
        // CloudFlare 헤더
        $cfCountry = $request->header('CF-IPCountry');
        if ($cfCountry) {
            return $cfCountry;
        }
        
        // Accept-Language에서 국가 코드 추출
        $acceptLanguage = $request->header('Accept-Language');
        if ($acceptLanguage && preg_match('/[a-z]{2}-([A-Z]{2})/', $acceptLanguage, $matches)) {
            return $matches[1];
        }
        
        // IP 기반 국가 감지 (GeoIP 사용 시)
        // 구현 예정
        
        return null;
    }
    
    /**
     * 사용자 언어 설정 자동 구성
     */
    private function setUserLanguageSettings($browserData)
    {
        $userId = Auth::id();
        
        // 이미 설정이 있는지 확인
        $exists = DB::table('user_language_settings')
            ->where('user_id', $userId)
            ->exists();
        
        if ($exists) {
            return;
        }
        
        // 감지된 언어로 설정
        $languageId = null;
        if ($browserData['detected_language']) {
            $language = DB::table('languages')
                ->where('code', $browserData['detected_language'])
                ->where('is_active', true)
                ->first();
            
            if ($language) {
                $languageId = $language->id;
            }
        }
        
        // 기본 언어 사용
        if (!$languageId) {
            $defaultLanguage = DB::table('languages')
                ->where('is_default', true)
                ->first();
            
            if ($defaultLanguage) {
                $languageId = $defaultLanguage->id;
            }
        }
        
        // 감지된 국가로 설정
        $countryId = null;
        if ($browserData['detected_country']) {
            $country = DB::table('countries')
                ->where('code', $browserData['detected_country'])
                ->where('is_active', true)
                ->first();
            
            if ($country) {
                $countryId = $country->id;
            }
        }
        
        // 사용자 언어 설정 저장
        if ($languageId) {
            DB::table('user_language_settings')->insert([
                'user_id' => $userId,
                'language_id' => $languageId,
                'country_id' => $countryId,
                'timezone' => $browserData['detected_timezone'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}