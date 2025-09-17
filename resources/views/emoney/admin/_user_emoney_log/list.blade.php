<x-wire-table>
    {{-- 테이블 제목 --}}
    <x-wire-thead>
        <th width='50'>Id</th>
        <th width='200'>
            {!! xWireLink('이메일', "orderBy('email')") !!}
        </th>
        <th>설명</th>

        <th width='100'>입금</th>
        <th width='100'>출금</th>
        <th width='100'>잔액</th>
        <th width='200'>생성일자</th>
    </x-wire-thead>
    <tbody>
        @if(!empty($rows))
            @foreach ($rows as $item)
            {{-- 테이블 리스트 --}}
            <x-wire-tbody-item :selected="$selected" :item="$item">
                <td width='50'>{{$item->id}}</td>
                <td width='200'>
                    <x-click wire:click="edit({{$item->id}})">
                        {{$item->email}}
                        ({{$item->user_id}})
                    </x-click>
                </td>
                <td>
                    {{$item->description}}
                </td>

                <td width='100'>
                    <div>{{$item->deposit}}</div>
                </td>
                <td width='100'>
                    <div>{{$item->withdraw}}</div>
                </td>

                <td width='100'>
                    <div>{{$item->balance}}</div>
                </td>

                <td width='200'>
                    <div>{{$item->created_at}}</div>
                </td>
            </x-wire-tbody-item>
            @endforeach
        @endif
    </tbody>
</x-wire-table>
