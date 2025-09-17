<div>
    @foreach ($message as $item)
    <article class="mb-4 border-bottom">
        <div class="d-flex justify-content-between">
            <div class="d-flex align-items-center gap-2">
                <h6 class="fw-bold mb-0">
                    {{ $item->subject }}
                </h6>

                @if($item->notice)
                <span class="badge bg-primary">공지</span>
                @endif
            </div>
            <div>
                <small class="float-end text-navy">{{ $item->created_at }}</small>
            </div>
        </div>
        <p>
            {{ $item->message }}
        </p>

    </article>
    @endforeach
</div>
