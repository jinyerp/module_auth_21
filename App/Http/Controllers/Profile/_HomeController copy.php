<?php
namespace Jiny\Auth\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;

class HomeController extends ProfileController
{
    public $viewName = "home.index";


    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 로그인후 Home 화면입니다.
     */
    public function index() {
        $message = [];
        $user = Auth::user();

        $viewfile = $this->getViewFile('home');

        if($viewfile) {
            return view($viewfile,[
                'message' => $message,
                'user' => $user
            ]);
        }

        return "리소스를 찾을 수 없습니다.";
    }


}
