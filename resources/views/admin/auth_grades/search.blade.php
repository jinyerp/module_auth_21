{{-- 회원 등급 검색 필터 --}}
<div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
    <div class="flex flex-wrap items-center justify-between">
        <div class="flex items-center space-x-4">
            {{-- 검색 입력 --}}
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <input type="text" 
                       wire:model.debounce.300ms="search"
                       class="block w-64 pl-10 pr-3 py-2 text-xs border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="등급명, 코드, 설명 검색...">
            </div>
            
            {{-- 상태 필터 --}}
            <select wire:model="filters.is_active"
                    class="block w-32 px-3 py-2 text-xs border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                <option value="">전체 상태</option>
                <option value="1">활성</option>
                <option value="0">비활성</option>
            </select>
            
            {{-- 레벨 범위 필터 --}}
            <div class="flex items-center space-x-2">
                <span class="text-xs text-gray-600">레벨:</span>
                <input type="number" 
                       wire:model.lazy="filters.level_min"
                       class="block w-16 px-2 py-2 text-xs border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="최소"
                       min="1">
                <span class="text-xs text-gray-500">~</span>
                <input type="number" 
                       wire:model.lazy="filters.level_max"
                       class="block w-16 px-2 py-2 text-xs border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="최대"
                       min="1">
            </div>
            
            {{-- 필터 초기화 --}}
            @if($search || !empty(array_filter($filters)))
            <button wire:click="resetFilters"
                    class="text-xs text-gray-600 hover:text-gray-900">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
                필터 초기화
            </button>
            @endif
        </div>
        
        {{-- 정렬 및 페이지당 표시 --}}
        <div class="flex items-center space-x-2 mt-2 sm:mt-0">
            <span class="text-xs text-gray-600">표시:</span>
            <select wire:model="perPage"
                    class="block w-20 px-2 py-1.5 text-xs border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                <option value="10">10</option>
                <option value="20">20</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
            <span class="text-xs text-gray-600">개</span>
        </div>
    </div>
    
    {{-- 적용된 필터 표시 --}}
    @if($search || !empty(array_filter($filters)))
    <div class="mt-2 flex flex-wrap gap-2">
        @if($search)
        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
            검색: {{ $search }}
            <button wire:click="$set('search', '')" class="ml-1">
                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </span>
        @endif
        
        @if(isset($filters['is_active']) && $filters['is_active'] !== '')
        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
            상태: {{ $filters['is_active'] == '1' ? '활성' : '비활성' }}
            <button wire:click="$set('filters.is_active', '')" class="ml-1">
                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </span>
        @endif
        
        @if(isset($filters['level_min']) && $filters['level_min'])
        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
            최소 레벨: {{ $filters['level_min'] }}
            <button wire:click="$set('filters.level_min', '')" class="ml-1">
                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </span>
        @endif
        
        @if(isset($filters['level_max']) && $filters['level_max'])
        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
            최대 레벨: {{ $filters['level_max'] }}
            <button wire:click="$set('filters.level_max', '')" class="ml-1">
                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </span>
        @endif
    </div>
    @endif
</div>