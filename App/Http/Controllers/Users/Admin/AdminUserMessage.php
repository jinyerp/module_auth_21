<?php
namespace Jiny\Auth\App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

/**
 *
 */
use Jiny\WireTable\Http\Controllers\WireTablePopupForms;
class AdminUserMessage extends WireTablePopupForms
{
    public function __construct()
    {
        parent::__construct();
        $this->setVisit($this);

        ##
        $this->actions['table']['name'] = "user_messages"; // 테이블 정보
        $this->actions['paging'] = 10; // 페이지 기본값

        $this->actions['view']['layout'] = "jiny-users::admin.message.layout";
        $this->actions['view']['table'] = "jiny-users::admin.message.table";

        $this->actions['view']['list'] = "jiny-users::admin.message.list";
        $this->actions['view']['form'] = "jiny-users::admin.message.form";

        // 커스텀 레이아웃
        $this->actions['title'] = "회원메시지";
        $this->actions['subtitle'] = "회원의 메시지 목록을 관리합니다.";
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
        if(isset($form['email']) && $form['email']) {
            $user = Auth::user();
            $form['user_id'] = $user->id;
            $form['email'] = $user->email;
            $form['name'] = $user->name;
        }


        if(isset($form['from_email']) && $form['from_email']) {
            $user = DB::table('users')
                ->where('email', $form['from_email'])
                ->first();
            $form['from_user_id'] = $user->id;
            $form['from_email'] = $user->email;
            $form['from_name'] = $user->name;
        }

        return $form; // 사전 처리한 데이터를 반환합니다.
    }

    /**
     * 데이터 수정전에 호출됩니다.
     */
    public function hookUpdating($wire, $form, $old)
    {
        if(isset($form['email']) && $form['email']) {
            $user = Auth::user();
            $form['user_id'] = $user->id;
            $form['email'] = $user->email;
            $form['name'] = $user->name;
        } else {
            $form['email'] = null;
            $form['name'] = null;
            $form['user_id'] = null;
        }


        if(isset($form['from_email']) && $form['from_email']) {
            $user = DB::table('users')
                ->where('email', $form['from_email'])
                ->first();
            $form['from_user_id'] = $user->id;
            $form['from_email'] = $user->email;
            $form['from_name'] = $user->name;
        } else {
            $form['from_email'] = null;
            $form['from_name'] = null;
            $form['from_user_id'] = null;
        }

        return $form;
        return true; // 정상
    }



}
