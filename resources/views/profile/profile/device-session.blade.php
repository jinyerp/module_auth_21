<x-www-layout>
    <main>
        <!--Device session start-->
        <section class="py-lg-7 py-5 bg-light-subtle">
            <div class="container">
                <div class="row">
                    <div class="col-lg-3 col-md-4">
                        @include("www::mypage.partials.info")
                        @include("www::mypage.partials.sidemenu")
                    </div>
                    <div class="col-lg-9 col-md-8">
                        <div class="mb-4">
                            <h1 class="mb-0 h3">Device session</h1>
                        </div>
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-body p-lg-5">
                                <div class="mb-5">
                                    <h4 class="mb-1">Web sessions</h4>
                                    <p class="fs-6 mb-0">This is a list of devices that have logged into your account. Revoke any sessions that you do not recognize.</p>
                                </div>

                                <div class="accordion" id="accordionExampleOne">
                                    <div class="border mb-4 rounded-3 px-4 py-3">
                                        <div class="d-flex align-items-start">
                                            <div class="me-4">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" class="bi bi-circle-fill" viewBox="0 0 16 16">
                                                    <circle cx="8" cy="8" r="8" />
                                                </svg>
                                            </div>
                                            <div class="d-lg-flex align-items-center justify-content-between w-100">
                                                <div class="d-flex">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-display text-primary" viewBox="0 0 16 16">
                                                        <path
                                                            d="M0 4s0-2 2-2h12s2 0 2 2v6s0 2-2 2h-4c0 .667.083 1.167.25 1.5H11a.5.5 0 0 1 0 1H5a.5.5 0 0 1 0-1h.75c.167-.333.25-.833.25-1.5H2s-2 0-2-2V4zm1.398-.855a.758.758 0 0 0-.254.302A1.46 1.46 0 0 0 1 4.01V10c0 .325.078.502.145.602.07.105.17.188.302.254a1.464 1.464 0 0 0 .538.143L2.01 11H14c.325 0 .502-.078.602-.145a.758.758 0 0 0 .254-.302 1.464 1.464 0 0 0 .143-.538L15 9.99V4c0-.325-.078-.502-.145-.602a.757.757 0 0 0-.302-.254A1.46 1.46 0 0 0 13.99 3H2c-.325 0-.502.078-.602.145z" />
                                                    </svg>
                                                    <div class="ms-4">
                                                        <h5 class="mb-0">Ahmedabad 102.250.233.120</h5>
                                                        <small>Your current session</small>
                                                    </div>
                                                </div>
                                                <div class="mt-4 mt-lg-0">
                                                    <a
                                                        href="#"
                                                        class="btn btn-light btn-sm"
                                                        data-bs-toggle="collapse"
                                                        data-bs-target="#collapseDeviceOne"
                                                        aria-expanded="false"
                                                        aria-controls="collapseDeviceOne">
                                                        See More
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                        <div id="collapseDeviceOne" class="accordion-collapse collapse" data-bs-parent="#accordionExampleOne">
                                            <div class="pt-4">
                                                <div class="mb-4">
                                                    <h6 class="mb-0">Device:</h6>
                                                    <small>Chrome on macOS</small>
                                                </div>
                                                <div class="mb-4">
                                                    <h6 class="mb-0">Last Location:</h6>
                                                    <small>Ahmedabad, Gujarat, India</small>
                                                </div>

                                                <h6 class="mb-1">Signed in:</h6>
                                                <small>February 28, 2022</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="border mb-4 rounded-3 px-4 py-3">
                                        <div class="d-flex align-items-start">
                                            <div class="me-4">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" class="bi bi-circle-fill" viewBox="0 0 16 16">
                                                    <circle cx="8" cy="8" r="8" />
                                                </svg>
                                            </div>
                                            <div class="d-lg-flex align-items-center justify-content-between w-100">
                                                <div class="d-flex">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-display text-primary" viewBox="0 0 16 16">
                                                        <path
                                                            d="M0 4s0-2 2-2h12s2 0 2 2v6s0 2-2 2h-4c0 .667.083 1.167.25 1.5H11a.5.5 0 0 1 0 1H5a.5.5 0 0 1 0-1h.75c.167-.333.25-.833.25-1.5H2s-2 0-2-2V4zm1.398-.855a.758.758 0 0 0-.254.302A1.46 1.46 0 0 0 1 4.01V10c0 .325.078.502.145.602.07.105.17.188.302.254a1.464 1.464 0 0 0 .538.143L2.01 11H14c.325 0 .502-.078.602-.145a.758.758 0 0 0 .254-.302 1.464 1.464 0 0 0 .143-.538L15 9.99V4c0-.325-.078-.502-.145-.602a.757.757 0 0 0-.302-.254A1.46 1.46 0 0 0 13.99 3H2c-.325 0-.502.078-.602.145z" />
                                                    </svg>
                                                    <div class="ms-4">
                                                        <h5 class="mb-0">Ahmedabad 102.250.233.120</h5>
                                                        <small>Your current session</small>
                                                    </div>
                                                </div>
                                                <div class="mt-4 mt-lg-0">
                                                    <a
                                                        href="#"
                                                        class="btn btn-light btn-sm"
                                                        data-bs-toggle="collapse"
                                                        data-bs-target="#collapseDeviceTwo"
                                                        aria-expanded="false"
                                                        aria-controls="collapseDeviceTwo">
                                                        See More
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                        <div id="collapseDeviceTwo" class="accordion-collapse collapse show" data-bs-parent="#accordionExampleOne">
                                            <div class="pt-4">
                                                <div class="mb-4">
                                                    <h6 class="mb-0">Device:</h6>
                                                    <small>Chrome on macOS</small>
                                                </div>
                                                <div class="mb-4">
                                                    <h6 class="mb-0">Last Location:</h6>
                                                    <small>Ahmedabad, Gujarat, India</small>
                                                </div>

                                                <h6 class="mb-1">Signed in:</h6>
                                                <small>February 28, 2022</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-body p-lg-5">
                                <div class="mb-5">
                                    <h4 class="mb-1">Mobile sessions</h4>
                                    <p class="fs-6 mb-0">Mobile can be used to verify your identity when signing in from a new device and as a two-factor authentication method.</p>
                                </div>

                                <div class="accordion" id="accordionExampleTwo">
                                    <div class="border mb-4 rounded-3 px-4 py-3">
                                        <div class="d-flex align-items-start">
                                            <div class="me-4">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" class="bi bi-circle-fill" viewBox="0 0 16 16">
                                                    <circle cx="8" cy="8" r="8" />
                                                </svg>
                                            </div>
                                            <div class="d-lg-flex align-items-center justify-content-between w-100">
                                                <div class="d-flex">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-display text-primary" viewBox="0 0 16 16">
                                                        <path
                                                            d="M0 4s0-2 2-2h12s2 0 2 2v6s0 2-2 2h-4c0 .667.083 1.167.25 1.5H11a.5.5 0 0 1 0 1H5a.5.5 0 0 1 0-1h.75c.167-.333.25-.833.25-1.5H2s-2 0-2-2V4zm1.398-.855a.758.758 0 0 0-.254.302A1.46 1.46 0 0 0 1 4.01V10c0 .325.078.502.145.602.07.105.17.188.302.254a1.464 1.464 0 0 0 .538.143L2.01 11H14c.325 0 .502-.078.602-.145a.758.758 0 0 0 .254-.302 1.464 1.464 0 0 0 .143-.538L15 9.99V4c0-.325-.078-.502-.145-.602a.757.757 0 0 0-.302-.254A1.46 1.46 0 0 0 13.99 3H2c-.325 0-.502.078-.602.145z" />
                                                    </svg>
                                                    <div class="ms-4">
                                                        <h5 class="mb-0">Ahmedabad 102.250.233.120</h5>
                                                        <small>Your current session</small>
                                                    </div>
                                                </div>
                                                <div class="mt-4 mt-lg-0">
                                                    <a
                                                        href="#"
                                                        class="btn btn-light btn-sm"
                                                        data-bs-toggle="collapse"
                                                        data-bs-target="#collapseMobileOne"
                                                        aria-expanded="false"
                                                        aria-controls="collapseMobileOne">
                                                        See More
                                                    </a>
                                                </div>
                                            </div>
                                        </div>

                                        <div id="collapseMobileOne" class="accordion-collapse collapse" data-bs-parent="#accordionExampleTwo">
                                            <div class="pt-4">
                                                <div class="mb-4">
                                                    <h6 class="mb-0">Device:</h6>
                                                    <small>Chrome on macOS</small>
                                                </div>
                                                <div class="mb-4">
                                                    <h6 class="mb-0">Last Location:</h6>
                                                    <small>Ahmedabad, Gujarat, India</small>
                                                </div>

                                                <h6 class="mb-1">Signed in:</h6>
                                                <small>February 28, 2022</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!--Device session end-->
    </main>

    @include("block.partials.footer")
    @include("block.partials.scripts")
    <script src="/assets/js/vendors/sidenav.js"></script>
</x-www-layout>

