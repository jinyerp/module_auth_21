<?php

namespace Jiny\Auth\App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Models\User;
use Carbon\Carbon;

/**
 * 비밀번호 재설정 컨트롤러
 * 비밀번호 찾기 및 재설정 처리
 */
class PasswordResetController extends Controller
{
    /**
     * 비밀번호 찾기 폼 표시
     * GET /forgot-password
     */
    public function showForgotForm()
    {
        return view('jiny-auth::auth.password-forgot');
    }

    /**
     * 비밀번호 재설정 링크 발송
     * POST /forgot-password
     */
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ], [
            'email.exists' => '등록되지 않은 이메일 주소입니다.',
        ]);

        // 사용자 조회
        $user = User::where('email', $request->email)->first();

        // 토큰 생성
        $token = Str::random(60);

        // 기존 토큰 삭제
        DB::table('password_resets')
            ->where('email', $request->email)
            ->delete();

        // 새 토큰 저장
        DB::table('password_resets')->insert([
            'email' => $request->email,
            'token' => Hash::make($token),
            'created_at' => now(),
        ]);

        // 재설정 링크 생성
        $resetLink = url('/reset-password/' . $token . '?email=' . urlencode($request->email));

        // 이메일 발송
        Mail::send('jiny-auth::emails.password-reset', [
            'account' => $user,
            'resetLink' => $resetLink,
        ], function ($message) use ($user) {
            $message->to($user->email)
                ->subject('비밀번호 재설정 안내');
        });

        // 활동 로그 기록
        if (\Illuminate\Support\Facades\Schema::hasTable('user_logs')) {
            DB::table('user_logs')->insert([
                'user_id' => $user->id,
                'action' => 'password_reset_request',
                'description' => '비밀번호 재설정 요청',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now()
            ]);
        }

        return back()->with('success', '비밀번호 재설정 링크를 이메일로 발송했습니다.');
    }

    /**
     * 비밀번호 재설정 폼 표시
     * GET /reset-password/{token}
     */
    public function showResetForm($token)
    {
        $email = request('email');
        
        // 토큰 유효성 검증
        $passwordReset = DB::table('password_resets')
            ->where('email', $email)
            ->first();

        if (!$passwordReset) {
            return redirect()->route('password.request')
                ->withErrors(['email' => '유효하지 않은 요청입니다.']);
        }

        // 토큰 만료 체크 (1시간)
        if (Carbon::parse($passwordReset->created_at)->addHour()->isPast()) {
            DB::table('password_resets')->where('email', $email)->delete();
            return redirect()->route('password.request')
                ->withErrors(['email' => '비밀번호 재설정 링크가 만료되었습니다.']);
        }

        // 토큰 검증
        if (!Hash::check($token, $passwordReset->token)) {
            return redirect()->route('password.request')
                ->withErrors(['email' => '유효하지 않은 토큰입니다.']);
        }

        return view('jiny-auth::auth.password-reset', [
            'token' => $token,
            'email' => $email,
        ]);
    }

    /**
     * 비밀번호 재설정 처리
     * POST /reset-password
     */
    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email|exists:users,email',
            'password' => 'required|confirmed|min:8',
        ]);

        // 토큰 확인
        $passwordReset = DB::table('password_resets')
            ->where('email', $request->email)
            ->first();

        if (!$passwordReset || !Hash::check($request->token, $passwordReset->token)) {
            return back()->withErrors(['email' => '유효하지 않은 토큰입니다.']);
        }

        // 토큰 만료 체크
        if (Carbon::parse($passwordReset->created_at)->addHour()->isPast()) {
            DB::table('password_resets')->where('email', $request->email)->delete();
            return back()->withErrors(['email' => '비밀번호 재설정 링크가 만료되었습니다.']);
        }

        // 사용자 비밀번호 업데이트
        $user = User::where('email', $request->email)->first();
        $user->update([
            'password' => Hash::make($request->password),
            'password_changed_at' => now(),
            'password_force_change' => false,
        ]);

        // 비밀번호 이력 저장
        if (\Illuminate\Support\Facades\Schema::hasTable('password_histories')) {
            DB::table('password_histories')->insert([
                'user_id' => $user->id,
                'password' => $user->password,
                'created_at' => now(),
            ]);
        }

        // 토큰 삭제
        DB::table('password_resets')->where('email', $request->email)->delete();

        // 활동 로그 기록
        if (\Illuminate\Support\Facades\Schema::hasTable('user_logs')) {
            DB::table('user_logs')->insert([
                'user_id' => $user->id,
                'action' => 'password_reset',
                'description' => '비밀번호 재설정 완료',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now()
            ]);
        }

        // 모든 세션 종료
        DB::table('sessions')
            ->where('user_id', $user->id)
            ->delete();

        return redirect()->route('login')
            ->with('success', '비밀번호가 재설정되었습니다. 새 비밀번호로 로그인해주세요.');
    }
}