<div>
    <div class="d-flex justify-content-center align-items-center gap-2 mb-8">
        <div class="form-check">
            <input class="form-check-input" type="radio" wire:model.live="type" value="bank" id="bank" checked>
            <label class="form-check-label" for="bank">
                무통장입금
            </label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="radio" wire:model.live="type" value="card" id="card">
            <label class="form-check-label" for="card">
                신용카드
            </label>
        </div>
    </div>

    @if ($type == 'bank')
        <div class="d-flex justify-content-center align-items-center gap-2 mb-4">
            <div>
                <select class="form-control" wire:model="bank_id" placeholder="출금은행">
                    <option value="">선택</option>
                    @foreach (DB::table('auth_bank')->get() as $bank)
                        <option value="{{ $bank->id }}">
                            {{ $bank->bank }} / {{ $bank->account }} / {{ $bank->owner }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <input type="text" class="form-control" wire:model="amount" placeholder="입금금액">
            </div>
            <button class="btn btn-info" wire:click="deposit">
                충전하기
            </button>
        </div>
    @endif

    @if ($type == 'card')
    <div class="row mb-8 justify-content-center">
        <div class="col-12 col-md-3">
            $10
        </div>
        <div class="col-12 col-md-3">
            $50
        </div>
        <div class="col-12 col-md-3">
            $100
        </div>
    </div>

        <div class="row mb-8 justify-content-center">
            <div class="col-12 col-md-4">
                <div class="form-check fs-xl">
                    <input type="radio" class="form-check-input"
                        id="card" name="payment" value="card"
                        wire:model="card_type">
                    <label for="card" class="form-check-label fs-base fw-medium text-body-emphasis ps-1">Pay by
                        card</label>
                </div>
                <p class="fs-sm">Visa, Mastercard, Maestro, Discover</p>

                <div class="position-relative mb-3" data-input-format="{&quot;creditCard&quot;: true}">
                    <input type="text" class="form-control form-icon-end rounded-pill"
                        placeholder="Enter card number">
                    <span class="position-absolute d-flex top-50 end-0 translate-middle-y fs-5 text-body-tertiary me-3"
                        data-card-icon=""><svg width="1em" height="1em" viewBox="0 0 24 24" fill="currentColor">
                            <path
                                d="M21 3H3C1.3 3 0 4.3 0 6v12c0 1.7 1.3 3 3 3h18c1.7 0 3-1.3 3-3V6c0-1.7-1.3-3-3-3zm1.2 15c0 .7-.6 1.2-1.2 1.2H3c-.7 0-1.2-.6-1.2-1.2V6c0-.7.6-1.2 1.2-1.2h18c.7 0 1.2.6 1.2 1.2v12z">
                            </path>
                            <path
                                d="M7 16.1H4c-.5 0-.9.4-.9.9s.4.9.9.9h3c.5 0 .9-.4.9-.9s-.4-.9-.9-.9zm13-9H4c-.5 0-.9.4-.9.9s.4.9.9.9h16c.5 0 .9-.4.9-.9s-.4-.9-.9-.9z">
                            </path>
                        </svg></span>
                </div>
                <div class="d-flex gap-3">
                    <input type="text" class="form-control w-50 rounded-pill"
                        data-input-format="{&quot;date&quot;: true, &quot;datePattern&quot;: [&quot;m&quot;, &quot;y&quot;]}"
                        placeholder="MM/YY">
                    <input type="text" class="form-control w-50 rounded-pill" maxlength="4"
                        data-input-format="{&quot;numeral&quot;: true, &quot;numeralPositiveOnly&quot;: true, &quot;numeralThousandsGroupStyle&quot;: &quot;none&quot;}"
                        placeholder="CVC">
                </div>

            </div>
            <div class="col-12 col-md-4">
                <div class="form-check fs-xl mt-4">
                    <input type="radio" class="form-check-input"
                        id="paypal" name="payment" value="paypal"
                        wire:model="card_type">

                    <label for="paypal" class="form-check-label fs-base fw-medium text-body-emphasis ps-1">PayPal</label>
                </div>
                <div class="d-flex align-items-center justify-content-between w-100 mt-4 mb-3">
                    <span class="fs-sm">Estimated total:</span>
                    <span class="h6 mb-0">$47</span>
                </div>
                <button type="button" class="btn btn-lg btn-dark w-100 rounded-pill">
                    Pay
                </button>
                <div class="d-flex align-items-center justify-content-center fs-xs text-body-secondary mt-3">
                    <i class="ci-lock me-1"></i>
                    Your payment is secure
                </div>
            </div>

        </div>
    @endif



    @if ($message)
        <div class="d-flex justify-content-center gap-2 mb-4">
            <div class="alert alert-danger">{{ $message }}</div>
        </div>
    @endif

    <table class="table table-hover">
        <thead>
            <tr>
                <th>결제일자</th>
                <th>입금은행</th>
                <th>입금금액</th>
                <th>처리상태</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($deposit as $item)
                <tr>
                    <td>{{ $item->created_at }}</td>
                    <td>{{ $item->bank }} / {{ $item->account }} / {{ $item->owner }}</td>
                    <td>{{ $item->amount }}</td>
                    <td>
                        <div class="d-flex gap-2">
                            {{ $item->status }}
                            @if ($item->checked == null && $item->log_id == null)
                                <button class="btn btn-sm btn-secondary" wire:click="cancel({{ $item->id }})">
                                    삭제
                                </button>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @if ($deposit)
        <div class="d-flex justify-content-center gap-2 mb-4">
            {{ $deposit->links() }}
        </div>
    @endif





</div>
