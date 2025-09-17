<?php
namespace Jiny\Auth\App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

/**
 * Auth Currency
 */
use Jiny\WireTable\Http\Controllers\WireTablePopupForms;
class AdminAuthCurrencyLog extends WireTablePopupForms
{
    public function __construct()
    {
        parent::__construct();
        $this->setVisit($this);

        ## 테이블 정보
        $this->actions['table']['name'] = "auth_currency_log";

        $this->actions['view']['layout']
            = "jiny-users-emoney::admin.auth_currency_log.layout";
        $this->actions['view']['list']
            = "jiny-users-emoney::admin.auth_currency_log.list";
        $this->actions['view']['form']
        = "jiny-users-emoney::admin.auth_currency_log.form";

        $this->actions['title'] = "Auth Currency Log";
        $this->actions['subtitle'] = "Auth Currency Log를 관리합니다.";


    }


    public function index(Request $request)
    {
        $code = $request->code;

        if($code) {
            //$this->params['code'] = $code;
            $row = DB::table('auth_currency')
                ->whereRaw('LOWER(currency) = ?', [strtolower($code)])
                ->first();
            if($row) {
                // 회원 계좌 조회 조건
                $this->actions['table']['where'] = [
                    'currency' => $row->currency
                ];
            }
        }



        return parent::index($request);
    }




}
