{{-- 검색 폼 --}}
<div class="bg-white p-4 rounded-lg shadow-sm">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        
        {{-- 텍스트 검색 --}}
        <div>
            <label for="search" class="block text-xs font-medium text-gray-700 mb-1">
                검색
            </label>
            <input type="text" 
                   wire:model.live.debounce.300ms="search"
                   id="search"
                   class="w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                   placeholder="액션, 설명, IP 주소로 검색...">
        </div>

        {{-- 회원 ID 필터 --}}
        <div>
            <label for="filter_account_id" class="block text-xs font-medium text-gray-700 mb-1">
                회원 ID
            </label>
            <input type="number" 
                   wire:model.live="filters.account_id"
                   id="filter_account_id"
                   class="w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                   placeholder="회원 ID 입력">
        </div>

        {{-- 활동 유형 필터 --}}
        <div>
            <label for="filter_action" class="block text-xs font-medium text-gray-700 mb-1">
                활동 유형
            </label>
            <select wire:model.live="filters.action"
                    id="filter_action"
                    class="w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                <option value="">전체 활동</option>
                <option value="login">로그인</option>
                <option value="logout">로그아웃</option>
                <option value="login_failed">로그인 실패</option>
                <option value="password_reset">비밀번호 재설정</option>
                <option value="email_change">이메일 변경</option>
                <option value="profile_update">프로필 수정</option>
                <option value="account_created">계정 생성</option>
                <option value="account_deleted">계정 삭제</option>
                <option value="permission_changed">권한 변경</option>
            </select>
        </div>

        {{-- 상태 필터 --}}
        <div>
            <label for="filter_status" class="block text-xs font-medium text-gray-700 mb-1">
                상태
            </label>
            <select wire:model.live="filters.status"
                    id="filter_status"
                    class="w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                <option value="">전체</option>
                <option value="success">성공</option>
                <option value="failed">실패</option>
                <option value="pending">대기</option>
            </select>
        </div>

        {{-- IP 주소 필터 --}}
        <div>
            <label for="filter_ip" class="block text-xs font-medium text-gray-700 mb-1">
                IP 주소
            </label>
            <input type="text" 
                   wire:model.live.debounce.300ms="filters.ip_address"
                   id="filter_ip"
                   class="w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                   placeholder="IP 주소 입력">
        </div>

        {{-- 시작 날짜 --}}
        <div>
            <label for="filter_date_from" class="block text-xs font-medium text-gray-700 mb-1">
                시작 날짜
            </label>
            <input type="datetime-local" 
                   wire:model.live="filters.date_from"
                   id="filter_date_from"
                   class="w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
        </div>

        {{-- 종료 날짜 --}}
        <div>
            <label for="filter_date_to" class="block text-xs font-medium text-gray-700 mb-1">
                종료 날짜
            </label>
            <input type="datetime-local" 
                   wire:model.live="filters.date_to"
                   id="filter_date_to"
                   class="w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
        </div>

        {{-- 의심스러운 활동만 --}}
        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">
                특수 필터
            </label>
            <div class="flex items-center space-x-4">
                <label class="inline-flex items-center">
                    <input type="checkbox" 
                           wire:model.live="filters.suspicious_only"
                           class="h-3.5 w-3.5 text-blue-600 focus:ring-1 focus:ring-blue-500 border-gray-200 rounded">
                    <span class="ml-2 text-xs text-gray-700">의심스러운 활동만</span>
                </label>
                
                <label class="inline-flex items-center">
                    <input type="checkbox" 
                           wire:model.live="filters.failed_only"
                           class="h-3.5 w-3.5 text-blue-600 focus:ring-1 focus:ring-blue-500 border-gray-200 rounded">
                    <span class="ml-2 text-xs text-gray-700">실패한 활동만</span>
                </label>
            </div>
        </div>
    </div>

    {{-- 검색 액션 버튼들 --}}
    <div class="mt-4 flex justify-between items-center">
        <div class="flex space-x-2">
            {{-- 필터 초기화 버튼 --}}
            <button wire:click="resetFilters" 
                    type="button"
                    class="px-3 py-1.5 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                초기화
            </button>

            {{-- 내보내기 버튼 --}}
            @if($jsonData['index']['features']['enableExport'] ?? false)
                <button wire:click="export" 
                        type="button"
                        class="px-3 py-1.5 text-xs font-medium text-white bg-green-600 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    내보내기
                </button>
            @endif
        </div>

        {{-- 검색 결과 요약 --}}
        <div class="text-xs text-gray-600">
            @if($search || collect($filters)->filter()->isNotEmpty())
                <span>필터 적용 중</span>
                <span class="mx-1">•</span>
            @endif
            <span>총 {{ $rows->total() ?? 0 }}개 항목</span>
        </div>
    </div>

    {{-- 빠른 필터 태그 --}}
    @if($search || collect($filters)->filter()->isNotEmpty())
        <div class="mt-3 flex flex-wrap gap-2">
            @if($search)
                <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-blue-100 text-blue-700">
                    검색: {{ $search }}
                    <button wire:click="$set('search', '')" class="ml-1 text-blue-500 hover:text-blue-700">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </span>
            @endif

            @if(isset($filters['account_id']) && $filters['account_id'])
                <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-gray-100 text-gray-700">
                    회원 ID: {{ $filters['account_id'] }}
                    <button wire:click="$set('filters.account_id', '')" class="ml-1 text-gray-500 hover:text-gray-700">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </span>
            @endif

            @if(isset($filters['action']) && $filters['action'])
                <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-purple-100 text-purple-700">
                    활동: {{ $filters['action'] }}
                    <button wire:click="$set('filters.action', '')" class="ml-1 text-purple-500 hover:text-purple-700">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </span>
            @endif

            @if(isset($filters['status']) && $filters['status'])
                <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-yellow-100 text-yellow-700">
                    상태: {{ $filters['status'] }}
                    <button wire:click="$set('filters.status', '')" class="ml-1 text-yellow-500 hover:text-yellow-700">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </span>
            @endif

            @if(isset($filters['ip_address']) && $filters['ip_address'])
                <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-green-100 text-green-700">
                    IP: {{ $filters['ip_address'] }}
                    <button wire:click="$set('filters.ip_address', '')" class="ml-1 text-green-500 hover:text-green-700">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </span>
            @endif

            @if(isset($filters['suspicious_only']) && $filters['suspicious_only'])
                <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-red-100 text-red-700">
                    의심스러운 활동
                    <button wire:click="$set('filters.suspicious_only', false)" class="ml-1 text-red-500 hover:text-red-700">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </span>
            @endif
        </div>
    @endif
</div>