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
class AdminAuthCurrency extends WireTablePopupForms
{
    public function __construct()
    {
        parent::__construct();
        $this->setVisit($this);

        ## 테이블 정보
        $this->actions['table']['name'] = "auth_currency";

        $this->actions['view']['layout']
            = "jiny-users-emoney::admin.auth_currency.layout";
        $this->actions['view']['list']
            = "jiny-users-emoney::admin.auth_currency.list";
        $this->actions['view']['form']
        = "jiny-users-emoney::admin.auth_currency.form";

        $this->actions['title'] = "Auth Currency";
        $this->actions['subtitle'] = "Auth Currency를 관리합니다.";


    }


    public function index(Request $request)
    {
        return parent::index($request);
    }

    /**
     * 신규 데이터 DB 삽입전에 호출됩니다.
     */
    public function hookStoring($wire,$form)
    {
        DB::table('auth_currency_log')->insert([
            'currency' => $form['currency'],
            'rate' => $form['rate'],
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return $form; // 사전 처리한 데이터를 반환합니다.
    }

    /**
     * 데이터 수정전에 호출됩니다.
     */
    public function hookUpdating($wire, $form, $old)
    {
        DB::table('auth_currency_log')->insert([
            'currency' => $form['currency'],
            'rate' => $form['rate'],
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return $form;
        return true; // 정상
    }





}
