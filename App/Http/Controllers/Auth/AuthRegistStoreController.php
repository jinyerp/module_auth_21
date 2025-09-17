<?php

namespace Jiny\Auth\App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class AuthRegistStoreController extends Controller
{
    protected $config;

    public function __construct()
    {
        $this->config = config('jiny-auth', [
            'auth' => [
                'password' => [
                    'min_length' => 8
                ]
            ]
        ]);
    }

    /**
     * 회원가입 처리
     */
    public function store(Request $request)
    {
        // 유효성 검사
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'name.required' => '이름을 입력해주세요.',
            'email.required' => '이메일을 입력해주세요.',
            'email.email' => '올바른 이메일 형식이 아닙니다.',
            'email.unique' => '이미 등록된 이메일입니다.',
            'password.required' => '비밀번호를 입력해주세요.',
            'password.min' => '비밀번호는 최소 8자 이상이어야 합니다.',
            'password.confirmed' => '비밀번호 확인이 일치하지 않습니다.',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // 사용자 생성
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            // 로그인 처리 (선택적)
            Auth::login($user);

            // 회원가입 성공 로그
            Log::info('User registered', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip()
            ]);

            // 홈으로 리다이렉트
            return redirect('/home')->with('success', '회원가입이 완료되었습니다.');

        } catch (\Exception $e) {
            Log::error('Registration failed', [
                'error' => $e->getMessage(),
                'email' => $request->email,
                'ip' => $request->ip()
            ]);

            return back()
                ->withErrors(['error' => '회원가입 처리 중 오류가 발생했습니다.'])
                ->withInput();
        }
    }
}