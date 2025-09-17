<div class="card">
    <div class="card-header">
        <div class="card-actions float-end">
            <button class="btn btn-sm btn-primary" wire:click="create">추가</button>
        </div>
        <h5 class="card-title mb-0">연락처</h5>
    </div>


    @if(!$popupForm)
        {{-- 목록 표시 --}}
        <table class="table table-sm table-striped my-0">
            <thead>
                <tr>
                    <th>선택</th>
                    <th>타입</th>
                    <th>연락처</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rows as $i => $item)
                    <tr>
                        <td>
                            @if ($item->selected)
                                <x-badge-primary class="cursor-pointer" wire:click="selected({{ $item->id }})">
                                    기본
                                </x-badge-primary>
                            @else
                                <x-badge-secondary class="cursor-pointer" wire:click="selected({{ $item->id }})">
                                    기본
                                </x-badge-secondary>
                            @endif

                        </td>
                        <td>
                            {{$item->type}}

                        </td>
                        <td>
                            <span>{{$item->country}}</span>
                            <x-click wire:click="edit({{ $item->id }})">
                                {{$item->number}}
                            </x-click>
                        </td>

                    </tr>
                @endforeach
            </tbody>
        </table>


    @else
    <div class="card-body">
        {{-- inline 추가폼 --}}
        @includeIf($viewForm)

        <x-flex-between class="mt-3">
            <div>
                @if($edit_id)
                <button class="btn btn-danger me-2" wire:click="delete">삭제</button>
                @endif
            </div>
            <div>
                <button class="btn btn-light" wire:click="close">취소</button>
                @if($edit_id)
                <button class="btn btn-info me-2" wire:click="update">수정</button>
                @else
                <button class="btn btn-primary me-2" wire:click="store">등록</button>
                @endif
            </div>
        </x-flex-between>
    </div>
    @endif
</div>

