<x-admin>
    <x-flex-between>
        <div class="page-title-box">
            <x-flex class="align-items-center gap-2">
                <h1 class="align-middle h3 d-inline">
                    @if (isset($actions['title']))
                    {{$actions['title']}}
                    @endif
                </h1>

            </x-flex>
            <p>
                @if (isset($actions['subtitle']))
                    {{$actions['subtitle']}}
                @endif
            </p>
        </div>

        <div class="page-title-box">
            <x-breadcrumb-item>
                {{$actions['route']['uri']}}
            </x-breadcrumb-item>

            <div class="mt-2 d-flex justify-content-end gap-2">
                <button class="btn btn-sm btn-danger">Video</button>
                <button class="btn btn-sm btn-secondary">Manual</button>
            </div>
        </div>
    </x-flex-between>




    <div class="row">
        <div class="col-xl-6 col-xxl-5 d-flex">
            <div class="w-100">
                <div class="row">
                    <div class="col-sm-6">
                        <div class="card flex-fill">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col mt-0">
                                        <a href="/admin/auth/emoney/user" class="text-decoration-none">
                                        <h5 class="card-title">
                                            전체 적립금
                                        </h5>
                                        </a>
                                    </div>

                                    <div class="col-auto">
                                        <div class="stat stat-sm" style="">
                                            <i class="fa-solid fa-dollar-sign align-middle"></i>
                                        </div>
                                    </div>
                                </div>
                                <h4 class="mt-0 mb-1">
                                    {{DB::table('user_emoney')->sum('balance')}}
                                </h4>

                                <div class="mb-0">

                                    <span class="badge badge-subtle-success">+6.15%</span>
                                    <span class="text-muted">Since last week</span>

                                </div>

                            </div>
                        </div>

                        <div class="card flex-fill">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col mt-0">
                                        <a href="/admin/auth/emoney/bank" class="text-decoration-none">
                                        <h5 class="card-title">사용자 계좌</h5>
                                        </a>
                                    </div>

                                    <div class="col-auto">
                                        <div class="stat stat-sm" style="background: #F7931A; color: white;">
                                            <i class="fa-solid fa-bitcoin-sign align-middle"></i>
                                        </div>
                                    </div>
                                </div>
                                <h4 class="mt-0 mb-1">
                                    {{DB::table('user_emoney_bank')->count()}} Banks
                                </h4>

                                <div class="mb-0">
                                    <span class="text-muted">Volume: 132,691 BTC</span>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="col-sm-6">
                        <div class="card flex-fill">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col mt-0">
                                        <h5 class="card-title">환율(KRW)</h5>
                                    </div>

                                    <div class="col-auto">
                                        <div class="stat stat-sm" style="background: #345D9D; color: white;">
                                            <i class="fa-solid fa-litecoin-sign align-middle"></i>
                                        </div>
                                    </div>
                                </div>
                                <h4 class="mt-0 mb-1">
                                    {{DB::table('auth_currency_log')
                                        ->where('currency','KRW')
                                        ->orderBy('id','desc')
                                        ->first()->rate}} 원
                                </h4>

                                <div class="mb-0">
                                    <span class="text-muted">Volume: 31,268 BTC</span>
                                </div>
                            </div>
                        </div>

                        <div class="card flex-fill">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col mt-0">
                                        <a href="/admin/auth/bank" class="text-decoration-none">
                                        <h5 class="card-title">입금계좌</h5>
                                        </a>
                                    </div>

                                    <div class="col-auto">
                                        <div class="stat stat-sm" style="background: #627EEA; color: white;">
                                            <i class="fa-brands fa-ethereum align-middle"></i>
                                        </div>
                                    </div>
                                </div>
                                <h4 class="mt-0 mb-1">
                                    {{DB::table('auth_bank')->count()}} Banks
                                </h4>

                                <div class="mb-0">
                                    <span class="text-muted">Volume: 32,982 BTC</span>
                                </div>
                            </div>
                        </div>



                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-6 col-xxl-7">
            <div class="card flex-fill w-100">
                <div class="card-header">
                    <a href="/admin/auth/currency" class="text-decoration-none">
                        <h5 class="card-title">통화</h5>
                    </a>
                    <h6 class="card-subtitle text-muted">
                        적립금에 대한 통화목록 입니다.
                    </h6>
                </div>
                <div class="card-body">

                </div>
            </div>
        </div>
    </div>





    <div class="row">
        <div class="col-12 col-lg-6 col-xxl d-flex">
            <div class="card flex-fill">
                <div class="card-header">
                    <div class="card-actions float-end">
                        <a class="btn btn-sm btn-light"
                        href="/admin/auth/emoney/deposit">전체보기</a>
                    </div>
                    <h5 class="card-title mb-0">입금확인</h5>
                </div>
                <table class="table table-sm table-striped my-0">
                    <thead>
                        <tr>
                            <th>요청일자</th>
                            <th>이메일</th>
                            <th width="200">금액</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach (DB::table('user_emoney_withdraw')->limit(10)->get() as $withdraw)
                        <tr>
                            <td>{{$withdraw->created_at}}</td>
                            <td>{{$withdraw->email}}</td>
                            <td width="200">{{$withdraw->amount}}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="col-12 col-lg-6 col-xxl d-flex">
            <div class="card flex-fill">
                <div class="card-header">
                    <div class="card-actions float-end">
                        <a class="btn btn-sm btn-light"
                        href="/admin/auth/emoney/withdraw">전체보기</a>
                    </div>
                    <h5 class="card-title mb-0">출금확인</h5>
                </div>
                <table class="table table-sm table-striped my-0">
                    <thead>
                        <tr>
                            <th>요청일자</th>
                            <th>이메일</th>
                            <th width="200">금액</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach (DB::table('user_emoney_deposit')->limit(10)->get() as $withdraw)
                        <tr>
                            <td>{{$withdraw->created_at}}</td>
                            <td>{{$withdraw->email}}</td>
                            <td width="200">{{$withdraw->amount}}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>



</x-admin>

