<?php

namespace Jiny\Auth\App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client as TwilioClient;

class SmsService
{
    protected $provider;
    protected $config;
    
    public function __construct()
    {
        $this->provider = config('sms.default', 'twilio');
        $this->config = config("sms.providers.{$this->provider}");
    }
    
    /**
     * SMS 발송
     */
    public function send($to, $message, $from = null)
    {
        // 발신번호 결정
        if (!$from) {
            $from = $this->getDefaultSender();
        }
        
        // 프로바이더별 발송
        switch ($this->provider) {
            case 'twilio':
                return $this->sendViaTwilio($to, $message, $from);
                
            case 'aligo':
                return $this->sendViaAligo($to, $message, $from);
                
            case 'toast':
                return $this->sendViaToast($to, $message, $from);
                
            default:
                Log::error("Unsupported SMS provider: {$this->provider}");
                return [
                    'success' => false,
                    'error' => 'Unsupported provider'
                ];
        }
    }
    
    /**
     * 템플릿을 사용한 SMS 발송
     */
    public function sendWithTemplate($to, $templateName, $variables = [], $from = null)
    {
        // 템플릿 조회
        $template = DB::table('auth_sms_templates')
            ->where('name', $templateName)
            ->where('is_active', true)
            ->first();
        
        if (!$template) {
            Log::error("SMS template not found: {$templateName}");
            return [
                'success' => false,
                'error' => 'Template not found'
            ];
        }
        
        // 변수 치환
        $message = $this->replaceVariables($template->content, $variables);
        
        // 발신번호 (템플릿에 설정된 번호 우선)
        if (!$from && $template->sender) {
            $from = $template->sender;
        }
        
        // SMS 발송
        $result = $this->send($to, $message, $from);
        
        // 템플릿 사용 횟수 증가
        if ($result['success']) {
            DB::table('auth_sms_templates')
                ->where('id', $template->id)
                ->increment('usage_count');
        }
        
        return $result;
    }
    
    /**
     * Twilio를 통한 SMS 발송
     */
    private function sendViaTwilio($to, $message, $from)
    {
        try {
            $client = new TwilioClient(
                $this->config['sid'],
                $this->config['token']
            );
            
            $result = $client->messages->create(
                $this->formatPhoneNumber($to),
                [
                    'from' => $from,
                    'body' => $message
                ]
            );
            
            return [
                'success' => true,
                'message_id' => $result->sid,
                'cost' => $result->price,
                'response' => $result->toArray()
            ];
        } catch (\Exception $e) {
            Log::error('Twilio SMS failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * 알리고를 통한 SMS 발송
     */
    private function sendViaAligo($to, $message, $from)
    {
        try {
            $data = [
                'key' => $this->config['api_key'],
                'userid' => $this->config['user_id'],
                'sender' => $from,
                'receiver' => $to,
                'msg' => $message,
                'msg_type' => strlen($message) > 90 ? 'LMS' : 'SMS'
            ];
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://apis.aligo.in/send/');
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            
            $response = curl_exec($ch);
            curl_close($ch);
            
            $result = json_decode($response, true);
            
            if ($result['result_code'] == '1') {
                return [
                    'success' => true,
                    'message_id' => $result['msg_id'],
                    'response' => $result
                ];
            } else {
                return [
                    'success' => false,
                    'error' => $result['message']
                ];
            }
        } catch (\Exception $e) {
            Log::error('Aligo SMS failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Toast SMS를 통한 발송
     */
    private function sendViaToast($to, $message, $from)
    {
        try {
            $url = sprintf(
                'https://api-sms.cloud.toast.com/sms/v3.0/appKeys/%s/sender/sms',
                $this->config['app_key']
            );
            
            $data = [
                'body' => $message,
                'sendNo' => $from,
                'recipientList' => [
                    ['recipientNo' => $to]
                ]
            ];
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'X-Secret-Key: ' . $this->config['secret_key']
            ]);
            
            $response = curl_exec($ch);
            curl_close($ch);
            
            $result = json_decode($response, true);
            
            if ($result['header']['isSuccessful']) {
                return [
                    'success' => true,
                    'message_id' => $result['body']['data']['requestId'],
                    'response' => $result
                ];
            } else {
                return [
                    'success' => false,
                    'error' => $result['header']['resultMessage']
                ];
            }
        } catch (\Exception $e) {
            Log::error('Toast SMS failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * 기본 발신번호 조회
     */
    private function getDefaultSender()
    {
        $sender = DB::table('auth_sms_senders')
            ->where('is_default', true)
            ->where('is_active', true)
            ->first();
        
        return $sender ? $sender->number : config('sms.default_sender');
    }
    
    /**
     * 전화번호 포맷팅
     */
    private function formatPhoneNumber($phone)
    {
        // 특수문자 제거
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // 한국 번호 처리
        if (substr($phone, 0, 2) == '01') {
            $phone = '82' . substr($phone, 1);
        }
        
        // + 추가 (국제 형식)
        if (substr($phone, 0, 1) != '+') {
            $phone = '+' . $phone;
        }
        
        return $phone;
    }
    
    /**
     * 변수 치환
     */
    private function replaceVariables($text, $variables)
    {
        foreach ($variables as $key => $value) {
            $text = str_replace('{{ ' . $key . ' }}', $value, $text);
            $text = str_replace('{{' . $key . '}}', $value, $text);
        }
        
        return $text;
    }
    
    /**
     * SMS 길이 계산
     */
    public function calculateLength($message)
    {
        $length = mb_strlen($message);
        
        if ($length <= 90) {
            return ['type' => 'SMS', 'count' => 1, 'length' => $length];
        } elseif ($length <= 2000) {
            return ['type' => 'LMS', 'count' => 1, 'length' => $length];
        } else {
            return ['type' => 'MMS', 'count' => ceil($length / 2000), 'length' => $length];
        }
    }
    
    /**
     * 발송 가능 여부 확인
     */
    public function canSend($to)
    {
        // 블랙리스트 확인
        $isBlacklisted = DB::table('blacklists')
            ->where('type', 'phone')
            ->where('value', $to)
            ->where('is_active', true)
            ->exists();
        
        if ($isBlacklisted) {
            return false;
        }
        
        // 수신 거부 확인
        // TODO: 수신 거부 시스템 구현
        
        return true;
    }
}