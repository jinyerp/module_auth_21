{{-- 검색 필터 UI --}}
<div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        {{-- 검색어 입력 --}}
        <div>
            <label for="search" class="block text-xs font-medium text-gray-700 mb-1">
                검색
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input type="text" wire:model.live.debounce.300ms="search" id="search"
                       class="block w-full pl-9 pr-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="국가명, 코드, 수도 검색...">
            </div>
        </div>

        {{-- 대륙 필터 --}}
        <div>
            <label for="filter_region" class="block text-xs font-medium text-gray-700 mb-1">
                대륙
            </label>
            <select wire:model.live="filters.region" id="filter_region"
                    class="block w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                <option value="">전체</option>
                <option value="Africa">Africa</option>
                <option value="Americas">Americas</option>
                <option value="Asia">Asia</option>
                <option value="Europe">Europe</option>
                <option value="Oceania">Oceania</option>
                <option value="Antarctic">Antarctic</option>
            </select>
        </div>

        {{-- 상태 필터 --}}
        <div>
            <label for="filter_status" class="block text-xs font-medium text-gray-700 mb-1">
                상태
            </label>
            <select wire:model.live="filters.is_active" id="filter_status"
                    class="block w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                <option value="">전체</option>
                <option value="1">활성</option>
                <option value="0">비활성</option>
            </select>
        </div>

        {{-- 사용자 필터 --}}
        <div>
            <label for="filter_users" class="block text-xs font-medium text-gray-700 mb-1">
                사용자
            </label>
            <select wire:model.live="filters.has_users" id="filter_users"
                    class="block w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                <option value="">전체</option>
                <option value="yes">사용자 있음</option>
                <option value="no">사용자 없음</option>
            </select>
        </div>

        {{-- 통화 필터 --}}
        <div>
            <label for="filter_currency" class="block text-xs font-medium text-gray-700 mb-1">
                통화 코드
            </label>
            <input type="text" wire:model.live.debounce.300ms="filters.currency_code" id="filter_currency"
                   class="block w-full px-3 py-2 text-xs uppercase border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                   placeholder="USD, EUR, KRW...">
        </div>

        {{-- 언어 필터 --}}
        <div>
            <label for="filter_language" class="block text-xs font-medium text-gray-700 mb-1">
                언어 코드
            </label>
            <input type="text" wire:model.live.debounce.300ms="filters.language" id="filter_language"
                   class="block w-full px-3 py-2 text-xs lowercase border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                   placeholder="ko, en, ja...">
        </div>

        {{-- 정렬 옵션 --}}
        <div>
            <label for="sort_field" class="block text-xs font-medium text-gray-700 mb-1">
                정렬
            </label>
            <select wire:model.live="sortField" id="sort_field"
                    class="block w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                <option value="display_order">표시 순서</option>
                <option value="name">국가명</option>
                <option value="code">ISO2 코드</option>
                <option value="created_at">생성일</option>
                <option value="updated_at">수정일</option>
            </select>
        </div>

        {{-- 정렬 방향 --}}
        <div>
            <label for="sort_direction" class="block text-xs font-medium text-gray-700 mb-1">
                정렬 방향
            </label>
            <select wire:model.live="sortDirection" id="sort_direction"
                    class="block w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                <option value="asc">오름차순 ↑</option>
                <option value="desc">내림차순 ↓</option>
            </select>
        </div>
    </div>

    {{-- 필터 리셋 및 액션 버튼 --}}
    <div class="mt-4 flex items-center justify-between">
        <div class="flex items-center space-x-2">
            <button wire:click="resetFilters" type="button"
                    class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                필터 초기화
            </button>
            
            @if($search || collect($filters)->filter()->isNotEmpty())
            <span class="text-xs text-gray-500">
                {{ $rows->total() }}개 결과 찾음
            </span>
            @endif
        </div>

        <div class="flex items-center space-x-2">
            {{-- CSV 가져오기 --}}
            <label for="import_file" 
                   class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 cursor-pointer">
                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                </svg>
                CSV 가져오기
                <input type="file" id="import_file" wire:model="importFile" accept=".csv,.txt" class="hidden">
            </label>

            {{-- CSV 내보내기 --}}
            <button wire:click="export" type="button"
                    class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"/>
                </svg>
                CSV 내보내기
            </button>

            {{-- 통계 보기 --}}
            <a href="{{ route('admin.auth.countries.statistics') }}"
               class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                통계
            </a>
        </div>
    </div>

    {{-- 선택된 필터 태그 --}}
    @if($search || collect($filters)->filter()->isNotEmpty())
    <div class="mt-3 flex flex-wrap gap-2">
        @if($search)
        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
            검색: {{ $search }}
            <button wire:click="$set('search', '')" type="button" class="ml-1">
                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
            </button>
        </span>
        @endif

        @if($filters['region'] ?? null)
        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
            대륙: {{ $filters['region'] }}
            <button wire:click="$set('filters.region', '')" type="button" class="ml-1">
                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
            </button>
        </span>
        @endif

        @if(($filters['is_active'] ?? null) !== null && $filters['is_active'] !== '')
        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
            상태: {{ $filters['is_active'] ? '활성' : '비활성' }}
            <button wire:click="$set('filters.is_active', '')" type="button" class="ml-1">
                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
            </button>
        </span>
        @endif
    </div>
    @endif
</div>