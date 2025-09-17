<?php

namespace Jiny\Auth\App\Services;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EmailService
{
    /**
     * 이메일 발송
     */
    public function send($to, $subject, $body, $name = null, $options = [])
    {
        try {
            // 이메일 데이터 준비
            $data = [
                'subject' => $subject,
                'body' => $body,
                'name' => $name,
            ];
            
            // Laravel Mail 사용
            Mail::send([], [], function ($message) use ($to, $subject, $body, $name, $options) {
                $message->to($to, $name)
                    ->subject($subject)
                    ->html($body);
                
                // CC
                if (isset($options['cc'])) {
                    $message->cc($options['cc']);
                }
                
                // BCC
                if (isset($options['bcc'])) {
                    $message->bcc($options['bcc']);
                }
                
                // Reply-To
                if (isset($options['reply_to'])) {
                    $message->replyTo($options['reply_to']);
                }
                
                // 첨부파일
                if (isset($options['attachments'])) {
                    foreach ($options['attachments'] as $attachment) {
                        $message->attach($attachment);
                    }
                }
            });
            
            return true;
        } catch (\Exception $e) {
            Log::error('Email send failed: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * 템플릿을 사용한 이메일 발송
     */
    public function sendWithTemplate($to, $templateName, $variables = [], $options = [])
    {
        // 템플릿 조회
        $template = DB::table('auth_email_templates')
            ->where('name', $templateName)
            ->where('is_active', true)
            ->first();
        
        if (!$template) {
            Log::error("Email template not found: {$templateName}");
            return false;
        }
        
        // 변수 치환
        $subject = $this->replaceVariables($template->subject, $variables);
        $body = $this->replaceVariables($template->body, $variables);
        
        // 이메일 발송
        $result = $this->send($to, $subject, $body, $variables['name'] ?? null, $options);
        
        // 템플릿 사용 횟수 증가
        if ($result) {
            DB::table('auth_email_templates')
                ->where('id', $template->id)
                ->increment('usage_count');
        }
        
        return $result;
    }
    
    /**
     * 대량 이메일 발송
     */
    public function sendBulk($recipients, $subject, $body, $options = [])
    {
        $results = [
            'sent' => 0,
            'failed' => 0,
            'errors' => []
        ];
        
        foreach ($recipients as $recipient) {
            $to = is_array($recipient) ? $recipient['email'] : $recipient;
            $name = is_array($recipient) ? ($recipient['name'] ?? null) : null;
            
            if ($this->send($to, $subject, $body, $name, $options)) {
                $results['sent']++;
            } else {
                $results['failed']++;
                $results['errors'][] = $to;
            }
        }
        
        return $results;
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
     * 이메일 유효성 검증
     */
    public function validateEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * 이메일 트래킹 픽셀 추가
     */
    public function addTrackingPixel($body, $trackingId)
    {
        $trackingUrl = route('email.track', ['id' => $trackingId]);
        $pixel = '<img src="' . $trackingUrl . '" width="1" height="1" style="display:none;" />';
        
        // </body> 태그 앞에 픽셀 추가
        if (strpos($body, '</body>') !== false) {
            $body = str_replace('</body>', $pixel . '</body>', $body);
        } else {
            $body .= $pixel;
        }
        
        return $body;
    }
    
    /**
     * 링크 트래킹 추가
     */
    public function addLinkTracking($body, $trackingId)
    {
        // 모든 링크를 트래킹 링크로 변환
        $pattern = '/<a\s+(?:[^>]*?\s+)?href="([^"]*)"([^>]*)>/i';
        $replacement = '<a href="' . route('email.click', ['id' => $trackingId, 'url' => '$1']) . '"$2>';
        
        return preg_replace($pattern, $replacement, $body);
    }
}