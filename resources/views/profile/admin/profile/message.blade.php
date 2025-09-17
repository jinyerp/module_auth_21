<div class="card">
    <div class="card-header">
        {{-- <div class="card-actions float-end">
            <div class="dropdown position-relative">
                <a href="#" data-bs-toggle="dropdown" data-bs-display="static">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" data-lucide="more-horizontal"
                        class="lucide lucide-more-horizontal align-middle">
                        <circle cx="12" cy="12" r="1"></circle>
                        <circle cx="19" cy="12" r="1"></circle>
                        <circle cx="5" cy="12" r="1"></circle>
                    </svg>
                </a>

                <div class="dropdown-menu dropdown-menu-end">
                    <a class="dropdown-item" href="#">Action</a>
                    <a class="dropdown-item" href="#">Another action</a>
                    <a class="dropdown-item" href="#">Something else here</a>
                </div>
            </div>
        </div> --}}
        <h5 class="card-title mb-0">시스템 메시지</h5>
    </div>
    <div class="card-body h-100">
        @php
            $messages = DB::table('user_messages')
                ->where('to_user_id', Auth::user()->id)
                ->paginate(10);
        @endphp
        @foreach ($messages as $item)
            @if ($item->status == 'new' && !$loop->first)
                <hr>
            @endif

            <div class="d-flex align-items-start">
                <img src="/home/user/avatar/{{ $item->user_id }}" width="36" height="36"
                    class="rounded-circle me-2" alt="Ashley Briggs">
                <div class="flex-grow-1">
                    <small class="float-end">{{ $item->created_at }}</small>
                    <strong>{{ $item->name }} : {{ $item->subject }}</strong><br>
                    <small class="text-muted">{{ $item->message }}</small><br>
                </div>
            </div>
        @endforeach

        <hr>
        <div class="d-grid">
            {{-- <a href="#" class="btn btn-primary">Load more</a> --}}
            {{ $messages->links() }}
        </div>
    </div>
</div>
