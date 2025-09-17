<?php
namespace Jiny\Auth\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Laravel\Socialite\Facades\Socialite;

class OAuthRedirect extends Controller
{
    public $driver = [];
    public function __construct()
    {
        $this->driver =[
            'google' => \Laravel\Socialite\Two\GoogleProvider::class,
        ];
    }

    public function redirect(Request $request)
    {
        $provider = $request->provider;
        return Socialite::driver($provider)->redirect();
    }


    /*
    public function index(Request $request)
    {
        $oauth = DB::table('user_oauth_providers')
            ->where('name', $request->provider)
            ->first();
        //dd($oauth);
        if($oauth) {

            if($oauth->callback_url) {
                $redirectUrl = $oauth->callback_url;
            } else {
                $redirectUrl = "http://localhost:8000/login/".$oauth->name."/callback";
            }
            //dd($redirectUrl);

            if(isset($this->driver[$oauth->name])) {
                $driver = $this->driver[$oauth->name];

                $oauth->client_id = "";
                $oauth->client_secret = "";


                $provider = Socialite::buildProvider(
                    \Laravel\Socialite\Two\GoogleProvider::class, [
                        'client_id' => $oauth->client_id,
                        'client_secret' => $oauth->client_secret,
                        'redirect' => $redirectUrl,
                    ]
                );

                return $provider->redirect();
            }

            // 드라이버가 존재하지 않습니다.
            session()->flash('error', $provider." 드라이버가 존재하지 않습니다..");
            return redirect()->back();

        }

        // 등록되지 않는 소셜 provider 입니다.
        session()->flash('error', "아직 미등록된 ".$provider." 로그인 서비스 입니다.");
        return redirect()->back();

    }
    */





}
