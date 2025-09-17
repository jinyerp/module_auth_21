<div>
    <div class="d-flex justify-content-center align-items-center gap-2">
        <h4>출금 가능금액 {{ number_format($deposit - $pending - $point) }} 원</h4>

    </div>
    <div class="d-flex justify-content-center align-items-center gap-2 mb-4">

        <span class="text-muted">
            (전체 {{ number_format($deposit) }} 원)
        : 적입된 포인트는 출금 금액에서 제외 됩니다.
        </span>
    </div>
    <div class="d-flex justify-content-center align-items-center gap-2 mb-4">
        <div>
            <label>
                <a href="/home/emoney/bank">은행관리</a>
            </label>
        </div>
        <div>
            <select class="form-control" wire:model="bank_id" placeholder="출금은행">
                <option value="">선택</option>
                @foreach (DB::table('user_emoney_bank')->get() as $bank)
                    <option value="{{ $bank->id }}">{{ $bank->bank }} / {{ $bank->account }} / {{ $bank->owner }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <input type="text" class="form-control" wire:model="amount" placeholder="출금금액">
        </div>
        <button class="btn btn-info" wire:click="withdraw">
            출금하기
        </button>
    </div>

    @if ($message)
        <div class="d-flex justify-content-center gap-2 mb-4">
            <div class="alert alert-danger">{{ $message }}</div>
        </div>
    @endif

    <table class="table table-hover">
        <thead>
            <tr>
                <th>요청일시</th>
                <th>출금은행</th>
                <th>출금금액</th>
                <th>처리상태</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($withdraw as $item)
                <tr>
                    <td>{{ $item->created_at }}</td>
                    <td>{{ $item->bank }} / {{ $item->account }} / {{ $item->owner }}</td>
                    <td>{{ $item->amount }}</td>
                    <td>
                        <div class="d-flex gap-2">
                            {{ $item->status }}
                            @if ($item->checked == null && $item->log_id == null)
                                <button class="btn btn-sm btn-danger"
                                    wire:click="cancel({{ $item->id }})">
                                    취소요청
                                </button>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @if ($withdraw)
    <div class="d-flex justify-content-center gap-2 mb-4">
            {{ $withdraw->links() }}
        </div>
    @endif





</div>
