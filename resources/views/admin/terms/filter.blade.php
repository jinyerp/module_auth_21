
<!-- 기본 검색 -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4">
    <div>
        <x-ui::form-input name="filter_title"
        label="약관명"
        placeholder="약관명으로 검색..."
        value="{{ request('filter_title') }}" />
    </div>
    <div>
        <x-ui::form-listbox label="약관 타입" name="filter_type"
            :selected="request('filter_type')">
            <x-ui::form-listbox-item :value="''" :selected-value="request('filter_type')">전체</x-ui::form-listbox-item>
            <x-ui::form-listbox-item :value="'required'" :selected-value="request('filter_type')">필수</x-ui::form-listbox-item>
            <x-ui::form-listbox-item :value="'optional'" :selected-value="request('filter_type')">선택</x-ui::form-listbox-item>
        </x-ui::form-listbox>
    </div>
    <div>
        <x-ui::form-listbox label="활성화" name="filter_is_active"
            :selected="request('filter_is_active')">
            <x-ui::form-listbox-item :value="''" :selected-value="request('filter_is_active')">전체</x-ui::form-listbox-item>
            <x-ui::form-listbox-item :value="'1'" :selected-value="request('filter_is_active')">활성</x-ui::form-listbox-item>
            <x-ui::form-listbox-item :value="'0'" :selected-value="request('filter_is_active')">비활성</x-ui::form-listbox-item>
        </x-ui::form-listbox>
    </div>
    <div>
        <x-ui::form-input name="filter_version"
        label="버전"
        placeholder="버전으로 검색..."
        value="{{ request('filter_version') }}" />
    </div>
</div>

<!-- 고급 검색 옵션 -->
<div class="border-t border-gray-200 pt-4">
    <x-ui::dropdown-link text="고급 검색 옵션 보기">
        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div>
                <label for="filter_effective_date_from" class="block text-sm font-medium text-gray-700 mb-1">시행일 (시작)</label>
                <input type="date" id="filter_effective_date_from" name="filter_effective_date_from"
                    class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
                    value="{{ request('filter_effective_date_from') }}" />
            </div>
            <div>
                <label for="filter_effective_date_to" class="block text-sm font-medium text-gray-700 mb-1">시행일 (종료)</label>
                <input type="date" id="filter_effective_date_to" name="filter_effective_date_to"
                    class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
                    value="{{ request('filter_effective_date_to') }}" />
            </div>
            <div>
                <label for="filter_expiry_date_from" class="block text-sm font-medium text-gray-700 mb-1">만료일 (시작)</label>
                <input type="date" id="filter_expiry_date_from" name="filter_expiry_date_from"
                    class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
                    value="{{ request('filter_expiry_date_from') }}" />
            </div>
            <div>
                <label for="filter_expiry_date_to" class="block text-sm font-medium text-gray-700 mb-1">만료일 (종료)</label>
                <input type="date" id="filter_expiry_date_to" name="filter_expiry_date_to"
                    class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
                    value="{{ request('filter_expiry_date_to') }}" />
            </div>
            <div>
                <label for="filter_display_order" class="block text-sm font-medium text-gray-700 mb-1">표시순서</label>
                <input type="number" id="filter_display_order" name="filter_display_order" min="0"
                    class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm"
                    placeholder="표시순서" value="{{ request('filter_display_order') }}" />
            </div>
            <div>
                <x-ui::form-listbox label="생성일" name="filter_created_at"
                    :selected="request('filter_created_at')">
                    <x-ui::form-listbox-item :value="''" :selected-value="request('filter_created_at')">전체</x-ui::form-listbox-item>
                    <x-ui::form-listbox-item :value="'today'" :selected-value="request('filter_created_at')">오늘</x-ui::form-listbox-item>
                    <x-ui::form-listbox-item :value="'week'" :selected-value="request('filter_created_at')">이번 주</x-ui::form-listbox-item>
                    <x-ui::form-listbox-item :value="'month'" :selected-value="request('filter_created_at')">이번 달</x-ui::form-listbox-item>
                    <x-ui::form-listbox-item :value="'year'" :selected-value="request('filter_created_at')">올해</x-ui::form-listbox-item>
                </x-ui::form-listbox>
            </div>
        </div>
    </x-ui::dropdown-link>
</div>
