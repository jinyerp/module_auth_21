<x-www-app>
    <x-www-layout>
        <x-www-main>

            <x-flex-between>
                <div class="page-title-box">
                    <x-flex class="align-items-center gap-2">
                        <h1 class="align-middle h3 d-inline">
                            적립금 내역
                        </h1>
                    </x-flex>
                    <p>
                        보유한 적립금을 확인합니다.
                    </p>
                </div>

                <div class="page-title-box">
                    <x-breadcrumb-item>
                        {{ $actions['route']['uri'] }}
                    </x-breadcrumb-item>

                </div>
            </x-flex-between>

            <section class="row mb-4">
                <div class="col-md-6 col-lg-5 col-xl-6">
                    <article class="card">
                        <div class="card-body">
                            <h3 class="fs-sm fw-normal mb-2">적립금</h3>
                            <div class="h5 mb-0">{{ user_balance() }}원</div>

                            <a class="position-relative d-inline-flex align-items-center fs-sm fw-medium text-success text-decoration-none"
                                href="/home/emoney/deposit">
                                <span class="hover-effect-underline stretched-link">
                                    적립금 충전하기
                                </span>
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14"
                                    fill="currentColor" class="bi bi-chevron-right" viewBox="0 0 16 16">
                                    <path fill-rule="evenodd"
                                        d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708" />
                                </svg>
                            </a>
                        </div>
                    </article>
                </div>
                <div class="col-md-6 col-lg-5 col-xl-6">
                    <div class="d-flex gap-2">
                        <a href="/home/emoney/bank"
                            class="btn btn-outline-secondary">
                            계좌관리
                        </a>

                        <a href="/home/emoney/withdraw"
                            class="btn btn-outline-secondary">
                            적립금 출금
                        </a>
                    </div>
                </div>
            </section>


            @livewire('site-myuser-emoney')

            <x-flex class="justify-content-center gap-2">
                <a class="btn btn-primary"
                    href="/home/emoney/deposit">
                    적립금 충전
                </a>

                <a class="btn btn-info"
                    href="/home/emoney/withdraw">
                    적립금 출금
                </a>
            </x-flex>

        </x-www-main>
    </x-www-layout>
</x-www-app>
