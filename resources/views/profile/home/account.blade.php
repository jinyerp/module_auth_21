<x-www-app>
    <x-www-layout>
        <x-www-main>
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
                            <h4 class="page-title">Profile Settings</h4>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- 왼쪽 메뉴 -->
                <div class="col-md-4 col-xl-3">
                    @includeIf('jiny-profile::home.sidemenu')
                </div>

                <div class="col-md-8 col-xl-9">
                    <!-- contents -->



                    <!--  -->
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title">
                                회원 정보
                            </h4>
                            <p class="text-muted font-14">
                                개인정보와 주소를 수정하세요.
                            </p>
                            {{-- @livewire('profile-account') --}}
                        </div>
                    </div>

                    <!-- -->
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title">
                                연락처
                            </h4>
                            <p class="text-muted font-14">
                                복수의 연락처를 관리 할 수 있습니다.
                            </p>
                            @livewire('profile-phone')
                        </div>
                    </div>

                    <!-- -->
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title">
                                회원주소
                            </h4>
                            <p class="text-muted font-14">
                                복수의 주소를 등록하고 관리 할 수 있습니다. 회사 또는 집등 다양한 주소를 관리하세요.
                            </p>
                            @livewire('profile-address')
                        </div>
                    </div>






                    <!-- -->
                </div>

            </div> <!-- end of row -->


        </x-www-main>
    </x-www-layout>
</x-www-app>
