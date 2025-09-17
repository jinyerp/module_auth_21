{{-- 기본설정 --}}
<x-form-hor>
    <x-form-label>기본</x-form-label>
    <x-form-item>
        <input type="checkbox" class="form-check-input"
            wire:model.defer="forms.default"
            {{ isset($forms['default']) && $forms['default'] ? 'checked' : '' }}/>
    </x-form-item>
</x-form-hor>


{{-- <x-form-hor>
    <x-form-label>이메일</x-form-label>
    <x-form-item>
        {!! xInputText()
            ->setWire('model.defer',"forms.email")
            ->setWidth("standard")
        !!}
    </x-form-item>
</x-form-hor> --}}

<x-form-hor>
    <x-form-label>Swift</x-form-label>
    <x-form-item>
        {!! xInputText()
            ->setWire('model.defer',"forms.swift")
            ->setWidth("standard")
        !!}
    </x-form-item>
</x-form-hor>

<x-form-hor>
    <x-form-label>통화</x-form-label>
    <x-form-item>
        {!! xInputText()
            ->setWire('model.defer',"forms.currency")
            ->setWidth("standard")
        !!}
    </x-form-item>
</x-form-hor>

<x-form-hor>
    <x-form-label>은행</x-form-label>
    <x-form-item>
        {!! xInputText()
            ->setWire('model.defer',"forms.bank")
            ->setWidth("standard")
        !!}
    </x-form-item>
</x-form-hor>

<x-form-hor>
    <x-form-label>계좌번호</x-form-label>
    <x-form-item>
        {!! xInputText()
            ->setWire('model.defer',"forms.account")
            ->setWidth("standard")
        !!}
    </x-form-item>
</x-form-hor>

<x-form-hor>
    <x-form-label>예금주</x-form-label>
    <x-form-item>
        {!! xInputText()
            ->setWire('model.defer',"forms.owner")
            ->setWidth("standard")
        !!}
    </x-form-item>
</x-form-hor>



<x-form-hor>
    <x-form-label>내용</x-form-label>
    <x-form-item>
        {!! xTextarea()
            ->setWire('model.defer',"forms.description")
        !!}
    </x-form-item>
</x-form-hor>
