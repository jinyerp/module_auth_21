<?php
namespace Jiny\Auth\App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($request->only('email', 'password'))) {
            $user = Auth::user();
            $token = $user->createToken('auth-token')->plainTextToken;

            return response()->json([
                'status' => 'success',
                'message' => '로그인 성공',
                'token' => $token,
                'user' => $user
            ]);
        }

        throw ValidationException::withMessages([
            'email' => ['이메일 또는 비밀번호가 일치하지 않습니다.'],
        ]);
    }
}
