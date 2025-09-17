<?php
namespace Jiny\Auth\App\Http\Controllers\Home;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;


use Jiny\Site\Http\Controllers\SiteController;
class SiteUserEmoneyWithdraw extends SiteController
{
    public function __construct()
    {
        parent::__construct();
        $this->setVisit($this);

        ## 테이블 정보
        $this->actions['table']['name'] = "user_emoney_log";

        $this->actions['view']['layout']
            = inSlotView("home.emoney_withdraw",
                "jiny-users-emoney::home.emoney_withdraw.layout");
    }

    public function index(Request $request)
    {
        return parent::index($request);
    }





}
