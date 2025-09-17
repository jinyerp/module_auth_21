<x-wire-table>
    <x-wire-thead>
        {{-- 테이블 제목 --}}

        <th >통화</th>
        <th width='200'>단위</th>
        <th width='200'>환율</th>
        <th width='200'>등록일자</th>
    </x-wire-thead>
    <tbody>
        @if(!empty($rows))
            @foreach ($rows as $item)
            <x-wire-tbody-item :selected="$selected" :item="$item">
                {{-- 테이블 리스트 --}}


                <td >
                    <a href="/admin/auth/currency/log">
                        {{$item->currency}}

                    </a>

                </td>

                <td width='200'>
                    {{$item->unit}}
                </td>

                <td width='200'>
                    <x-link-void wire:click="edit({{$item->id}})">
                        {{$item->rate}}
                    </x-link-void>
                </td>

                <td width='200'>
                    {{$item->created_at}}
                </td>
            </x-wire-tbody-item>
            @endforeach
        @endif
    </tbody>
</x-wire-table>
