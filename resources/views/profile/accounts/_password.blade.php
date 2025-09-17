<x-app>
    <x-bootstrap>

        <main class="content">

            <div class="container p-0">
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
            </div>


            <div class="container p-0">

                <div class="row">
                    <div class="col-md-4 col-xl-3">
                        @includeIf('jiny-profile::accounts.sidemenu')

                    </div>

                    <div class="col-md-8 col-xl-9">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="header-title">
                                    {{ __('패스워드 재설정') }}
                                </h4>
                                <p class="text-muted font-14">
                                    {{ __('비밀번로를 변경할 수 있습니다.') }}
                                </p>
                            </div>
                        </div>





                    </div>
                </div>

            </div>
        </main>
    </x-bootstrap>
</x-app>


