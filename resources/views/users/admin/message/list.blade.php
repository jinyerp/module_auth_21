<x-wire-table>
    <x-wire-thead>
        {{-- 테이블 제목 --}}
        <th width='100'>수신자</th>
        <th>내용</th>
        <th width='200'>확인/발신자</th>
        <th width='200'>등록일자</th>

    </x-wire-thead>
    <tbody>
        @if(!empty($rows))
            @foreach ($rows as $item)
            <x-wire-tbody-item :selected="$selected" :item="$item">
                {{-- 테이블 리스트 --}}
                <td width='100'>
                    {{$item->email}}
                </td>

                <td class="d-flex gap-2 align-items-center">
                    @if($item->notice)
                    <span class="badge bg-primary">공지</span>
                    @endif

                    <div wire:click="edit({{$item->id}})">
                        {{$item->subject}}
                    </div>
                </td>

                <td width='200'>
                    @if($item->readed_at)
                    <span class="badge bg-info">읽음</span>
                    @endif

                    <div>
                        {{$item->from_email}}
                    </div>
                </td>
                <td width='200'>{{$item->created_at}}</td>
            </x-wire-tbody-item>
            @endforeach
        @endif
    </tbody>
</x-wire-table>
