<?php
namespace Jiny\Auth\App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

use Jiny\Admin\Http\Controllers\AdminController;
class AdminOAuthProvider extends AdminController
{
    //const MENU_PATH = "menus";
    public function __construct()
    {
        parent::__construct();
        $this->setVisit($this);

        ##
        $this->actions['table']['name'] = "user_oauth_providers"; // 테이블 정보
        $this->actions['paging'] = 10; // 페이지 기본값

        $this->actions['view']['table'] = "jiny-social::admin.provider.table";

        $this->actions['view']['list'] = "jiny-social::admin.provider.list";
        $this->actions['view']['form'] = "jiny-social::admin.provider.form";

        $this->actions['title'] = "소셜로그인 공급자";
        $this->actions['subtitle'] = "소셜 로그인 공급자 관리";
    }

    public function index(Request $request)
    {
        //$this->params['total_users'] = user_count();
        return parent::index($request);
    }



}
