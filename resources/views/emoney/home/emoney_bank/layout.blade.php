<x-www-app>
    <x-www-layout>
        <x-www-main>
            <x-flex-between>
                <div class="page-title-box">
                    <x-flex class="align-items-center gap-2">
                        <h1 class="align-middle h3 d-inline">
                            회원 적립금 은행 목록
                        </h1>
                    </x-flex>
                    <p>
                        등록한 계좌로 적립금을 환급받습니다.
                    </p>
                </div>

                <div class="page-title-box">
                    <x-breadcrumb-item>
                        {{ $actions['route']['uri'] }}
                    </x-breadcrumb-item>

                </div>
            </x-flex-between>




            @livewire('site-table',[
                'actions' => $actions
            ])

            @livewire('site-form-popup',[
                'actions' => $actions
            ])

            <div class="d-flex justify-content-center mt-3">
                <a class="btn btn-primary" href="/home/emoney/withdraw">적립금 출금 신청</a>
            </div>

        </x-www-main>
    </x-www-layout>
</x-www-app>
