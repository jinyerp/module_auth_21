<div class="row">
    @foreach($rows as $row)
        <div class="col-md-4">
            <div class="card mb-3 h-100">
                <div class="card-body">
                    <h5 class="card-title">
                        <x-click wire:click="edit({{ $row->id }})">
                            {{ $row->bank }}
                        </x-click>
                    </h5>
                    <p class="card-text">
                        계좌번호: {{ $row->account }}<br>
                        예금주: {{ $row->owner }}
                    </p>
                </div>
            </div>
        </div>
    @endforeach
    <div class="col-md-4">
        <div class="card mb-3 h-100">
            <div class="card-body d-flex justify-content-center align-items-center h-100">
                <span class="cursor-pointer" wire:click="create">
                    + 계좌 등록
                </span>
            </div>
        </div>
    </div>
</div>
