<?php
namespace Jiny\Auth\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;

class ProfileSecurityController extends Controller
{
    private $viewName;
    public function __construct()
    {
        $this->viewName = "home.security";
    }

    public function index() {
        $message = [];
        $viewName = "home.security";

        $user = Auth::user();

        // 우선순위1
        // resources/www 에서 먼저 화면을 찾아 출력합니다.
        if(function_exists("is_package")) {
            if(is_package("jiny/site")) {
                $prefix = "www";

                // 슬롯이 있는 경우
                if($slot = www_slot()) {
                    $viewfile = $prefix."::".$slot.".".$this->viewName;
                }
                // 슬롯이 없는 경우
                else {
                    $viewfile = $prefix."::".$this->viewName;
                }

                // 우선순위1. www 의 home.blade를 출력
                if (View::exists($viewfile)) {
                    return view($viewfile,[
                        'message' => $message,
                        'user' => $user
                    ]);
                }
            }
        }


        // 우선순위2
        // 테마의 리소스를 읽어 옵니다.
        if(function_exists("is_package")) {
            if(is_package("jiny/theme")) {

                // 테마가 지정되어 있는 경우만 처리
                if($themeName = getThemeName()) {
                    $themeName = str_replace('/','.',$themeName);
                    $viewfile = "theme::".$themeName.".".$this->viewName;
                    if (View::exists($viewfile)) {
                        return view($viewfile,[
                            'message' => $message,
                            'user' => $user
                        ]);
                    }
                }

            }
        }

        // 우선순위3
        // 패키지의 리소스에서 home을 출력
        $package = "jiny-profile";
        $viewfile = $package."::".$this->viewName;

        if($viewfile) {
            return view($viewfile,[
                'message' => $message,
                'user' => $user
            ]);
        }

        return "리소스를 찾을 수 없습니다.";
    }


}
