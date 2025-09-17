{{-- 2단계 인증 검색 필터 --}}
<div class="bg-white p-4 rounded-lg shadow-sm mb-4">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        {{-- 검색어 입력 --}}
        <div>
            <label for="search" class="block text-xs font-medium text-gray-700 mb-1">
                사용자 검색
            </label>
            <div class="relative">
                <input type="text"
                       wire:model.debounce.500ms="search"
                       id="search"
                       placeholder="이름 또는 이메일..."
                       class="w-full px-3 py-2 text-xs border border-gray-300 rounded-md pl-8 focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                <svg class="absolute left-2 top-2.5 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
        </div>
        
        {{-- 상태 필터 --}}
        <div>
            <label for="filter_enabled" class="block text-xs font-medium text-gray-700 mb-1">
                활성화 상태
            </label>
            <select wire:model="filters.enabled"
                    id="filter_enabled"
                    class="w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                <option value="">전체</option>
                <option value="1">활성화</option>
                <option value="0">비활성화</option>
            </select>
        </div>
        
        {{-- 인증 방법 필터 --}}
        <div>
            <label for="filter_method" class="block text-xs font-medium text-gray-700 mb-1">
                인증 방법
            </label>
            <select wire:model="filters.method"
                    id="filter_method"
                    class="w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                <option value="">전체</option>
                <option value="totp">TOTP (앱)</option>
                <option value="sms">SMS</option>
                <option value="email">이메일</option>
            </select>
        </div>
        
        {{-- 실패 시도 필터 --}}
        <div>
            <label for="filter_failed" class="block text-xs font-medium text-gray-700 mb-1">
                실패 시도
            </label>
            <select wire:model="filters.failed_attempts"
                    id="filter_failed"
                    class="w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                <option value="">전체</option>
                <option value="0">없음</option>
                <option value="1-2">1-2회</option>
                <option value="3-4">3-4회</option>
                <option value="5+">5회 이상</option>
            </select>
        </div>
    </div>
    
    {{-- 고급 필터 (토글 가능) --}}
    <div x-data="{ showAdvanced: false }" class="mt-4">
        <button @click="showAdvanced = !showAdvanced"
                type="button"
                class="text-xs text-blue-600 hover:text-blue-800 font-medium flex items-center">
            <span x-show="!showAdvanced">고급 필터 표시</span>
            <span x-show="showAdvanced">고급 필터 숨기기</span>
            <svg class="w-3 h-3 ml-1 transition-transform" 
                 :class="{ 'rotate-180': showAdvanced }"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>
        
        <div x-show="showAdvanced" x-transition class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
            {{-- 최근 사용 기간 --}}
            <div>
                <label for="filter_last_used" class="block text-xs font-medium text-gray-700 mb-1">
                    최근 사용
                </label>
                <select wire:model="filters.last_used"
                        id="filter_last_used"
                        class="w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">전체 기간</option>
                    <option value="today">오늘</option>
                    <option value="week">최근 7일</option>
                    <option value="month">최근 30일</option>
                    <option value="never">사용 안함</option>
                </select>
            </div>
            
            {{-- 생성 기간 --}}
            <div>
                <label for="filter_created_from" class="block text-xs font-medium text-gray-700 mb-1">
                    생성일 (시작)
                </label>
                <input type="date"
                       wire:model="filters.created_from"
                       id="filter_created_from"
                       class="w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
            </div>
            
            <div>
                <label for="filter_created_to" class="block text-xs font-medium text-gray-700 mb-1">
                    생성일 (종료)
                </label>
                <input type="date"
                       wire:model="filters.created_to"
                       id="filter_created_to"
                       class="w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
            </div>
        </div>
    </div>
    
    {{-- 필터 초기화 버튼 --}}
    @if($search || count(array_filter($filters ?? [])) > 0)
    <div class="mt-4 flex items-center justify-between">
        <button wire:click="clearFilters"
                type="button"
                class="text-xs text-gray-600 hover:text-gray-900 font-medium">
            <svg class="w-3 h-3 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
            필터 초기화
        </button>
        
        <div class="text-xs text-gray-500">
            {{ $rows->total() }}개 결과
        </div>
    </div>
    @endif
</div>