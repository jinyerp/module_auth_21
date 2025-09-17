<x-www-layout>
    <main>
        <!--Account profile Start-->
        <section class="py-lg-7 py-5 bg-light-subtle">
            <div class="container">
                <div class="row">
                    <div class="col-lg-3 col-md-4">
                        @include("www::mypage.partials.info")
                        @include("www::mypage.partials.sidemenu")
                    </div>
                    <div class="col-lg-9 col-md-8">
                        <div class="mb-4">
                            <h1 class="mb-0 h3">사용자 정보</h1>
                        </div>






                    </div>
                </div>
            </div>
        </section>
        <!--Account profile end-->
    </main>

    @include('block.partials.footer')
    @include('block.partials.scripts')

    <script src="/assets/js/vendors/sidenav.js"></script>
    <script src="/node_modules/cleave.js/dist/cleave.min.js"></script>
    <script src="/node_modules/cleave.js/dist/addons/cleave-phone.i18n.js"></script>
    <script src="/assets/js/vendors/cleave-function.js"></script>
</x-www-layout>
