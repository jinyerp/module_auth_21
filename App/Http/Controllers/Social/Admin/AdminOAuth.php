<?php
namespace Jiny\Auth\App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

use Jiny\Admin\Http\Controllers\AdminController;
class AdminOAuth extends AdminController
{
    //const MENU_PATH = "menus

    public function __construct()
    {
        parent::__construct();
        $this->setVisit($this);

        ##
        $this->actions['table']['name'] = "user_oauth"; // 테이블 정보
        $this->actions['paging'] = 10; // 페이지 기본값

        $this->actions['view']['layout'] = "jiny-social::admin.oauth.layout";
        $this->actions['view']['table'] = "jiny-social::admin.oauth.table";

        $this->actions['view']['list'] = "jiny-social::admin.oauth.list";
        $this->actions['view']['form'] = "jiny-social::admin.oauth.form";

        $this->actions['title'] = "소셜로그인 회원";
        $this->actions['subtitle'] = "소셜로그인 가입 로그를 기록합니다.";
    }

    public function index(Request $request)
    {
        $provider = $request->provider;
        if($provider) {
            $this->actions['table']['where'] = [
                "provider" => $provider
            ];
        }
        return parent::index($request);
    }



}
