<div>
    <x-navtab class="mb-3 nav-bordered">

        <!-- formTab -->
        <x-navtab-item class="show active" >
            <x-navtab-link class="rounded-0 active">
                <span class="d-none d-md-block">기본정보</span>
            </x-navtab-link>

            <x-form-hor>
                <x-form-label>이메일</x-form-label>
                <x-form-item>
                    <input type="text" class="form-control"
                        style="width:453px;"
                        wire:model.defer="forms.email">
                </x-form-item>
            </x-form-hor>

            @if($mode == 'deposit')
            <x-form-hor>
                <x-form-label>입금</x-form-label>
                <x-form-item>
                    {!! xInputText()
                        ->setWire('model.defer',"forms.deposit")
                        ->setWidth("standard")
                    !!}
                </x-form-item>
            </x-form-hor>
            @endif

            @if($mode == 'withdraw')
            <x-form-hor>
                <x-form-label>출금</x-form-label>
                <x-form-item>
                    {!! xInputText()
                        ->setWire('model.defer',"forms.withdraw")
                        ->setWidth("standard")
                    !!}
                </x-form-item>
            </x-form-hor>
            @endif

            <x-form-hor>
                <x-form-label>메모</x-form-label>
                <x-form-item>
                    {!! xTextarea()
                        ->setWire('model.defer',"forms.description")
                    !!}
                </x-form-item>
            </x-form-hor>

        </x-navtab-item>



    </x-navtab>
</div>
