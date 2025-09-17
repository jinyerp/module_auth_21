<x-www-layout>
    <main>

        <!--Account home start-->
        <section class="py-lg-7 py-5 bg-light-subtle">
            <div class="container">
                <div class="row">
                    <!-- 왼쪽 메뉴 -->
                    <div class="col-lg-3 col-md-4">
                        @include("www::mypage.partials.info")
                        @include("www::mypage.partials.sidemenu")

                    </div>

                    <!-- 오른쪽 본문 -->
                    <div class="col-lg-9 col-md-8">

                        {{$slot}}

                    </div>
                </div>
            </div>
        </section>
        <!--Account home end-->
    </main>

    @pushOnce('scripts')
    <script src="/assets/js/vendors/sidenav.js"></script>
    @endPushOnce
</x-www-layout>
