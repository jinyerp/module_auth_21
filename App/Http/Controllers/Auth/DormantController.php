<?php

namespace Jiny\Auth\App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Models\User;
use Carbon\Carbon;

class DormantController extends Controller
{
    /**
     * 휴면계정 안내 페이지
     * GET /login/dormant
     */
    public function index(Request $request)
    {
        // 세션에서 휴면계정 이메일 가져오기
        $email = session('dormant_email');
        
        if (!$email) {
            return redirect()->route('login')
                ->with('error', '잘못된 접근입니다.');
        }
        
        $user = User::where('email', $email)
            ->where('is_dormant', true)
            ->first();
        
        if (!$user) {
            return redirect()->route('login')
                ->with('error', '휴면계정이 아닙니다.');
        }
        
        // 휴면계정 정보
        $dormantInfo = [
            'email' => $user->email,
            'dormant_at' => Carbon::parse($user->dormant_at),
            'dormant_days' => Carbon::parse($user->dormant_at)->diffInDays(now()),
            'scheduled_delete_at' => $user->dormant_scheduled_delete_at 
                ? Carbon::parse($user->dormant_scheduled_delete_at)
                : null,
            'days_until_delete' => $user->dormant_scheduled_delete_at 
                ? now()->diffInDays(Carbon::parse($user->dormant_scheduled_delete_at))
                : null,
        ];
        
        return view('jiny-auth::auth.dormant.index', compact('dormantInfo'));
    }
    
    /**
     * 휴면계정 활성화 요청
     * POST /login/dormant/activate
     */
    public function requestActivation(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'password' => 'required'
        ]);
        
        $user = User::where('email', $request->email)->first();
        
        // 비밀번호 확인
        if (!Auth::validate(['email' => $request->email, 'password' => $request->password])) {
            return back()->withErrors(['password' => '비밀번호가 일치하지 않습니다.']);
        }
        
        // 휴면계정이 아닌 경우
        if (!$user->is_dormant) {
            return redirect()->route('login')
                ->with('info', '휴면계정이 아닙니다. 정상적으로 로그인하세요.');
        }
        
        // 활성화 토큰 생성
        $token = Str::random(64);
        $tokenRecord = DB::table('dormant_activation_tokens')->insertGetId([
            'user_id' => $user->id,
            'token' => hash('sha256', $token),
            'email' => $user->email,
            'expires_at' => now()->addHours(24),
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        // 활성화 이메일 발송
        $this->sendActivationEmail($user, $token);
        
        // 로그 기록
        $this->logActivity($user->id, 'activation_requested', '휴면계정 활성화 요청', $request);
        
        return back()->with('success', '활성화 링크가 이메일로 발송되었습니다. 24시간 이내에 이메일을 확인하세요.');
    }
    
    /**
     * 휴면계정 활성화 처리
     * GET /login/dormant/activate/{token}
     */
    public function activate(Request $request, $token)
    {
        $hashedToken = hash('sha256', $token);
        
        $tokenRecord = DB::table('dormant_activation_tokens')
            ->where('token', $hashedToken)
            ->where('expires_at', '>', now())
            ->whereNull('used_at')
            ->first();
        
        if (!$tokenRecord) {
            return redirect()->route('login')
                ->with('error', '유효하지 않거나 만료된 토큰입니다.');
        }
        
        $user = User::find($tokenRecord->user_id);
        
        if (!$user || !$user->is_dormant) {
            return redirect()->route('login')
                ->with('error', '휴면계정이 아니거나 이미 활성화되었습니다.');
        }
        
        // 휴면계정 활성화
        $user->update([
            'is_dormant' => false,
            'dormant_at' => null,
            'dormant_notified_at' => null,
            'dormant_notification_count' => 0,
            'dormant_scheduled_delete_at' => null,
            'dormant_reason' => null,
            'last_activity_at' => now()
        ]);
        
        // 토큰 사용 처리
        DB::table('dormant_activation_tokens')
            ->where('id', $tokenRecord->id)
            ->update([
                'used_at' => now(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);
        
        // 로그 기록
        $this->logActivity($user->id, 'activated', '휴면계정 활성화 완료', $request);
        
        return redirect()->route('login')
            ->with('success', '휴면계정이 활성화되었습니다. 이제 로그인할 수 있습니다.');
    }
    
    /**
     * 휴면계정 상태 확인 (인증된 사용자)
     * GET /home/account/dormant
     */
    public function status(Request $request)
    {
        $user = Auth::user();
        
        // 휴면계정 정책 정보
        $policy = [
            'inactive_days' => config('jiny-auth.dormant.inactive_days', 365),
            'notification_days' => config('jiny-auth.dormant.notification_days', 30),
            'delete_after_days' => config('jiny-auth.dormant.delete_after_days', 90),
        ];
        
        // 마지막 활동일로부터 경과 일수
        $lastActivityDays = $user->last_activity_at 
            ? Carbon::parse($user->last_activity_at)->diffInDays(now())
            : null;
        
        // 휴면 예정일 계산
        $willBeDormantAt = $user->last_activity_at
            ? Carbon::parse($user->last_activity_at)->addDays($policy['inactive_days'])
            : null;
        
        $dormantStatus = [
            'is_dormant' => $user->is_dormant,
            'last_activity_at' => $user->last_activity_at,
            'last_activity_days' => $lastActivityDays,
            'will_be_dormant_at' => $willBeDormantAt,
            'days_until_dormant' => $willBeDormantAt ? now()->diffInDays($willBeDormantAt, false) : null,
        ];
        
        return view('jiny-auth::home.dormant.status', compact('dormantStatus', 'policy'));
    }
    
    /**
     * 휴면계정 연장 요청
     * POST /home/account/dormant/extend
     */
    public function extend(Request $request)
    {
        $user = Auth::user();
        
        // 활동 시간 업데이트 (휴면 방지)
        $user->update([
            'last_activity_at' => now()
        ]);
        
        // 로그 기록
        $this->logActivity($user->id, 'extended', '휴면 방지를 위한 활동 시간 갱신', $request);
        
        return back()->with('success', '활동 시간이 갱신되었습니다. 휴면계정 전환이 연장됩니다.');
    }
    
    /**
     * 활성화 이메일 발송
     */
    private function sendActivationEmail($user, $token)
    {
        $activationUrl = route('dormant.activate', ['token' => $token]);
        
        // 실제 환경에서는 Mail facade를 사용하여 이메일 발송
        // Mail::to($user->email)->send(new DormantActivationMail($user, $activationUrl));
        
        // 개발 환경에서는 로그에 기록
        \Log::info('Dormant account activation email sent', [
            'user_id' => $user->id,
            'email' => $user->email,
            'activation_url' => $activationUrl
        ]);
    }
    
    /**
     * 활동 로그 기록
     */
    private function logActivity($userId, $action, $description, $request)
    {
        DB::table('dormant_logs')->insert([
            'user_id' => $userId,
            'action' => $action,
            'description' => $description,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now()
        ]);
    }
}