<?php

namespace Jiny\Auth\App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class AuthRegisterHelpController extends Controller
{
    private $config;

    public function __construct()
    {
        $this->config = config('admin.auth');
    }

    public function index()
    {
        // 회원가입 도움말 기능이 비활성화된 경우
        if (!$this->config['auth']['registration']['enabled']) {
            return redirect()->route('regist')->with('error', '회원가입 기능이 비활성화되었습니다.');
        }

        return view('jiny-auth::auth.regist_help');
    }
}
