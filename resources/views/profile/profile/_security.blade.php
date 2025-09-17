<x-www-layout>
    <main>
        <!--Account security start-->
        <section class="py-lg-7 py-5 bg-light-subtle">
            <div class="container">
                <div class="row">
                    <div class="col-lg-3 col-md-4">
                        @include("www::mypage.partials.info")
                        @include("www::mypage.partials.sidemenu")
                    </div>
                    <div class="col-lg-9 col-md-8">
                        <div class="mb-4">
                            <h1 class="mb-0 h3">Security</h1>
                        </div>
                        <div class="card border-0 mb-4 shadow-sm">
                            <div class="card-body p-lg-5">
                                <div class="mb-5">
                                    <h4 class="mb-1">이메일 주소</h4>
                                    <p class="mb-0 fs-6">
                                        계정의 이메일 주소를 변경하세요. 현재 귀하의 계정은

                                        <a href="#" class="text-primary">사용자ID@example.com</a> 입니다.
                                    </p>
                                </div>
                                @livewire('account-email')
                            </div>
                        </div>
                        <div class="card border-0 mb-4 shadow-sm">
                            <div class="card-body p-lg-5">
                                <div class="mb-5">
                                    <h4 class="mb-1">비밀번호 변경</h4>
                                    <p class="mb-0 fs-6">
                                        비밀번호 변경 시 확인 메일을 보내드리니, 제출 후 해당 메일을 기다려 주시기 바랍니다.
                                    </p>
                                </div>
                                @livewire('user-password')
                            </div>
                        </div>
                        <div class="card border-danger bg-danger bg-opacity-10 mb-4 shadow-sm">
                            <div class="card-body p-lg-5">
                                <div class="mb-4">
                                    <h5 class="mb-0">Danger Zone</h5>
                                    <small>Deleting your account will</small>
                                </div>
                                <ul class="list-unstyled mb-3">
                                    <li class="mb-2">1. Permanently delete your profile, along with your authentication associations.</li>
                                    <li class="mb-2">2. Permanently delete all your content, including your articles, bookmarks, comments, upvotes, etc.</li>
                                    <li class="mb-2">3. Allow your username to become available to anyone.</li>
                                </ul>
                                <p class="mb-0">
                                    Important: deleting your account is unrecoverable and cannot be undone. Feel free to contact
                                    <a href="#" class="text-danger">support@blockthemeexample.com</a>
                                    with any questions.
                                </p>
                                <div class="mt-3">
                                    <a href="#" class="btn btn-danger">Delete Account</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!--Account security end-->
    </main>

    @include("block.partials.footer")
    @include("block.partials.scripts")

    <script src="/assets/js/vendors/sidenav.js"></script>
</x-www-layout>
