<?php

namespace Jiny\Auth\App\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class Password extends Controller
{
    public $view = [];

    public function __construct()
    {
        $views = config('accounts.views');
        $this->view = $views;
    }

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {

        if(isset($this->view['profile']) && $this->view['profile']) {
            // 2. www 리소스의 mypage출력
            if(view()->exists($this->view['profile'])) {
                return view($this->view['profile']);
            }
        }

        // 3. 패키지 기본 mypage
        return view('jiny-profile::accounts.password');
    }


}
