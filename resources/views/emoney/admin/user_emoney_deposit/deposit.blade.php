<div>

    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between">
                <div>
                    <div class="input-group">
                        <input type="text" class="form-control" wire:model="search_keyword" placeholder="검색어를 입력하세요">

                        @if ($search_keyword)
                            <button class="btn btn-secondary" wire:click="searchReset">
                                삭제
                            </button>
                        @endif
                        <button class="btn btn-primary" wire:click="search">검색</button>
                    </div>

                </div>
                <div>
                    {{-- <button class="btn btn-primary" wire:click="deposit">
                        입금
                    </button>
                    <button class="btn btn-primary" wire:click="withdraw">
                        출금
                    </button> --}}
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
                    <th width='200'>요청일자</th>
                    <th>은행/계좌/예금주</th>
                    <th width='200'>입금액</th>
                    <th width='200'>입금상태</th>
                    <th width='150'>처리</th>
                </thead>
                <tbody>
                    @if (!empty($rows))
                        @foreach ($rows as $item)
                            <tr>
                                <td width='50'>{{ $item->id }}</td>
                                <td width='200'>

                                    {{ $item->email }}
                                    ({{ $item->user_id }})
                                </td>
                                <td width='200'>
                                    <div>{{ $item->created_at }}</div>
                                </td>
                                <td>
                                    {{ $item->bank }} / {{ $item->account }} / {{ $item->owner }}
                                </td>

                                <td width='200'>
                                    <x-click wire:click="edit({{ $item->id }})">
                                        {{ $item->amount }}
                                    </x-click>
                                </td>

                                <td width='200'>
                                    <div>{{ $item->status }}</div>
                                    <div class="text-muted small">{{ $item->checked_at }}</div>
                                </td>
                                <td width='150'>
                                    @if ($item->checked)
                                        <button class="btn btn-danger btn-sm"
                                            wire:click="confirmCancel({{ $item->id }})">
                                            승인취소
                                            @if ($item->log_id)
                                                ({{ $item->log_id }})
                                            @endif
                                        </button>
                                    @else
                                        <button class="btn btn-primary btn-sm"
                                            wire:click="confirm({{ $item->id }})">
                                            입금승인
                                            @if ($item->log_id)
                                                ({{ $item->log_id }})
                                            @endif
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>

        <div class="card-footer">
            @if ($rows)
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
            입금수정
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
                        <button type="button" class="btn btn-danger" wire:click="delete('{{$forms['id']}}')">삭제</button>
                    @endif
                </div>
                <div> {{-- right --}}
                    <button type="button" class="btn btn-secondary"
                        wire:click="cancel">취소</button>
                    <button type="button" class="btn btn-info"
                        wire:click="update">입금 수정</button>
                </div>
            </div>
            @else
            {{-- 생성폼 --}}
            <div class="flex justify-between">
                <div></div>
                <div class="text-right">
                    <button type="button" class="btn btn-secondary"
                        wire:click="cancel">취소</button>

                    <button type="button" class="btn btn-info"
                        wire:click="storeWithdraw">압금</button>
                </div>
            </div>
            @endif
        </x-slot>
    </x-wire-dialog-modal>
    @endif

</div>
