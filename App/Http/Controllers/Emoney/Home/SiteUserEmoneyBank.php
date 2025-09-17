<?php
namespace Jiny\Auth\App\Http\Controllers\Home;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;


use Jiny\Site\Http\Controllers\SiteController;
class SiteUserEmoneyBank extends SiteController
{
    public function __construct()
    {
        parent::__construct();
        $this->setVisit($this);

        ## 테이블 정보
        $this->actions['table']['name'] = "user_emoney_bank";

        $this->actions['view']['layout']
            = inSlotView("home.emoney_bank",
                "jiny-users-emoney::home.emoney_bank.layout");

        $this->actions['view']['table']
            = inSlotView("home.emoney_bank",
                "jiny-users-emoney::home.emoney_bank.table");

        $this->actions['view']['list']
            = inSlotView("home.emoney_bank",
                "jiny-users-emoney::home.emoney_bank.list");

        $this->actions['view']['form']
                = "jiny-users-emoney::home.emoney_bank.form";

    }

    public function index(Request $request)
    {
        $this->actions['table']['where'] = [
            'user_id' => Auth::user()->id
        ];

        return parent::index($request);
    }

    /**
     * 신규 데이터 DB 삽입전에 호출됩니다.
     */
    public function hookStoring($wire,$form)
    {
        // if(isset($form['email'])) {
        //     $user = DB::table('users')
        //         ->where('email', $form['email'])
        //         ->first();
        //     $form['user_id'] = $user->id;
        // }

        $form['user_id'] = Auth::user()->id;
        $form['email'] = Auth::user()->email;

        return $form; // 사전 처리한 데이터를 반환합니다.
    }

    /**
     * 데이터 수정전에 호출됩니다.
     */
    public function hookUpdating($wire, $form, $old)
    {
        // if(isset($form['email'])) {
        //     $user = DB::table('users')
        //         ->where('email', $form['email'])
        //         ->first();
        //     $form['user_id'] = $user->id;
        // }

        return $form;
        return true; // 정상
    }



}
