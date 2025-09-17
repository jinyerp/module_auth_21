{{-- 검색 및 필터 UI --}}
<div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        {{-- 검색어 입력 --}}
        <div class="md:col-span-2">
            <label for="search" class="block text-xs font-medium text-gray-700 mb-1">
                검색
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <input type="text" 
                       id="search"
                       wire:model.debounce.300ms="search"
                       class="block w-full pl-10 pr-3 py-2 text-xs border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="역할명, 슬러그, 설명 검색...">
            </div>
        </div>
        
        {{-- 상태 필터 --}}
        <div>
            <label for="filter_status" class="block text-xs font-medium text-gray-700 mb-1">
                상태
            </label>
            <select id="filter_status"
                    wire:model="filters.is_active"
                    class="block w-full px-3 py-2 text-xs border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                <option value="">전체</option>
                <option value="1">활성</option>
                <option value="0">비활성</option>
            </select>
        </div>
        
        {{-- 타입 필터 --}}
        <div>
            <label for="filter_type" class="block text-xs font-medium text-gray-700 mb-1">
                타입
            </label>
            <select id="filter_type"
                    wire:model="filters.type"
                    class="block w-full px-3 py-2 text-xs border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                <option value="">전체</option>
                <option value="system">시스템 역할</option>
                <option value="custom">커스텀 역할</option>
            </select>
        </div>
    </div>
    
    {{-- 고급 필터 (접을 수 있는 영역) --}}
    <details class="mt-4">
        <summary class="text-xs text-blue-600 cursor-pointer hover:text-blue-800 font-medium">
            고급 필터 옵션
        </summary>
        
        <div class="mt-4 pt-4 border-t border-gray-200">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                {{-- 권한 포함 필터 --}}
                <div>
                    <label for="filter_has_permission" class="block text-xs font-medium text-gray-700 mb-1">
                        권한 포함
                    </label>
                    <select id="filter_has_permission"
                            wire:model="filters.has_permission"
                            class="block w-full px-3 py-2 text-xs border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">전체</option>
                        <option value="users.view">사용자 보기</option>
                        <option value="users.create">사용자 생성</option>
                        <option value="users.edit">사용자 수정</option>
                        <option value="users.delete">사용자 삭제</option>
                        <option value="roles.view">역할 보기</option>
                        <option value="roles.create">역할 생성</option>
                        <option value="roles.edit">역할 수정</option>
                        <option value="roles.delete">역할 삭제</option>
                        <option value="settings.view">설정 보기</option>
                        <option value="settings.edit">설정 수정</option>
                    </select>
                </div>
                
                {{-- 생성일 범위 --}}
                <div>
                    <label for="filter_created_from" class="block text-xs font-medium text-gray-700 mb-1">
                        생성일 (시작)
                    </label>
                    <input type="date" 
                           id="filter_created_from"
                           wire:model="filters.created_from"
                           class="block w-full px-3 py-2 text-xs border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <div>
                    <label for="filter_created_to" class="block text-xs font-medium text-gray-700 mb-1">
                        생성일 (종료)
                    </label>
                    <input type="date" 
                           id="filter_created_to"
                           wire:model="filters.created_to"
                           class="block w-full px-3 py-2 text-xs border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                {{-- 사용자 수 범위 --}}
                <div>
                    <label for="filter_min_users" class="block text-xs font-medium text-gray-700 mb-1">
                        최소 사용자 수
                    </label>
                    <input type="number" 
                           id="filter_min_users"
                           wire:model="filters.min_users"
                           min="0"
                           class="block w-full px-3 py-2 text-xs border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="0">
                </div>
                
                <div>
                    <label for="filter_max_users" class="block text-xs font-medium text-gray-700 mb-1">
                        최대 사용자 수
                    </label>
                    <input type="number" 
                           id="filter_max_users"
                           wire:model="filters.max_users"
                           min="0"
                           class="block w-full px-3 py-2 text-xs border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="무제한">
                </div>
                
                {{-- 정렬 옵션 --}}
                <div>
                    <label for="sort_by" class="block text-xs font-medium text-gray-700 mb-1">
                        정렬 기준
                    </label>
                    <select id="sort_by"
                            wire:model="sortField"
                            class="block w-full px-3 py-2 text-xs border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                        <option value="name">역할명</option>
                        <option value="slug">슬러그</option>
                        <option value="created_at">생성일</option>
                        <option value="updated_at">수정일</option>
                        <option value="user_count">사용자 수</option>
                    </select>
                </div>
            </div>
            
            {{-- 필터 액션 버튼 --}}
            <div class="mt-4 flex justify-end space-x-2">
                <button wire:click="resetFilters"
                        class="px-3 py-1.5 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="inline-block w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    필터 초기화
                </button>
                
                <button wire:click="applyFilters"
                        class="px-3 py-1.5 text-xs font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="inline-block w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                    </svg>
                    필터 적용
                </button>
            </div>
        </div>
    </details>
    
    {{-- 현재 적용된 필터 표시 --}}
    @if($search || !empty(array_filter($filters ?? [])))
        <div class="mt-4 pt-4 border-t border-gray-200">
            <div class="flex items-center">
                <span class="text-xs text-gray-500 mr-2">적용된 필터:</span>
                <div class="flex flex-wrap gap-2">
                    @if($search)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            검색: {{ $search }}
                            <button wire:click="$set('search', '')" class="ml-1 text-blue-600 hover:text-blue-800">
                                <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                </svg>
                            </button>
                        </span>
                    @endif
                    
                    @if(isset($filters['is_active']) && $filters['is_active'] !== '')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            상태: {{ $filters['is_active'] ? '활성' : '비활성' }}
                            <button wire:click="$set('filters.is_active', '')" class="ml-1 text-green-600 hover:text-green-800">
                                <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                </svg>
                            </button>
                        </span>
                    @endif
                    
                    @if(isset($filters['type']) && $filters['type'])
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                            타입: {{ $filters['type'] === 'system' ? '시스템' : '커스텀' }}
                            <button wire:click="$set('filters.type', '')" class="ml-1 text-purple-600 hover:text-purple-800">
                                <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                </svg>
                            </button>
                        </span>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>