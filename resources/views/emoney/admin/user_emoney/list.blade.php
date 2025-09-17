<table class="table table-hover mb-0">
    <thead>
        <tr>
            <th width="100" class="text-center">
                ID
            </th>
            <th>회원명</th>
            <th width="250">통화</th>
            <th width="250">적립금</th>
            <th width="250">포인트</th>
            <th width="250">최근일자</th>
        </tr>
    </thead>
    <tbody>
        @if (!empty($rows))
            @foreach ($rows as $item)
                <tr>
                    <td width="100" class="text-center">
                        {{ $item->id }}
                    </td>
                    <td class="d-flex gap-2 align-items-center">
                        <x-user-avata :id="$item->user_id" />
                        <span>
                            <div>{{ $item->name }}</div>
                            <div class="text-muted">
                                {{ $item->email }}
                            </div>
                        </span>
                    </td>
                    <td width="250">
                        {{ $item->currency }}
                    </td>
                    <td width="250">
                        <a href="/admin/auth/emoney/log/{{ $item->id }}">
                            {{ $item->balance }}
                        </a>
                    </td>
                    <td width="250">
                        {{ $item->point }}
                    </td>
                    <td width="250">
                        {{ $item->updated_at }}
                    </td>
                </tr>
            @endforeach
        @endif
    </tbody>
</table>

@if (count($rows) == 0)
    <div class="text-center mb-3">
        데이터가 없습니다.
    </div>
@endif
