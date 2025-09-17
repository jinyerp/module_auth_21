<x-www-layout>
    <main>
        <!--Account notification start-->
        <section class="py-lg-7 py-5 bg-light-subtle">
            <div class="container">
                <div class="row">
                    <div class="col-lg-3 col-md-4">
                        @include("www::mypage.partials.info")
                        @include("www::mypage.partials.sidemenu")
                    </div>
                    <div class="col-lg-9 col-md-8">
                        <div class="mb-4">
                            <div class="mb-4 d-flex align-items-center justify-content-between">
                                <h1 class="mb-0 h3">Notifications</h1>
                                <a href="#" class="btn btn-outline-primary">
                                    <span class="me-2">
                                        Pause
                                        <span class="d-none d-md-inline-flex">all notifications</span>
                                    </span>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-bell-fill" viewBox="0 0 16 16">
                                        <path
                                            d="M8 16a2 2 0 0 0 2-2H6a2 2 0 0 0 2 2zm.995-14.901a1 1 0 1 0-1.99 0A5.002 5.002 0 0 0 3 6c0 1.098-.5 6-2 7h14c-1.5-1-2-5.902-2-7 0-2.42-1.72-4.44-4.005-4.901z" />
                                    </svg>
                                </a>
                            </div>
                        </div>
                        <div class="card border-0 mb-4 shadow-sm">
                            <div class="card-body p-lg-5">
                                <div class="mb-4">
                                    <h4 class="mb-1">Default notifications email</h4>
                                    <p class="mb-0 fs-5">Choose where you'd like emails to be sent. You can add more email addresses.</p>
                                </div>
                                <form class="row align-items-center g-lg-3 g-2">
                                    <div class="col-lg-6">
                                        <label for="chooseEmail" class="form-label visually-hidden">Choose Email</label>
                                        <select class="form-select" id="chooseEmail">
                                            <option selected disabled value="">Choose...</option>
                                            <option selected value="blocktheme@example.com">blocktheme@example.com</option>
                                            <option value="block_theme@example.com">block_theme@example.com</option>
                                        </select>
                                    </div>
                                    <div class="col-lg-6">
                                        <a href="#" class="text-primary">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus" viewBox="0 0 16 16">
                                                <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z" />
                                            </svg>
                                            <span class="ms-2">Add New Email</span>
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="card border-0 mb-4 shadow-sm">
                            <div class="card-body p-lg-5">
                                <div class="mb-5">
                                    <h4 class="mb-1">Email notifications</h4>
                                    <p class="fs-6 mb-0">Receive notifications via email so you never miss a mention, reply, upvote or comment on block theme.</p>
                                </div>

                                <h6 class="mb-3">Send me emails for:</h6>
                                <form>
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" role="switch" id="flexSwitchNewActivity" />
                                        <label class="form-check-label ms-2" for="flexSwitchNewActivity">New activity notifications (mentions, replies, etc.)</label>
                                    </div>
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" role="switch" id="flexSwitchCheckUpdates" checked />
                                        <label class="form-check-label ms-2" for="flexSwitchCheckUpdates">News and Updatest</label>
                                    </div>
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" role="switch" id="flexSwitchCheckMarket" />
                                        <label class="form-check-label ms-2" for="flexSwitchCheckMarket">Marketing updates</label>
                                    </div>
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" role="switch" id="flexSwitchCheckEmail" />
                                        <label class="form-check-label ms-2" for="flexSwitchCheckEmail">Email me System notifications (security related, always on)</label>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="card border-0 mb-4 shadow-sm">
                            <div class="card-body p-lg-5">
                                <div class="mb-5">
                                    <h4 class="mb-1">Push notifications</h4>
                                    <p class="fs-6 mb-0">The block theme notification system notifies you of important events such as replies, mentions, updates, etc.</p>
                                </div>

                                <h6 class="mb-3">Send me emails for:</h6>
                                <form>
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" role="switch" id="flexSwitchCheckActivity" />
                                        <label class="form-check-label ms-2" for="flexSwitchCheckActivity">
                                            <span class="fs-6 fw-semibold text-dark">Comments</span>
                                            <small>The block theme notification system notifies you of important events such as replies, mentions, updates, etc.</small>
                                        </label>
                                    </div>
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" role="switch" id="flexSwitchCheckNews" checked />
                                        <label class="form-check-label ms-2" for="flexSwitchCheckNews">
                                            <span class="fs-6 fw-semibold text-dark">Reminders</span>
                                            <small>The block theme notification system notifies you of important events such as replies, mentions, updates, etc.</small>
                                        </label>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!--Account notification end-->
    </main>

    @include("block.partials.footer")
    @include("block.partials.scripts")

    <script src="/assets/js/vendors/sidenav.js"></script>
</x-www-layout>
