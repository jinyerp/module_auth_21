<?php
namespace Jiny\Auth\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;

use Jiny\Table\Http\Controllers\DashboardController;
class Dashboard extends DashboardController
{
    use \Jiny\WireTable\Http\Trait\Permit;
    use \Jiny\Table\Http\Controllers\SetMenu;

    public function __construct()
    {
        parent::__construct();  // setting Rule 초기화
        $this->setVisit($this); // Livewire와 양방향 의존성 주입

        $this->actions['view']['main'] = "jiny-social::admin.dashboard";

        $this->actions['title'] = "소셜인증 로그인";

    }

    public function index(Request $request)
    {
        // Request값 Action 병합
        $this->checkRequestNesteds($request);
        $this->checkRequestQuery($request);

        // 메뉴 설정
        ##$this->menu_init();

        // 권한
        $this->permitCheck();
        if($this->permit['read']) {
            $view = $this->checkMainView();

            // 오늘 가입회원
            $userInfo = [];
            $userInfo['total'] = DB::table('users')->count();
            $userInfo['today'] = DB::table('users')
                ->where('created_at',">", date("Y-m-d 00:00:00"))
                ->count();

            $userAdmin = DB::table('users_admin')->count();
            $userSuper = DB::table('users_super')->count();

            $authInfo = [];
            $authInfo['total'] = DB::table('users_auth')->count();
            $authInfo['today'] = DB::table('users_auth')
                ->where('created_at',">", date("Y-m-d 00:00:00"))
                ->count();


            $counts = DB::table('users')->select(DB::raw('count(id) as total_count, count(auth) as auth_count, count(sleeper) as sleeper_count'))->get();

            return view($view,[
                'actions' => $this->actions,
                'request' => $request,

                'userInfo' => $userInfo,
                'userAdmin' => $userAdmin,
                'userSuper' => $userSuper,

                'authInfo' => $authInfo,


                "total_count" => $counts[0]->total_count,
                "auth_count" => $counts[0]->auth_count,
                "sleeper_count"=> $counts[0]->sleeper_count
            ]);
        }


        // 권한 접속 실패
        return view("jinytable::error.permit",[
            'actions' => $this->actions,
            'request' => $request
        ]);
    }

    private function checkMainView()
    {
        // 메인뷰 페이지...
        if (isset($this->actions['view']['main'])) {
            if (view()->exists($this->actions['view']['main']))
            {
                return $this->actions['view']['main'];
            }
        }

        return "jinytable::main";
    }


}
