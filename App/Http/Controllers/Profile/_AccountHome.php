<?php
namespace Jiny\Auth\App\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class AccountHome extends Controller
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

        if(isset($this->view['home']) && $this->view['home']) {
            // 2. www 리소스의 mypage출력
            if(view()->exists($this->view['home'])) {
                return view($this->view['home']);
            }
        }

        // 3. 패키지 기본 mypage
        return view('jiny-profile::accounts.index',[
            'user' => Auth::user()
        ]);
    }



    public function profile()
    {
        return view('accounts::profile.index');
    }

    public function password()
    {
        return view('accounts::password.index');
    }

    public function billing()
    {
        return view('accounts::billing.index');
    }

}
