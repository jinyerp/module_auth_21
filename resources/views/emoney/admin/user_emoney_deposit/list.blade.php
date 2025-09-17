<x-wire-table>
    <x-wire-thead>
        {{-- 테이블 제목 --}}
        <th width='200'>
            회원
        </th>

       
        <th >은행/계좌/예금주</th>
        <th width='200'>출금액</th>
        <th width='200'>출금상태</th>
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

                <td >
                    {{$item->bank}} /  {{$item->account}} / {{$item->owner}}
                </td>

                <td width='200'>
                    {{$item->amount}}   
                </td>

                <td width='200'>
                    {{$item->status}}   
                </td>

                <td width='200'>{{$item->created_at}}</td>

            </x-wire-tbody-item>
            @endforeach
        @endif
    </tbody>
</x-wire-table>
