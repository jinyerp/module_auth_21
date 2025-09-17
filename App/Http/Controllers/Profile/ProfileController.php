<?php
namespace Jiny\Auth\App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\View;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public $setting=[];

    public function __construct()
    {
        $this->setting = config("jiny.auth.profile");
    }


    protected function getViewFile($key)
    {
        // 1. 사용자 지정 view
        if(isset($this->setting['view'][$key])) {
            $viewfile = $this->setting['view'][$key];
            if (View::exists($viewfile)) {
                return $viewfile;
            }
        }



        // 2. www 폴더
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

                if (View::exists($viewfile)) {
                    return $viewfile;
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
                        return $viewfile;
                    }
                }
            }
        }

        // 우선순위3
        // 패키지의 리소스에서 home을 출력
        $package = "jiny-profile";
        $viewfile = $package."::".$this->viewName;
        if (View::exists($viewfile)) {
            return $viewfile;
        }

        return false;
    }

}
