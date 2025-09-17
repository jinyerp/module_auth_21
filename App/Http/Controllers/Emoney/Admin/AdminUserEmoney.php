<?php
namespace Jiny\Auth\App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;


use Jiny\WireTable\Http\Controllers\WireTablePopupForms;
class AdminUserEmoney extends WireTablePopupForms
{
    public function __construct()
    {
        parent::__construct();
        $this->setVisit($this);

        ## 테이블 정보
        $this->actions['table']['name'] = "user_emoney";

        $this->actions['view']['layout'] = "jiny-users-emoney::admin.user_emoney.layout";
        $this->actions['view']['list'] = "jiny-users-emoney::admin.user_emoney.list";
        $this->actions['view']['form'] = "jiny-users-emoney::admin.user_emoney.form";

        $this->actions['view']['filter']
            = "jiny-users-emoney::admin.user_emoney.filter";

        $this->actions['title'] = "회원 적립금";
        $this->actions['subtitle'] = "판원 적립금 내역을 관리합니다.";


    }


    public function index(Request $request)
    {
        $id = $request->id;
        $this->params['id'] = $id;

        return parent::index($request);
    }




}
