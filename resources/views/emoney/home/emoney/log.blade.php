<div>
    <table class="table table-hover">
        <thead>
            <tr>
                <th width='200'>일자</th>
                <th width='100'>타입</th>
                <th>내역</th>

                <th width='150'>출금</th>
                <th width='150'>입금</th>
                <th width='150'>잔액</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($emoney as $item)
                <tr>
                    <td width='200'>
                        <span class="text-muted fs-sm">
                            {{ $item->created_at }}
                        </span>
                    </td>
                    <td width='100'>
                        <span class="text-muted fs-sm">
                            {{ $item->type }}
                        </span>
                    </td>
                    <td>
                        <span class="text-muted fs-sm">
                            {{ $item->description }}
                        </span>
                    </td>

                    <td>
                        <span class="text-muted fs-sm">
                            {{ $item->withdraw }}
                        </span>
                    </td>
                    <td>
                        <span class="text-muted fs-sm">
                            {{ $item->deposit }}
                        </span>
                    </td>
                    <td>
                        <span class="text-muted fs-sm">
                            {{ $item->balance }}
                        </span>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @if (count($emoney) == 0)
        <div class="text-center py-4">
            <p class="text-muted">적립금 내역이 없습니다.</p>
        </div>
    @endif

    {{-- 페이지 네비게이션 --}}
    @if ($emoney)
        <div class="mt-4">
            {{ $emoney->links() }}
        </div>
    @endif
</div>
