<?php
namespace Jiny\Auth\App\Http\Controllers;

use App\Http\Controllers\Controller;

use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;
use Laravel\Socialite\Facades\Socialite;

use Jiny\Auth\User; // 로그 기록

class OAuthFacebook extends Controller
{
    private $client_id;
    private $client_secret;
    private $callback;

    public function __construct()
    {
        $row = DB::table('user_oauth_providers')
            ->where('provider', 'facebook')
            ->first();
        if($row) {
            $this->client_id = $row->client_id;
            $this->client_secret = $row->client_secret;
            $this->callback = $row->callback_url;
        }

    }

    // 페이스북 로그인 페이지로 리다이렉트
    public function redirect()
    {
        $provider = Socialite::buildProvider(
            \Laravel\Socialite\Two\FacebookProvider::class, [
                'client_id' => $this->client_id,
                'client_secret' => $this->client_secret,
                'redirect' => $this->callback
            ]
        );

        return $provider->redirect();
    }

    public function callback()
    {

        try {
            $provider = Socialite::buildProvider(
                \Laravel\Socialite\Two\FacebookProvider::class, [
                    'client_id' => $this->client_id,
                    'client_secret' => $this->client_secret,
                    'redirect' => $this->callback,
                    'guzzle' => [
                        'verify' => false // SSL 인증서 검증 비활성화
                    ]
                ]
            );

            $socailUser = $provider->stateless()->user();
            // dd($socailUser);

            $row = DB::table('users')
                ->where('email', $socailUser->email)
                ->first();

            // 로그인 인증 처리
            if($row) {
                // 로그인 세션 생성
                $id = $row->id;
                Auth::loginUsingId($id);

                // 네이버 연동 여부 확인
                $oauth = DB::table('user_oauth')
                    ->where('user_id', $id)
                    ->where('provider', 'facebook')
                    ->first();

                if(!$oauth) {
                    DB::table('user_oauth')->insert([
                        'user_id' => $id,
                        'email' => $socailUser->email,
                        'provider' => 'facebook',
                        'provider_id' => $socailUser->id,
                        'created_at' => date("Y-m-d H:i:s"),
                        'updated_at' => date("Y-m-d H:i:s"),
                    ]);

                    DB::table('user_oauth_providers')
                    ->where('provider', 'facebook')
                    ->increment('users');
                }

            } else {
                // 신규 유저 생성
                $password = Hash::make(\Illuminate\Support\Str::random(24));
                $id = DB::table('users')->insertGetId([
                    'name' => $socailUser->name,
                    'email' => $socailUser->email,
                    'password' => $password,
                    //'google_id' => $socailUser->id,

                    'created_at' => date("Y-m-d H:i:s"),
                    'updated_at' => date("Y-m-d H:i:s"),
                ]);

                DB::table('user_oauth')->insert([
                    'user_id' => $id,
                    'email' => $socailUser->email,

                    'provider' => 'facebook',
                    'provider_id' => $socailUser->id,

                    'created_at' => date("Y-m-d H:i:s"),
                    'updated_at' => date("Y-m-d H:i:s"),
                ]);

                DB::table('user_oauth_providers')
                    ->where('provider', 'facebook')
                    ->increment('users');

                // 로그인 세션 생성
                Auth::loginUsingId($id);

            }



            // 접속횟수 증가
            User::log($id, 'facebook');
            User::userLogSave($id, 'facebook'); // 로그 기록

            return redirect('/home');

        } catch (\Laravel\Socialite\Two\InvalidStateException $e) {
            //dd($e);
            return redirect('/login')
                ->withErrors(['error' => '페이스북 로그인 중 상태값 오류가 발생했습니다.']);
        }
    }

}
