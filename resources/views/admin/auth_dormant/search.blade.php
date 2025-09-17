<div class="bg-white px-4 py-3 border-b border-gray-200">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
        {{-- 검색 입력 --}}
        <div>
            <input type="text" 
                   wire:model.debounce.300ms="search"
                   placeholder="이메일, 이름, 사유로 검색..."
                   class="w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
        </div>
        
        {{-- 상태 필터 --}}
        <div>
            <select wire:model="filters.status" 
                    class="w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                <option value="">전체 상태</option>
                <option value="dormant">휴면</option>
                <option value="notified">알림발송</option>
                <option value="reactivated">재활성화</option>
                <option value="deleted">삭제됨</option>
            </select>
        </div>
        
        {{-- 알림 상태 필터 --}}
        <div>
            <select wire:model="filters.notification_status" 
                    class="w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                <option value="">알림 상태</option>
                <option value="not_sent">미발송</option>
                <option value="sent">발송완료</option>
            </select>
        </div>
        
        {{-- 삭제 예정 필터 --}}
        <div>
            <select wire:model="filters.deletion_status" 
                    class="w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                <option value="">삭제 예정</option>
                <option value="scheduled">예정됨</option>
                <option value="imminent">임박(30일 이내)</option>
                <option value="overdue">기한 초과</option>
            </select>
        </div>
    </div>
    
    {{-- 날짜 범위 필터 --}}
    <div class="mt-3 grid grid-cols-1 md:grid-cols-3 gap-3">
        <div>
            <label class="block text-xs text-gray-600 mb-1">휴면 처리일 시작</label>
            <input type="date" 
                   wire:model="filters.date_from"
                   class="w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
        </div>
        
        <div>
            <label class="block text-xs text-gray-600 mb-1">휴면 처리일 종료</label>
            <input type="date" 
                   wire:model="filters.date_to"
                   class="w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
        </div>
        
        <div class="flex items-end space-x-2">
            <button wire:click="applyFilters" 
                    class="px-4 py-2 text-xs font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                필터 적용
            </button>
            <button wire:click="resetFilters" 
                    class="px-4 py-2 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                초기화
            </button>
        </div>
    </div>
    
    {{-- 활성 필터 표시 --}}
    @if($search || count(array_filter($filters ?? [])) > 0)
    <div class="mt-3 flex flex-wrap gap-2">
        @if($search)
        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
            검색: {{ $search }}
            <button wire:click="$set('search', '')" class="ml-1 text-blue-600 hover:text-blue-800">
                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
            </button>
        </span>
        @endif
        
        @if(isset($filters['status']) && $filters['status'])
        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
            상태: {{ ['dormant' => '휴면', 'notified' => '알림발송', 'reactivated' => '재활성화', 'deleted' => '삭제됨'][$filters['status']] }}
            <button wire:click="$set('filters.status', '')" class="ml-1 text-gray-600 hover:text-gray-800">
                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
            </button>
        </span>
        @endif
        
        @if(isset($filters['date_from']) && $filters['date_from'])
        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
            시작: {{ $filters['date_from'] }}
            <button wire:click="$set('filters.date_from', '')" class="ml-1 text-green-600 hover:text-green-800">
                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
            </button>
        </span>
        @endif
        
        @if(isset($filters['date_to']) && $filters['date_to'])
        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
            종료: {{ $filters['date_to'] }}
            <button wire:click="$set('filters.date_to', '')" class="ml-1 text-green-600 hover:text-green-800">
                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
            </button>
        </span>
        @endif
    </div>
    @endif
</div>