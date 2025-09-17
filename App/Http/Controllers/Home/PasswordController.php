<?php

namespace Jiny\Auth\App\Http\Controllers\Home;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;

/**
 * 사용자 비밀번호 관리 컨트롤러
 * 비밀번호 변경 기능 처리
 */
class PasswordController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * 비밀번호 변경 폼 표시
     * GET /home/account/password
     */
    public function showChangeForm()
    {
        $user = Auth::user();
        
        // 비밀번호 변경 이력
        $passwordHistory = DB::table('account_logs')
            ->where('account_id', $user->id)
            ->where('action', 'password_change')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('jiny-auth::home.password-change', compact('user', 'passwordHistory'));
    }

    /**
     * 비밀번호 변경 처리
     * POST /home/account/password
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
            ],
        ], [
            'current_password.current_password' => '현재 비밀번호가 일치하지 않습니다.',
            'password.confirmed' => '새 비밀번호가 일치하지 않습니다.',
            'password.min' => '비밀번호는 최소 8자 이상이어야 합니다.',
            'password.mixed' => '비밀번호는 대소문자를 포함해야 합니다.',
            'password.numbers' => '비밀번호는 숫자를 포함해야 합니다.',
            'password.symbols' => '비밀번호는 특수문자를 포함해야 합니다.',
        ]);

        $user = Auth::user();

        // 이전 비밀번호와 동일한지 체크
        if (Hash::check($validated['password'], $user->password)) {
            return back()->withErrors([
                'password' => '새 비밀번호는 현재 비밀번호와 달라야 합니다.'
            ]);
        }

        // 최근 사용한 비밀번호 체크 (옵션)
        $recentPasswords = DB::table('password_histories')
            ->where('account_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->pluck('password');

        foreach ($recentPasswords as $oldPassword) {
            if (Hash::check($validated['password'], $oldPassword)) {
                return back()->withErrors([
                    'password' => '최근에 사용한 비밀번호는 재사용할 수 없습니다.'
                ]);
            }
        }

        // 비밀번호 변경
        $user->update([
            'password' => Hash::make($validated['password']),
            'password_changed_at' => now(),
        ]);

        // 비밀번호 이력 저장
        DB::table('password_histories')->insert([
            'account_id' => $user->id,
            'password' => $user->password,
            'created_at' => now(),
        ]);

        // 활동 로그 기록
        DB::table('account_logs')->insert([
            'account_id' => $user->id,
            'action' => 'password_change',
            'description' => '비밀번호 변경',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now()
        ]);

        // 다른 세션 종료 (선택적)
        if ($request->has('logout_other_sessions')) {
            DB::table('sessions')
                ->where('user_id', $user->id)
                ->where('id', '!=', session()->getId())
                ->delete();
        }

        return redirect()->route('home.account.password')
            ->with('success', '비밀번호가 성공적으로 변경되었습니다.');
    }

    /**
     * 비밀번호 강제 변경 (관리자 요청 또는 만료)
     * GET /home/account/password/force-change
     */
    public function forceChangeForm()
    {
        $user = Auth::user();
        
        // 비밀번호 변경이 필요한지 확인
        if (!$user->password_force_change) {
            return redirect()->route('home');
        }

        return view('jiny-auth::home.password-force-change', compact('user'));
    }

    /**
     * 비밀번호 강제 변경 처리
     * POST /home/account/password/force-change
     */
    public function forceChange(Request $request)
    {
        $validated = $request->validate([
            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
            ],
        ]);

        $user = Auth::user();

        // 비밀번호 변경
        $user->update([
            'password' => Hash::make($validated['password']),
            'password_changed_at' => now(),
            'password_force_change' => false,
        ]);

        // 비밀번호 이력 저장
        DB::table('password_histories')->insert([
            'account_id' => $user->id,
            'password' => $user->password,
            'created_at' => now(),
        ]);

        // 활동 로그 기록
        DB::table('account_logs')->insert([
            'account_id' => $user->id,
            'action' => 'password_force_change',
            'description' => '강제 비밀번호 변경',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now()
        ]);

        return redirect()->route('home')
            ->with('success', '비밀번호가 성공적으로 변경되었습니다.');
    }
}