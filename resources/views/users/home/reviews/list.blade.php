<div>
    @foreach ($reviews as $item)
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <span>
                            {{ $item->title }}
                        </sapn>
                    </div>
                    <div>
                        <small class="float-end text-navy">{{ $item->created_at }}</small>
                    </div>
                </div>
                <div>
                    {{ $item->review }}
                </div>
            </div>
        </div>
    @endforeach
</div>
