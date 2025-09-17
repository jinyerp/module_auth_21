<div>
    <x-navtab class="mb-3 nav-bordered">

        <!-- formTab -->
        <x-navtab-item class="show active">

            <x-navtab-link class="rounded-0 active">
                <span class="d-none d-md-block">기본정보</span>
            </x-navtab-link>

            <x-form-hor>
                <x-form-label>활성화</x-form-label>
                <x-form-item>
                    <input type="checkbox" class="form-check-input" wire:model.defer="forms.enable"
                        {{ isset($forms['enable']) && $forms['enable'] ? 'checked' : '' }} />
                </x-form-item>
            </x-form-hor>




            <x-form-hor>
                <x-form-label>이메일</x-form-label>
                <x-form-item>
                    {!! xInputText()->setWire('model.defer', 'forms.email')->setWidth('standard') !!}
                </x-form-item>
            </x-form-hor>


            {{-- 기본설정 --}}
            <x-form-hor>
                <x-form-label>처리</x-form-label>
                <x-form-item>
                    <input type="checkbox" class="form-check-input" wire:model.defer="forms.checked"
                        {{ isset($forms['checked']) && $forms['checked'] ? 'checked' : '' }} />
                </x-form-item>
            </x-form-hor>

            <x-form-hor>
                <x-form-label>상태</x-form-label>
                <x-form-item>
                    <select class="form-select" wire:model.defer="forms.status">
                        <option value="">선택</option>
                        <option value="pending">대기</option>
                        <option value="success">완료</option>
                    </select>
                </x-form-item>
            </x-form-hor>

            <x-form-hor>
                <x-form-label>통화</x-form-label>
                <x-form-item>
                    {!! xInputText()->setWire('model.defer', 'forms.currency')->setWidth('standard') !!}
                </x-form-item>
            </x-form-hor>

            <x-form-hor>
                <x-form-label>은행</x-form-label>
                <x-form-item>
                    {!! xInputText()->setWire('model.defer', 'forms.bank')->setWidth('standard') !!}
                </x-form-item>
            </x-form-hor>

            <x-form-hor>
                <x-form-label>계좌번호</x-form-label>
                <x-form-item>
                    {!! xInputText()->setWire('model.defer', 'forms.account')->setWidth('standard') !!}
                </x-form-item>
            </x-form-hor>

            <x-form-hor>
                <x-form-label>예금주</x-form-label>
                <x-form-item>
                    {!! xInputText()->setWire('model.defer', 'forms.owner')->setWidth('standard') !!}
                </x-form-item>
            </x-form-hor>



            <x-form-hor>
                <x-form-label>내용</x-form-label>
                <x-form-item>
                    {!! xTextarea()->setWire('model.defer', 'forms.description') !!}
                </x-form-item>
            </x-form-hor>



        </x-navtab-item>



    </x-navtab>
</div>
