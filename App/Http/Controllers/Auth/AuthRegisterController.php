<?php

namespace Jiny\Auth\App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class AuthRegisterController extends Controller
{
    protected $config;

    public function __construct()
    {
        $this->middleware('guest');
        
        $this->config = config('jiny-auth', [
            'auth' => [
                'password' => [
                    'min_length' => 8
                ],
                'register' => [
                    'enabled' => true
                ]
            ]
        ]);
    }

    /**
     * Show registration form
     */
    public function index(Request $request)
    {
        // Check if registration is enabled
        if (!($this->config['auth']['register']['enabled'] ?? true)) {
            return view('jiny-auth::auth.regist_disabled');
        }

        // Prepare form data
        $formData = [
            'password_policy' => $this->config['auth']['password'] ?? [],
            'terms_required' => false
        ];

        return view('jiny-auth::auth.regist', $formData);
    }
}