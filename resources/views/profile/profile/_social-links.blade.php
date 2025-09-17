<x-www-layout>
    <main>
        <!--Account social start-->
        <section class="py-lg-7 py-5 bg-light-subtle">
            <div class="container">
                <div class="row">
                    <div class="col-lg-3 col-md-4">
                        @include("www::mypage.partials.info")
                        @include("www::mypage.partials.sidemenu")
                    </div>
                    <div class="col-lg-9 col-md-8">
                        <div class="mb-4">
                            <h1 class="mb-0 h3">Social Profile Links</h1>
                        </div>
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-body p-lg-5">
                                @livewire("account-social")
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!--Account social end-->
    </main>


    @include("block.partials.scripts")

    <script src="/assets/js/vendors/sidenav.js"></script>
</x-www-layout>

