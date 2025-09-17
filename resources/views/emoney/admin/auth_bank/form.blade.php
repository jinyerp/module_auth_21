<div>
    <x-navtab class="mb-3 nav-bordered">

        <!-- formTab -->
        <x-navtab-item class="show active" >

            <x-navtab-link class="rounded-0 active">
                <span class="d-none d-md-block">기본정보</span>
            </x-navtab-link>

            <x-form-hor>
                <x-form-label>활성화</x-form-label>
                <x-form-item>
                    <input type="checkbox" class="form-check-input"
                        wire:model.defer="forms.enable"
                        {{ isset($forms['enable']) && $forms['enable'] ? 'checked' : '' }}/>
                </x-form-item>
            </x-form-hor>

            {{-- 기본설정 --}}
            <x-form-hor>
                <x-form-label>기본</x-form-label>
                <x-form-item>
                    <input type="checkbox" class="form-check-input"
                        wire:model.defer="forms.default"
                        {{ isset($forms['default']) && $forms['default'] ? 'checked' : '' }}/>
                </x-form-item>
            </x-form-hor>

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
                    {{-- {!! xInputText()
                        ->setWire('model.defer',"forms.currency")
                        ->setWidth("standard")
                    !!} --}}
                    <select class="form-control"
                        wire:model="forms.currency"
                        style="width: 400px;"
                        placeholder="통화">
                        <option value="">선택</option>
                        @foreach (DB::table('auth_currency')->get() as $item)
                            <option value="{{ $item->id }}:{{ $item->currency }}">
                                {{ $item->currency }}
                            </option>
                        @endforeach
                    </select>


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



        </x-navtab-item>



    </x-navtab>
</div>

