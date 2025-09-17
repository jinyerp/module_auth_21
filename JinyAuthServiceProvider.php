<?php

namespace Jiny\Auth;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\Compilers\BladeCompiler;
use Livewire\Livewire;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\EloquentUserProvider;

/**
 * Jiny Auth Service Provider
 * 
 * Laravel 애플리케이션에 Jiny Auth 패키지를 등록하고 설정하는 서비스 프로바이더
 * 
 * @package Jiny\Auth
 * @author JinyPHP Team
 * @version 1.0.0
 */
class JinyAuthServiceProvider extends ServiceProvider
{
    /**
     * 패키지 식별자
     * 
     * @var string
     */
    private $package = 'jiny-auth';

    /**
     * 패키지 부팅 메서드
     * 
     * 라우트, 뷰, 마이그레이션, 명령어 등을 등록합니다.
     * 
     * @return void
     */
    public function boot()
    {
        // ========================================
        // 1. 미들웨어 등록
        // ========================================
        $this->registerMiddleware();

        // ========================================
        // 2. 라우트 파일 로드
        // ========================================
        $this->loadRoutes();

        // ========================================
        // 3. 뷰 리소스 등록
        // ========================================
        $this->loadViewsFrom(__DIR__.'/resources/views', $this->package);

        // ========================================
        // 4. 설정 파일 퍼블리싱
        // ========================================
        $this->publishConfiguration();

        // ========================================
        // 5. 데이터베이스 마이그레이션
        // ========================================
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');

        // ========================================
        // 6. Artisan 명령어 등록 (콘솔 환경에서만)
        // ========================================
        if ($this->app->runningInConsole()) {
            $this->registerCommands();
        }

        // ========================================
        // 7. 인증 게이트 정의
        // ========================================
        $this->defineGates();
    }

    /**
     * 패키지 등록 메서드
     * 
     * 설정 파일 병합 및 서비스 컨테이너 바인딩을 처리합니다.
     * 
     * @return void
     */
    public function register()
    {
        // ========================================
        // 1. 설정 파일 병합
        // ========================================
        $this->mergeConfiguration();

        // ========================================
        // 2. 서비스 컨테이너 바인딩
        // ========================================
        $this->registerServices();

        // ========================================
        // 3. Livewire 컴포넌트 등록
        // ========================================
        $this->registerLivewireComponents();

        // ========================================
        // 4. 인증 드라이버 등록
        // ========================================
        $this->registerAuthDriver();
    }

    /**
     * 미들웨어 등록
     * 
     * @return void
     */
    protected function registerMiddleware()
    {
        // 미들웨어는 나중에 필요시 구현
        // $router = $this->app->make(Router::class);
    }

    /**
     * 라우트 파일 로드
     * 
     * @return void
     */
    protected function loadRoutes()
    {
        // 웹 라우트 (일반 사용자)
        $this->loadRoutesFrom(__DIR__.'/routes/web.php');
        
        // 관리자 라우트 - @jiny/admin이 설치되어 있을 때만 로드
        if (class_exists('Jiny\Admin\JinyAdminServiceProvider')) {
            $this->loadRoutesFrom(__DIR__.'/routes/admin.php');
        }
        
        // API 라우트
        $this->loadRoutesFrom(__DIR__.'/routes/api.php');
    }

    /**
     * 설정 파일 퍼블리싱
     * 
     * php artisan vendor:publish --tag=jiny-auth-config
     * php artisan vendor:publish --tag=jiny-auth-views
     * php artisan vendor:publish --tag=jiny-auth-migrations
     * 
     * @return void
     */
    protected function publishConfiguration()
    {
        // 설정 파일 퍼블리싱
        $this->publishes([
            __DIR__.'/config/auth.php' => config_path('admin/auth.php'),
            __DIR__.'/config/oauth.php' => config_path('admin/oauth.php'),
        ], 'jiny-auth-config');

        // 뷰 파일 퍼블리싱
        $this->publishes([
            __DIR__.'/resources/views' => resource_path('views/vendor/'.$this->package),
        ], 'jiny-auth-views');

        // 마이그레이션 파일 퍼블리싱
        $this->publishes([
            __DIR__.'/database/migrations' => database_path('migrations'),
        ], 'jiny-auth-migrations');
    }

    /**
     * Artisan 명령어 등록
     * 
     * @return void
     */
    protected function registerCommands()
    {
        // 명령어는 나중에 필요시 구현
        // $this->commands([]);
    }

    /**
     * 설정 파일 병합
     * 
     * @return void
     */
    protected function mergeConfiguration()
    {
        // auth.php를 admin.auth로 병합
        $this->mergeConfigFrom(
            __DIR__.'/config/auth.php', 'admin.auth'
        );
        
        // oauth.php를 admin.oauth로 병합
        $this->mergeConfigFrom(
            __DIR__.'/config/oauth.php', 'admin.oauth'
        );
    }

    /**
     * 서비스 컨테이너 바인딩 등록
     * 
     * @return void
     */
    protected function registerServices()
    {
        // 서비스는 나중에 필요시 구현
    }

    /**
     * Livewire 컴포넌트 등록
     * 
     * @return void
     */
    protected function registerLivewireComponents()
    {
        // Profile Livewire Components
        Livewire::component('auth.profile.status', \Jiny\Auth\App\Http\Livewire\Profile\ProfileStatus::class);
        Livewire::component('auth.profile.account', \Jiny\Auth\App\Http\Livewire\Profile\ProfileAccount::class);
        
        // E-Money Livewire Components
        Livewire::component('auth.emoney.admin.user-emoney', \Jiny\Auth\App\Http\Livewire\Emoney\AdminUserEmoney::class);
        Livewire::component('auth.emoney.admin.deposit', \Jiny\Auth\App\Http\Livewire\Emoney\AdminUserEmoneyDeposit::class);
        Livewire::component('auth.emoney.admin.withdraw', \Jiny\Auth\App\Http\Livewire\Emoney\AdminUserEmoneyWithdraw::class);
        Livewire::component('auth.emoney.my.balance', \Jiny\Auth\App\Http\Livewire\Emoney\SiteMyUserEmoney::class);
        Livewire::component('auth.emoney.my.deposit', \Jiny\Auth\App\Http\Livewire\Emoney\SiteMyUserEmoneyDeposit::class);
        Livewire::component('auth.emoney.my.withdraw', \Jiny\Auth\App\Http\Livewire\Emoney\SiteMyUserEmoneyWithdraw::class);
        
        // Users Livewire Components
        Livewire::component('auth.users.message', \Jiny\Auth\App\Http\Livewire\Users\HomeUserMessage::class);
        Livewire::component('auth.users.reviews', \Jiny\Auth\App\Http\Livewire\Users\HomeUserReviews::class);
    }

    /**
     * 커스텀 인증 드라이버 등록
     * 
     * @return void
     */
    protected function registerAuthDriver()
    {
        // 필요한 경우 커스텀 인증 드라이버 등록
        Auth::provider('jiny-auth', function ($app, array $config) {
            return new EloquentUserProvider($app['hash'], $config['model']);
        });
    }

    /**
     * 인증 관련 게이트 정의
     * 
     * @return void
     */
    protected function defineGates()
    {
        Gate::define('manage-users', function ($user) {
            return $user->hasRole('admin') || $user->hasRole('super-admin');
        });

        Gate::define('view-user', function ($user, $targetUser) {
            return $user->id === $targetUser->id || $user->hasRole('admin');
        });

        Gate::define('update-user', function ($user, $targetUser) {
            return $user->id === $targetUser->id || $user->hasRole('admin');
        });

        Gate::define('delete-user', function ($user, $targetUser) {
            return $user->hasRole('super-admin') && $user->id !== $targetUser->id;
        });
    }

    /**
     * 패키지에서 제공하는 서비스 목록
     * 
     * @return array
     */
    public function provides()
    {
        return [];
    }
}