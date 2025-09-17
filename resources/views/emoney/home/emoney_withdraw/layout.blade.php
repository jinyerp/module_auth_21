<x-www-app>
    <x-www-layout>
        <x-www-main>

            <x-flex-between>
                <div class="page-title-box">
                    <x-flex class="align-items-center gap-2">
                        <h1 class="align-middle h3 d-inline">
                            적립금 출금
                        </h1>
                    </x-flex>
                    <p>
                        보유한 적립금을 계좌로 출급신청 합니다.
                    </p>
                </div>

                <div class="page-title-box">
                    <x-breadcrumb-item>
                        {{$actions['route']['uri']}}
                    </x-breadcrumb-item>

                </div>
            </x-flex-between>

            @livewire('site-myuser-emoney-withdraw')

            <div class="d-flex justify-content-center gap-2 mb-4">
                <div class="alert alert-info">
                    출금요청은 매일 오전 10시에 처리됩니다.
                </div>
            </div>

            <div class="d-flex justify-content-center gap-2 mb-4">
                <a class="btn btn-info"
                    href="/home/emoney">
                    내역확인
                </a>

                <a class="btn btn-primary"
                    href="/home/emoney/deposit">
                    입금하기
                </a>
            </div>


        </x-www-main>
    </x-www-layout>
</x-www-app>
