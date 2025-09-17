<x-www-layout>
    <main>
        <!--Avcount appearance start-->
        <section class="py-lg-7 py-5 bg-light-subtle">
            <div class="container">
                <div class="row">
                    <div class="col-lg-3 col-md-4">
                        @include("www::mypage.partials.info")
                        @include("www::mypage.partials.sidemenu")
                    </div>
                    <div class="col-lg-9 col-md-8">
                        <div class="mb-4">
                            <h1 class="mb-0 h3">Appearance</h1>
                        </div>
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-body p-lg-5">
                                <div class="mb-5">
                                    <h4 class="mb-1">Theme preferences</h4>
                                    <p class="fs-6 mb-0">Choose how block looks to you. Select a single theme, or sync with your system and automatically switch between day and night themes.</p>
                                </div>
                                <div class="mb-5">
                                    <h5 class="mb-2">Theme mode</h5>
                                    <form class="row align-items-center">
                                        <div class="col-lg-5">
                                            <label for="lightDarkTheme" class="form-label visually-hidden">System</label>
                                            <select class="form-select" id="lightDarkTheme">
                                                <option selected>Sync with System</option>
                                                <option value="Light">Light</option>
                                                <option value="Dark">Dark</option>
                                            </select>
                                        </div>
                                        <div class="col-lg-7">
                                            <p class="fs-6 mb-0 mt-2 mt-lg-0">Block theme will match your system active settings.</p>
                                        </div>
                                    </form>
                                </div>
                                <form>
                                    <div class="row">
                                        <div class="col-lg-6 mb-4 mb-lg-0">
                                            <div class="card h-100">
                                                <div class="card-header bg-white">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="flexRadioDefault" id="flexRadioLight" checked />
                                                        <label class="form-check-label" for="flexRadioLight">
                                                            <span class="fw-semibold fs-5 text-dark ms-1">Light Theme</span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="card-body">
                                                    <p class="card-text fs-6 mb-4">This theme will be active when your system is set to “light mode”</p>
                                                    <img src="/assets/images/landings/account/light.svg" alt="light" class="w-100" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-6 mb-4 mb-lg-0">
                                            <div class="card h-100">
                                                <div class="card-header bg-white">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="flexRadioDefault" id="flexRadioDark" />
                                                        <label class="form-check-label" for="flexRadioDark">
                                                            <span class="fw-semibold fs-5 text-dark ms-1">Dark Theme</span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="card-body">
                                                    <p class="card-text fs-6">This theme will be active when your system is set to “dark mode”</p>
                                                    <img src="/assets/images/landings/account/dark.svg" alt="dark" class="w-100" />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!--Avcount appearance end-->
    </main>

    @include("block.partials.footer")
    @include("block.partials.scripts")

    <script src="/assets/js/vendors/sidenav.js"></script>
</x-www-layout>
