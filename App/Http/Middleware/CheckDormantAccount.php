<?php

namespace Jiny\Auth\App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\User;

class CheckDormantAccount
{
    /**
     * 휴면계정 체크 및 처리 미들웨어
     */
    public function handle(Request $request, Closure $next)
    {
        // 인증되지 않은 사용자는 통과
        if (!Auth::check()) {
            return $next($request);
        }
        
        $user = Auth::user();
        
        // 휴면계정 기능이 비활성화된 경우 통과
        if (!$this->isDormantFeatureEnabled()) {
            return $next($request);
        }
        
        // 관리자는 제외 옵션 확인
        if ($this->shouldExcludeAdmins() && $this->isAdmin($user)) {
            return $next($request);
        }
        
        // 휴면계정인 경우 처리
        if ($user->is_dormant) {
            Auth::logout();
            session()->put('dormant_email', $user->email);
            return redirect()->route('dormant.index')
                ->with('warning', '휴면계정입니다. 계정을 활성화해주세요.');
        }
        
        // 휴면계정 전환 체크
        $this->checkAndMarkDormant($user);
        
        // 활동 시간 업데이트 (휴면 방지)
        $this->updateLastActivity($user);
        
        return $next($request);
    }
    
    /**
     * 휴면계정 기능 활성화 여부 확인
     */
    private function isDormantFeatureEnabled(): bool
    {
        return Cache::remember('dormant_enabled', 3600, function () {
            return config('jiny-auth.dormant.enabled', true);
        });
    }
    
    /**
     * 관리자 제외 옵션 확인
     */
    private function shouldExcludeAdmins(): bool
    {
        return Cache::remember('dormant_exclude_admins', 3600, function () {
            return config('jiny-auth.dormant.exclude_admins', true);
        });
    }
    
    /**
     * 사용자가 관리자인지 확인
     */
    private function isAdmin($user): bool
    {
        // 관리자 확인 로직 (프로젝트에 따라 수정 필요)
        return $user->is_admin || $user->hasRole('admin');
    }
    
    /**
     * 휴면계정 전환 체크 및 처리
     */
    private function checkAndMarkDormant($user): void
    {
        // 이미 휴면계정이거나 최근 활동이 있는 경우 통과
        if ($user->is_dormant || !$user->last_activity_at) {
            return;
        }
        
        $inactiveDays = $this->getInactiveDays();
        $lastActivityDays = Carbon::parse($user->last_activity_at)->diffInDays(now());
        
        // 휴면 전환 기준 충족 시
        if ($lastActivityDays >= $inactiveDays) {
            $this->markAsDormant($user);
        }
        // 휴면 경고 알림
        elseif ($lastActivityDays >= ($inactiveDays - $this->getWarningDays())) {
            $this->sendDormantWarning($user);
        }
    }
    
    /**
     * 사용자를 휴면계정으로 전환
     */
    private function markAsDormant($user): void
    {
        $user->update([
            'is_dormant' => true,
            'dormant_at' => now(),
            'dormant_reason' => 'auto_inactive',
            'dormant_scheduled_delete_at' => $this->getScheduledDeleteDate()
        ]);
        
        // 로그 기록
        DB::table('dormant_logs')->insert([
            'user_id' => $user->id,
            'action' => 'marked_dormant',
            'description' => '장기 미접속으로 인한 자동 휴면 전환',
            'created_at' => now()
        ]);
        
        // 알림 발송 (필요 시)
        $this->sendDormantNotification($user);
    }
    
    /**
     * 휴면 경고 알림 발송
     */
    private function sendDormantWarning($user): void
    {
        // 이미 경고를 받은 경우 확인
        if ($user->dormant_notified_at && 
            Carbon::parse($user->dormant_notified_at)->isToday()) {
            return;
        }
        
        // 경고 횟수 제한 확인
        $maxNotifications = $this->getMaxNotificationCount();
        if ($user->dormant_notification_count >= $maxNotifications) {
            return;
        }
        
        // 경고 알림 발송 (이메일 등)
        // Mail::to($user->email)->send(new DormantWarningMail($user));
        
        // 알림 기록 업데이트
        $user->update([
            'dormant_notified_at' => now(),
            'dormant_notification_count' => $user->dormant_notification_count + 1
        ]);
        
        // 로그 기록
        DB::table('dormant_logs')->insert([
            'user_id' => $user->id,
            'action' => 'warning_sent',
            'description' => '휴면계정 전환 경고 알림 발송',
            'metadata' => json_encode(['count' => $user->dormant_notification_count]),
            'created_at' => now()
        ]);
    }
    
    /**
     * 휴면 전환 알림 발송
     */
    private function sendDormantNotification($user): void
    {
        // 휴면 전환 알림 이메일 발송
        // Mail::to($user->email)->send(new DormantAccountMail($user));
        
        \Log::info('User account marked as dormant', [
            'user_id' => $user->id,
            'email' => $user->email
        ]);
    }
    
    /**
     * 활동 시간 업데이트
     */
    private function updateLastActivity($user): void
    {
        // 5분마다 업데이트 (과도한 DB 업데이트 방지)
        $cacheKey = "user_activity_{$user->id}";
        if (!Cache::has($cacheKey)) {
            $user->update(['last_activity_at' => now()]);
            Cache::put($cacheKey, true, 300); // 5분간 캐시
        }
    }
    
    /**
     * 비활성 기간 (일) 가져오기
     */
    private function getInactiveDays(): int
    {
        return Cache::remember('dormant_inactive_days', 3600, function () {
            return config('jiny-auth.dormant.inactive_days', 365);
        });
    }
    
    /**
     * 경고 기간 (일) 가져오기
     */
    private function getWarningDays(): int
    {
        return Cache::remember('dormant_warning_days', 3600, function () {
            return config('jiny-auth.dormant.warning_days', 30);
        });
    }
    
    /**
     * 최대 알림 횟수 가져오기
     */
    private function getMaxNotificationCount(): int
    {
        return Cache::remember('dormant_notification_count', 3600, function () {
            return config('jiny-auth.dormant.notification_count', 3);
        });
    }
    
    /**
     * 삭제 예정일 계산
     */
    private function getScheduledDeleteDate()
    {
        if (!$this->isAutoDeleteEnabled()) {
            return null;
        }
        
        $deleteAfterDays = Cache::remember('dormant_delete_after_days', 3600, function () {
            return config('jiny-auth.dormant.delete_after_days', 90);
        });
        
        return now()->addDays($deleteAfterDays);
    }
    
    /**
     * 자동 삭제 활성화 여부
     */
    private function isAutoDeleteEnabled(): bool
    {
        return Cache::remember('dormant_auto_delete', 3600, function () {
            return config('jiny-auth.dormant.auto_delete', false);
        });
    }
}