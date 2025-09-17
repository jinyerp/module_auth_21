<div>
    <x-navtab class="mb-3 nav-bordered">

        <!-- formTab -->
        <x-navtab-item class="show active" >
            <x-navtab-link class="rounded-0 active">
                <span class="d-none d-md-block">메시지</span>
            </x-navtab-link>

            <x-form-hor>
                <x-form-label>활성화</x-form-label>
                <x-form-item>
                    <input type="checkbox" class="form-check-input"
                        wire:model="forms.enable"
                        {{ isset($forms['enable']) && $forms['enable'] ? 'checked' : '' }}>
                </x-form-item>
            </x-form-hor>



            <x-form-hor>
                <x-form-label>수신자</x-form-label>
                <x-form-item>
                    {!! xInputText()
                        ->setWire('model.defer',"forms.email")
                        ->setWidth("standard")
                    !!}
                </x-form-item>
            </x-form-hor>

            <x-form-hor>
                <x-form-label>제목</x-form-label>
                <x-form-item>
                    {!! xInputText()
                        ->setWire('model.defer',"forms.subject")
                        ->setWidth("standard")
                    !!}
                </x-form-item>
            </x-form-hor>

            <x-form-hor>
                <x-form-label>메시지</x-form-label>
                <x-form-item>
                    {!! xTextarea()
                        ->setWire('model.defer',"forms.message")
                    !!}
                </x-form-item>
            </x-form-hor>

            <x-form-hor>
                <x-form-label>전체 공지</x-form-label>
                <x-form-item>
                    <input type="checkbox" class="form-check-input"
                        wire:model="forms.notice"
                        {{ isset($forms['notice']) && $forms['notice'] ? 'checked' : '' }}>
                </x-form-item>
            </x-form-hor>

        </x-navtab-item>

        <!-- formTab -->
        <x-navtab-item class="" >
            <x-navtab-link class="rounded-0">
                <span class="d-none d-md-block">발신자</span>
            </x-navtab-link>

            <x-form-hor>
                <x-form-label>발신자</x-form-label>
                <x-form-item>
                    {!! xInputText()
                        ->setWire('model.defer',"forms.from_email")
                        ->setWidth("standard")
                    !!}
                </x-form-item>
            </x-form-hor>



        </x-navtab-item>

    </x-navtab>


</div>
