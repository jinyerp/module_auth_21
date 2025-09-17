<?php
namespace Jiny\Auth\App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

/**
 * Auth Bank
 */
use Jiny\WireTable\Http\Controllers\WireTablePopupForms;
class AdminAuthBank extends WireTablePopupForms
{
    public function __construct()
    {
        parent::__construct();
        $this->setVisit($this);

        ## 테이블 정보
        $this->actions['table']['name'] = "auth_bank";

        $this->actions['view']['layout']
            = "jiny-users-emoney::admin.auth_bank.layout";
        $this->actions['view']['list']
            = "jiny-users-emoney::admin.auth_bank.list";
        $this->actions['view']['form']
        = "jiny-users-emoney::admin.auth_bank.form";

        $this->actions['title'] = "Auth 계좌";
        $this->actions['subtitle'] = "Auth 계좌를 관리합니다.";


    }


    public function index(Request $request)
    {
        return parent::index($request);
    }





}
