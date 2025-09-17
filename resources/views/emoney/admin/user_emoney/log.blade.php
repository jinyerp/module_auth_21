<div>

    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between">
                <div>
                    <div class="input-group">
                        <input type="text" class="form-control" wire:model="search_keyword" placeholder="검색어를 입력하세요">

                        @if($search_keyword)
                            <button class="btn btn-secondary" wire:click="searchReset">
                                삭제
                            </button>
                        @endif
                        <button class="btn btn-primary" wire:click="search">검색</button>
                    </div>

                </div>
                <div>
                    <button class="btn btn-primary" wire:click="deposit">
                        입금
                </button>
                <button class="btn btn-primary" wire:click="withdraw">
                        출금
                    </button>
                </div>

            </div>
        </div>
        <div class="card-body">
            <table class="table table-hover">
                <thead>
                    <th width='50'>Id</th>
                    <th width='200'>
                {!! xWireLink('이메일', "orderBy('email')") !!}
            </th>
            <th>설명</th>
            <th width='100'>타입</th>
            <th width='100'>입금</th>
            <th width='100'>출금</th>
            <th width='100'>잔액</th>
            <th width='100'>포인트</th>
            <th width='200'>생성일자</th>
        </thead>
        <tbody>
            @if(!empty($rows))
                @foreach ($rows as $item)
                <tr>
                    <td width='50'>{{$item->id}}</td>
                    <td width='200'>

                            {{$item->email}}
                            ({{$item->user_id}})

                    </td>
                    <td>
                        {{$item->description}}
                        @if($item->trans)
                        <div>
                            <span class="text-muted">{{$item->trans}}</span>
                            <span class="text-muted">({{$item->trans_id}})</span>
                        </div>
                        @endif
                    </td>
                    <td width='100'>
                        {{$item->type}}
                    </td>
                    <td width='100'>
                        @if($item->deposit)
                            {{$item->deposit}}
                        @endif
                    </td>
                    <td width='100'>
                        @if($item->withdraw)
                            {{$item->withdraw}}
                        @endif
                    </td>

                    <td width='100'>
                        <x-click wire:click="edit({{$item->id}})">
                            {{$item->balance}}
                        </x-click>
                    </td>

                    <td width='100'>
                        @if($item->point)
                            {{$item->point}}
                        @endif
                    </td>

                    <td width='200'>
                        <div>{{$item->created_at}}</div>
                    </td>
                </tr>
                @endforeach
            @endif
        </tbody>
            </table>
        </div>

        <div class="card-footer">
            @if($rows)
        <div class="mt-4">
            {{ $rows->links() }}
        </div>
    @endif
        </div>


    </div>

    <!-- 팝업 데이터 수정창 -->
    @if ($popupForm)
    <x-wire-dialog-modal wire:model="popupForm" :maxWidth="$popupWindowWidth">
        <x-slot name="title">
            @if($mode == 'deposit')
                이머니 입금
            @else
                이머니 출금
            @endif
        </x-slot>

        <x-slot name="content">
            @includeIf($viewForm)
        </x-slot>

        <x-slot name="footer">
            @if($message)
            <div class="alert alert-danger" role="alert">
                {{$message}}
            </div>
            @endif


            @if (isset($forms['id']))
            {{-- 수정폼--}}
            <div class="flex justify-between">
                <div> {{-- 2단계 삭제 --}}
                    @if($popupDelete)
                        <span class="text-red-600">정말로 삭제를 진행할까요?</span>
                        <button type="button" class="btn btn-danger btn-sm" wire:click="deleteConfirm">삭제</button>
                    @else
                        <button type="button" class="btn btn-danger" wire:click="delete({{$forms['id']}})">삭제</button>
                    @endif
                </div>
                <div> {{-- right --}}
                    <button type="button" class="btn btn-secondary"
                        wire:click="cancel">취소</button>
                    {{-- <button type="button" class="btn btn-info"
                        wire:click="update">수정</button> --}}
                    @if($mode == 'deposit')
                    <button type="button" class="btn btn-primary"
                        wire:click="update">입금 수정</button>
                    @else
                    <button type="button" class="btn btn-info"
                        wire:click="update">출금 수정</button>
                    @endif
                </div>
            </div>
            @else
            {{-- 생성폼 --}}
            <div class="flex justify-between">
                <div></div>
                <div class="text-right">
                    <button type="button" class="btn btn-secondary"
                        wire:click="cancel">취소</button>

                    @if($mode == 'deposit')
                    <button type="button" class="btn btn-primary"
                        wire:click="storeDeposit">입금</button>
                    @else
                    <button type="button" class="btn btn-info"
                        wire:click="storeWithdraw">출금</button>
                    @endif
                </div>
            </div>
            @endif
        </x-slot>
    </x-wire-dialog-modal>
    @endif


</div>
