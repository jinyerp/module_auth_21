<x-wire-table>
    <x-wire-thead>
        {{-- 테이블 제목 --}}
        <th width='200'>
            회원
        </th>

        <th width='100'>Swift</th>
        <th >은행</th>
        <th width='200'>통화</th>
        <th width='200'>계좌</th>
        <th width='200'>예금주</th>
        <th width='200'>등록일자</th>
    </x-wire-thead>
    <tbody>
        @if(!empty($rows))
            @foreach ($rows as $item)
            <x-wire-tbody-item :selected="$selected" :item="$item">
                {{-- 테이블 리스트 --}}
                <td width='200'>
                    <x-link-void wire:click="edit({{$item->id}})">
                        {{$item->email}}
                    </x-link-void>
                </td>

                <td width='100'>
                    {{$item->swift}}
                </td>

                <td >
                    {{$item->bank}}
                </td>

                <td width='200'>
                    {{$item->currency}}
                </td>
                <td width='200'>
                    {{$item->account}}
                </td>

                <td width='200'>
                    {{$item->owner}}
                </td>

                <td width='200'>{{$item->created_at}}</td>

            </x-wire-tbody-item>
            @endforeach
        @endif
    </tbody>
</x-wire-table>
