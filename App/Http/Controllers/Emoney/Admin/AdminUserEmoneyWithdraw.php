<?php
namespace Jiny\Auth\App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;


use Jiny\WireTable\Http\Controllers\WireTablePopupForms;
class AdminUserEmoneyWithdraw extends WireTablePopupForms
{
    public function __construct()
    {
        parent::__construct();
        $this->setVisit($this);

        ## 테이블 정보
        $this->actions['table']['name'] = "user_emoney_withdraw";

        $this->actions['view']['layout']
            = "jiny-users-emoney::admin.user_emoney_withdraw.layout";
        $this->actions['view']['list']
            = "jiny-users-emoney::admin.user_emoney_withdraw.list";
        $this->actions['view']['form']
            = "jiny-users-emoney::admin.user_emoney_withdraw.form";

        $this->actions['title'] = "회원 출금";
        $this->actions['subtitle'] = "판원 계좌를 관리합니다.";


    }


    public function index(Request $request)
    {
        $id = $request->id;
        if($id) {
            $this->params['id'] = $id;

            // 회원 계좌 조회 조건
            $this->actions['table']['where'] = [
                'user_id' => $id
            ];
        }

        return parent::index($request);
    }

    /**
     * 신규 데이터 DB 삽입전에 호출됩니다.
     */
    public function hookStoring($wire,$form)
    {
        if(isset($form['email'])) {
            $user = DB::table('users')
                ->where('email', $form['email'])
                ->first();
            $form['user_id'] = $user->id;
        }

        return $form; // 사전 처리한 데이터를 반환합니다.
    }

    /**
     * 데이터 수정전에 호출됩니다.
     */
    public function hookUpdating($wire, $form, $old)
    {
        if(isset($form['email'])) {
            $user = DB::table('users')
                ->where('email', $form['email'])
                ->first();
            $form['user_id'] = $user->id;
        }

        return $form;
        return true; // 정상
    }




}
