<x-wire-table>
    <x-wire-thead>
        {{-- 테이블 제목 --}}


        <th >은행</th>
        <th width='100'>통화</th>
        <th width='200'>계좌</th>
        <th width='200'>예금주</th>
        <th width='200'>등록일자</th>
    </x-wire-thead>
    <tbody>
        @if(!empty($rows))
            @foreach ($rows as $item)
            <x-wire-tbody-item :selected="$selected" :item="$item">
                {{-- 테이블 리스트 --}}



                <td >
                    <x-link-void wire:click="edit({{$item->id}})">
                        {{$item->bank}}
                    </x-link-void>

                    @if($item->swift)
                        <span class="badge badge-info">
                            {{$item->swift}}
                        </span>
                    @endif
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

                <td width='200'>
                    {{$item->created_at}}
                </td>
            </x-wire-tbody-item>
            @endforeach
        @endif
    </tbody>
</x-wire-table>
