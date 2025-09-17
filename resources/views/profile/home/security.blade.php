<x-www-app>
    <x-www-layout>
        <x-www-main>
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box">
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="javascript: void(0);">Home</a></li>
                                <li class="breadcrumb-item active">Profile</li>
                            </ol>
                        </div>
                        <h4 class="page-title">Profile</h4>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 col-xl-3">
                    @includeIf('jiny-profile::home.sidemenu')
                </div>

                <div class="col-md-8 col-xl-9">

                    {{-- 브라우저 세션--}}
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title">
                                {{ __('브라우저 세션') }}
                            </h4>
                            <p class="text-muted font-14">
                                {{ __('Manage and log out your active sessions on other browsers and devices.') }}
                            </p>

                            <div class="text-muted font-14">
                                {{ __('If necessary, you may log out of all of your other browser sessions across all of your devices. Some of your recent sessions are listed below; however, this list may not be exhaustive. If you feel your account has been compromised, you should also update your password.') }}
                            </div>

                            @livewire('profile-browser-sessions')
                        </div>
                    </div>


                    {{-- 2FA 인증--}}
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title">
                                {{ __('Two Factor Authentication') }}
                            </h4>
                            <p class="text-muted font-14">
                                {{ __('Add additional security to your account using two factor authentication.') }}
                            </p>

                            @livewire('profile.two-factor-authentication-form')
                        </div>
                    </div>





                </div>
            </div>


        </x-www-main>
    </x-www-layout>
</x-www-app>
