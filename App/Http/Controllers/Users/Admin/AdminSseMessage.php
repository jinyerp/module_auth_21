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
class AdminSseMessage extends WireTablePopupForms
{
    public function __construct()
    {
        parent::__construct();
        $this->setVisit($this);

        ##
        // $this->actions['table']['name'] = "user_messages"; // 테이블 정보
        // $this->actions['paging'] = 10; // 페이지 기본값

        $this->actions['view']['layout'] = "jiny-users::admin.sse.layout";
        // $this->actions['view']['table'] = "jiny-users::admin.message.table";

        // $this->actions['view']['list'] = "jiny-users::admin.message.list";
        // $this->actions['view']['form'] = "jiny-users::admin.message.form";

        // // 커스텀 레이아웃
        // $this->actions['title'] = "회원메시지";
        // $this->actions['subtitle'] = "회원의 메시지 목록을 관리합니다.";
    }

    public function index(Request $request)
    {
        // $id = $request->id;
        // if($id) {
        //     $this->params['id'] = $id;

        //     // 회원 계좌 조회 조건
        //     $this->actions['table']['where'] = [
        //         'user_id' => $id
        //     ];
        // }

        return parent::index($request);
    }


    public function serverEvent(Request $request)
    {
        return response()->stream(function () {
            while (true) {
                echo "data: " . json_encode(['time' => now()]) . "\n\n";
                ob_flush();
                flush();
                sleep(2); // Send data every 2 seconds
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
        ]);

        // 타임존 설정
        config(['app.timezone' => 'Asia/Seoul']);

        // SSE 헤더 설정
        return response()->stream(function() {
            // $counter = rand(1, 10);

            // while (true) {
            //     $curDate = now()->toIso8601String();

            //     // ping 이벤트 전송
            //     echo "event: ping\n";
            //     echo "data: " . json_encode(['time' => $curDate]) . "\n\n";

            //     $counter--;

            //     // 랜덤 메시지 전송
            //     if (!$counter) {
            //         echo "data: This is a message at time " . $curDate . "\n\n";
            //         $counter = rand(1, 10);
            //     }

            //     // 버퍼 비우기
            //     if (ob_get_level() > 0) {
            //         ob_end_flush();
            //     }
            //     flush();

            //     // 연결이 끊어졌는지 확인
            //     if (connection_aborted()) {
            //         break;
            //     }

            //     sleep(1);
            // }
        }, 200, [
            'Cache-Control' => 'no-cache',
            'Content-Type' => 'text/event-stream',
            'X-Accel-Buffering' => 'no'
        ]);
    }


}
