<?php

namespace Jiny\Auth\App\Http\Controllers\Admin\AuthTwoFactor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jiny\Auth\App\Models\TwoFactorAuth;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class AuthTwoFactorShow extends Controller
{
    private $jsonData;

    public function __construct()
    {
        $this->jsonData = $this->loadJsonData();
    }

    private function loadJsonData()
    {
        $jsonPath = __DIR__ . '/AuthTwoFactor.json';
        if (file_exists($jsonPath)) {
            $jsonContent = file_get_contents($jsonPath);
            return json_decode($jsonContent, true);
        }
        return [];
    }

    public function index($id)
    {
        $data = TwoFactorAuth::with('account')->findOrFail($id);
        
        // JSON 설정에 컨트롤러 클래스 추가
        $this->jsonData['controllerClass'] = self::class;
        
        // 템플릿 경로 설정
        $this->jsonData['template'] = [
            'show' => 'jiny-admin::crud.show',
            'detail' => 'jiny-auth::admin.auth_two_factor.show'
        ];
        
        // QR 코드 생성 (TOTP인 경우)
        $qrCode = null;
        if ($data->method === 'totp' && $data->secret) {
            $qrCode = $this->generateQrCode($data);
        }
        
        // 복구 코드 처리
        $recoveryCodes = [];
        if ($data->recovery_codes) {
            $codes = json_decode($data->recovery_codes, true);
            if (is_array($codes)) {
                // 보안상 일부만 표시
                $recoveryCodes = array_map(function($code) {
                    return substr($code, 0, 4) . '****-****' . substr($code, -4);
                }, array_slice($codes, 0, 3));
                $recoveryCodes[] = '... 외 ' . (count($codes) - 3) . '개';
            }
        }

        return view('jiny-admin::crud.show', [
            'jsonData' => $this->jsonData,
            'data' => $data,
            'id' => $id,
            'qrCode' => $qrCode,
            'recoveryCodes' => $recoveryCodes
        ]);
    }

    /**
     * Hook: 표시 데이터 로드 시
     */
    public function hookShowing($livewire, $id)
    {
        // 관련 데이터 미리 로드
        $livewire->with = ['account', 'account.loginHistories' => function($query) {
            $query->latest()->limit(5);
        }];
    }

    /**
     * Hook: 표시 데이터 가공
     */
    public function hookShowed($livewire, $data)
    {
        // 상태 정보 추가
        $data->status_info = [
            'enabled' => $data->enabled,
            'label' => $data->enabled ? '활성화' : '비활성화',
            'color' => $data->enabled ? 'green' : 'gray',
            'icon' => $data->enabled ? 'check-circle' : 'x-circle'
        ];
        
        // 방법별 상세 정보
        $methodDetails = [
            'totp' => [
                'label' => 'Time-based OTP (앱 인증)',
                'description' => 'Google Authenticator, Microsoft Authenticator 등의 앱을 사용',
                'icon' => 'device-mobile'
            ],
            'sms' => [
                'label' => 'SMS 인증',
                'description' => '등록된 전화번호로 인증 코드 전송',
                'icon' => 'chat-bubble-left-text'
            ],
            'email' => [
                'label' => '이메일 인증',
                'description' => '등록된 이메일로 인증 코드 전송',
                'icon' => 'envelope'
            ]
        ];
        
        $data->method_detail = $methodDetails[$data->method] ?? [
            'label' => $data->method,
            'description' => '',
            'icon' => 'question-mark-circle'
        ];
        
        // 시간 정보 포맷팅
        if ($data->enabled_at) {
            $data->enabled_at_formatted = \Carbon\Carbon::parse($data->enabled_at)->format('Y년 m월 d일 H:i');
        }
        
        if ($data->last_used_at) {
            $lastUsed = \Carbon\Carbon::parse($data->last_used_at);
            $data->last_used_at_formatted = $lastUsed->format('Y년 m월 d일 H:i');
            $data->last_used_ago = $lastUsed->diffForHumans();
        }
        
        // 보안 상태 평가
        $data->security_level = $this->evaluateSecurityLevel($data);
        
        // 최근 로그인 기록 추가
        if ($data->account && $data->account->loginHistories) {
            $data->recent_logins = $data->account->loginHistories->map(function($login) {
                return [
                    'time' => \Carbon\Carbon::parse($login->created_at)->format('m/d H:i'),
                    'ip' => $login->ip_address,
                    'device' => $login->user_agent ? substr($login->user_agent, 0, 30) . '...' : 'Unknown',
                    'two_factor_used' => $login->two_factor_verified ?? false
                ];
            });
        }
        
        return $data;
    }

    /**
     * QR 코드 생성
     */
    private function generateQrCode($twoFactor)
    {
        if (!$twoFactor->secret || !$twoFactor->account) {
            return null;
        }
        
        // TOTP URI 생성
        $issuer = config('app.name', 'Laravel');
        $account = $twoFactor->account->email;
        $secret = $twoFactor->secret;
        
        $uri = sprintf(
            'otpauth://totp/%s:%s?secret=%s&issuer=%s',
            rawurlencode($issuer),
            rawurlencode($account),
            $secret,
            rawurlencode($issuer)
        );
        
        try {
            // QR 코드를 SVG로 생성
            $svg = (new Writer(
                new ImageRenderer(
                    new RendererStyle(200),
                    new ImagickImageBackEnd()
                )
            ))->writeString($uri);
            
            // Base64 인코딩하여 직접 표시 가능하도록
            return 'data:image/svg+xml;base64,' . base64_encode($svg);
        } catch (\Exception $e) {
            // QR 코드 생성 실패 시 URI만 반환
            return $uri;
        }
    }

    /**
     * 보안 수준 평가
     */
    private function evaluateSecurityLevel($data)
    {
        $score = 0;
        $issues = [];
        
        // 활성화 여부 (40점)
        if ($data->enabled) {
            $score += 40;
        } else {
            $issues[] = '2FA가 비활성화되어 있습니다';
        }
        
        // 방법별 점수 (20점)
        if ($data->method === 'totp') {
            $score += 20; // 가장 안전
        } elseif ($data->method === 'sms') {
            $score += 15;
            $issues[] = 'SMS보다 TOTP 앱 사용을 권장합니다';
        } elseif ($data->method === 'email') {
            $score += 10;
            $issues[] = '이메일보다 TOTP 앱 사용을 권장합니다';
        }
        
        // 최근 사용 (20점)
        if ($data->last_used_at) {
            $daysSinceUsed = \Carbon\Carbon::parse($data->last_used_at)->diffInDays();
            if ($daysSinceUsed < 7) {
                $score += 20;
            } elseif ($daysSinceUsed < 30) {
                $score += 15;
            } elseif ($daysSinceUsed < 90) {
                $score += 10;
                $issues[] = '2FA를 최근에 사용하지 않았습니다';
            } else {
                $issues[] = '2FA를 오랫동안 사용하지 않았습니다';
            }
        }
        
        // 실패 시도 (20점 감점)
        if ($data->failed_attempts > 0) {
            if ($data->failed_attempts >= 5) {
                $score -= 20;
                $issues[] = '실패 시도가 많습니다 (' . $data->failed_attempts . '회)';
            } elseif ($data->failed_attempts >= 3) {
                $score -= 10;
                $issues[] = '실패 시도 주의 (' . $data->failed_attempts . '회)';
            }
        } else {
            $score += 20;
        }
        
        // 레벨 결정
        $level = 'low';
        $color = 'red';
        $label = '낮음';
        
        if ($score >= 80) {
            $level = 'high';
            $color = 'green';
            $label = '높음';
        } elseif ($score >= 60) {
            $level = 'medium';
            $color = 'yellow';
            $label = '보통';
        }
        
        return [
            'score' => $score,
            'level' => $level,
            'color' => $color,
            'label' => $label,
            'issues' => $issues
        ];
    }
}