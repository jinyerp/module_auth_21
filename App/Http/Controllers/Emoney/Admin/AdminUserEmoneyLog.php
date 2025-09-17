<?php
namespace Jiny\Auth\App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

/**
 * 회원 적립금 이력을 출력합니다.
 */
use Jiny\WireTable\Http\Controllers\WireTablePopupForms;
class AdminUserEmoneyLog extends WireTablePopupForms
{
    public function __construct()
    {
        parent::__construct();
        $this->setVisit($this);

        ## 테이블 정보
        $this->actions['table']['name'] = "user_emoney_log";

        $this->actions['view']['layout'] = "jiny-users-emoney::admin.user_emoney_log.layout";
        $this->actions['view']['list'] = "jiny-users-emoney::admin.user_emoney_log.list";
        $this->actions['view']['form'] = "jiny-users-emoney::admin.user_emoney_log.form";

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
