<div class="bg-white rounded-lg shadow p-4">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        {{-- 검색어 입력 --}}
        <div class="md:col-span-2">
            <label for="search" class="block text-xs font-medium text-gray-700 mb-1">
                검색
            </label>
            <div class="relative">
                <input type="text" 
                       wire:model.debounce.300ms="search" 
                       id="search"
                       placeholder="차단 대상, 사유, 설명으로 검색..."
                       class="w-full pl-8 pr-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                <svg class="absolute left-2.5 top-2.5 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
        </div>

        {{-- 차단 유형 필터 --}}
        <div>
            <label for="filter_type" class="block text-xs font-medium text-gray-700 mb-1">
                차단 유형
            </label>
            <select wire:model="filters.type" 
                    id="filter_type"
                    class="w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                <option value="">전체</option>
                <option value="email">이메일</option>
                <option value="ip">IP 주소</option>
                <option value="phone">전화번호</option>
                <option value="domain">도메인</option>
                <option value="user_agent">User Agent</option>
                <option value="account">계정</option>
            </select>
        </div>

        {{-- 상태 필터 --}}
        <div>
            <label for="filter_status" class="block text-xs font-medium text-gray-700 mb-1">
                상태
            </label>
            <select wire:model="filters.status" 
                    id="filter_status"
                    class="w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                <option value="">전체</option>
                <option value="active">활성</option>
                <option value="inactive">비활성</option>
                <option value="expired">만료됨</option>
            </select>
        </div>

        {{-- 차단 기간 필터 --}}
        <div>
            <label for="filter_permanent" class="block text-xs font-medium text-gray-700 mb-1">
                차단 기간
            </label>
            <select wire:model="filters.is_permanent" 
                    id="filter_permanent"
                    class="w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                <option value="">전체</option>
                <option value="1">영구 차단</option>
                <option value="0">임시 차단</option>
            </select>
        </div>

        {{-- 날짜 범위 필터 --}}
        <div>
            <label for="filter_date_from" class="block text-xs font-medium text-gray-700 mb-1">
                등록일 (시작)
            </label>
            <input type="date" 
                   wire:model="filters.date_from" 
                   id="filter_date_from"
                   class="w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div>
            <label for="filter_date_to" class="block text-xs font-medium text-gray-700 mb-1">
                등록일 (종료)
            </label>
            <input type="date" 
                   wire:model="filters.date_to" 
                   id="filter_date_to"
                   class="w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
        </div>

        {{-- 차단 횟수 필터 --}}
        <div>
            <label for="filter_hit_count" class="block text-xs font-medium text-gray-700 mb-1">
                최소 차단 횟수
            </label>
            <input type="number" 
                   wire:model="filters.min_hit_count" 
                   id="filter_hit_count"
                   min="0"
                   placeholder="0"
                   class="w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
        </div>

        {{-- 필터 리셋 버튼 --}}
        <div class="flex items-end">
            <button wire:click="resetFilters" 
                    class="w-full px-4 py-2 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <svg class="inline-block w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                필터 초기화
            </button>
        </div>
    </div>

    {{-- 고급 검색 옵션 --}}
    <details class="mt-4">
        <summary class="text-xs font-medium text-gray-700 cursor-pointer hover:text-gray-900">
            고급 검색 옵션
        </summary>
        <div class="mt-3 p-3 bg-gray-50 rounded-md">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                {{-- 차단자 검색 --}}
                <div>
                    <label for="filter_blocked_by" class="block text-xs font-medium text-gray-700 mb-1">
                        차단자
                    </label>
                    <input type="text" 
                           wire:model.debounce.300ms="filters.blocked_by_name" 
                           id="filter_blocked_by"
                           placeholder="차단자 이름으로 검색"
                           class="w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                </div>

                {{-- 특정 값 검색 --}}
                <div>
                    <label for="filter_exact_value" class="block text-xs font-medium text-gray-700 mb-1">
                        정확한 값
                    </label>
                    <input type="text" 
                           wire:model.debounce.300ms="filters.exact_value" 
                           id="filter_exact_value"
                           placeholder="정확한 차단 대상 값"
                           class="w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                </div>

                {{-- 정렬 옵션 --}}
                <div>
                    <label for="sort_by" class="block text-xs font-medium text-gray-700 mb-1">
                        정렬 기준
                    </label>
                    <select wire:model="sortBy" 
                            id="sort_by"
                            class="w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                        <option value="created_at">등록일</option>
                        <option value="hit_count">차단 횟수</option>
                        <option value="last_hit_at">마지막 차단</option>
                        <option value="expires_at">만료일</option>
                        <option value="type">차단 유형</option>
                        <option value="value">차단 대상</option>
                    </select>
                </div>
            </div>
        </div>
    </details>

    {{-- 현재 필터 표시 --}}
    @if($search || !empty(array_filter($filters ?? [])))
    <div class="mt-3 flex flex-wrap gap-2">
        @if($search)
        <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-blue-100 text-blue-800">
            검색: {{ $search }}
            <button wire:click="$set('search', '')" class="ml-1 text-blue-600 hover:text-blue-800">
                <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
            </button>
        </span>
        @endif

        @foreach($filters ?? [] as $key => $value)
            @if($value)
            <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-gray-100 text-gray-800">
                {{ $this->getFilterLabel($key) }}: {{ $this->getFilterValue($key, $value) }}
                <button wire:click="$set('filters.{{ $key }}', '')" class="ml-1 text-gray-600 hover:text-gray-800">
                    <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </button>
            </span>
            @endif
        @endforeach
    </div>
    @endif
</div>