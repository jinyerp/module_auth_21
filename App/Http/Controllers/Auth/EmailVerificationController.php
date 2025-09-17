<?php

namespace Jiny\Auth\App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use App\Models\User;

/**
 * 이메일 인증 컨트롤러
 * 이메일 확인 및 재발송 처리
 */
class EmailVerificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * 이메일 인증 안내 페이지
     * GET /email/verify
     */
    public function notice()
    {
        $user = Auth::user();
        
        if ($user->hasVerifiedEmail()) {
            return redirect()->route('home');
        }

        return view('jiny-auth::auth.verify-email');
    }

    /**
     * 이메일 인증 처리
     * GET /email/verify/{id}/{hash}
     */
    public function verify(Request $request, $id, $hash)
    {
        $user = User::findOrFail($id);

        // 해시 검증
        if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return redirect()->route('verification.notice')
                ->withErrors(['email' => '유효하지 않은 인증 링크입니다.']);
        }

        // 이미 인증된 경우
        if ($user->hasVerifiedEmail()) {
            return redirect()->route('home')
                ->with('info', '이미 이메일 인증이 완료되었습니다.');
        }

        // 이메일 인증 처리
        $user->markEmailAsVerified();

        // 활동 로그 기록
        DB::table('user_logs')->insert([
            'user_id' => $user->id,
            'action' => 'email_verified',
            'description' => '이메일 인증 완료',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now()
        ]);

        return redirect()->route('home')
            ->with('success', '이메일 인증이 완료되었습니다.');
    }

    /**
     * 이메일 인증 재발송
     * POST /email/verification-notification
     */
    public function resend(Request $request)
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('home');
        }

        $user->sendEmailVerificationNotification();

        // 활동 로그 기록
        DB::table('user_logs')->insert([
            'user_id' => $user->id,
            'action' => 'email_verification_resent',
            'description' => '이메일 인증 재발송',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now()
        ]);

        return back()->with('success', '인증 이메일을 재발송했습니다.');
    }
}