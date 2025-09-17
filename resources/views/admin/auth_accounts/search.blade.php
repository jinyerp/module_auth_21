{{--
    AuthAccounts 검색 폼 뷰
    Tailwind CSS 스타일 적용 및 Livewire 기능 통합
--}}
<div class="bg-white p-4 rounded-lg shadow-sm">
    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">
        {{-- 검색어 입력 --}}
        <div class="md:col-span-2 lg:col-span-2">
            <label for="search" class="block text-xs font-medium text-gray-700 mb-1">
                검색
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <input type="text"
                       wire:model.live.debounce.300ms="search"
                       id="search"
                       class="block w-full pl-10 pr-3 py-2 text-xs border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="이름, 이메일, 전화번호로 검색...">
            </div>
        </div>

        {{-- 상태 필터 --}}
        <div>
            <label for="filter_status" class="block text-xs font-medium text-gray-700 mb-1">
                상태
            </label>
            <select wire:model.live="filters.status"
                    id="filter_status"
                    class="block w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                <option value="">전체</option>
                <option value="active">활성</option>
                <option value="inactive">비활성</option>
                <option value="suspended">정지</option>
            </select>
        </div>

        {{-- 등급 필터 --}}
        <div>
            <label for="filter_grade" class="block text-xs font-medium text-gray-700 mb-1">
                회원 등급
            </label>
            <select wire:model.live="filters.grade_id"
                    id="filter_grade"
                    class="block w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                <option value="">전체</option>
                @if(isset($grades))
                    @foreach($grades as $grade)
                        <option value="{{ $grade->id }}">{{ $grade->name }}</option>
                    @endforeach
                @endif
            </select>
        </div>

        {{-- 국가 필터 --}}
        <div>
            <label for="filter_country" class="block text-xs font-medium text-gray-700 mb-1">
                국가
            </label>
            <select wire:model.live="filters.country_id"
                    id="filter_country"
                    class="block w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                <option value="">전체</option>
                @if(isset($countries))
                    @foreach($countries as $country)
                        <option value="{{ $country->id }}">{{ $country->name }}</option>
                    @endforeach
                @endif
            </select>
        </div>
    </div>

    {{-- 고급 검색 옵션 (확장 가능) --}}
    <div x-data="{ showAdvanced: false }" class="mt-4">
        <button @click="showAdvanced = !showAdvanced"
                type="button"
                class="text-xs text-gray-600 hover:text-gray-900 font-medium focus:outline-none">
            <span x-show="!showAdvanced" class="flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
                고급 검색 옵션
            </span>
            <span x-show="showAdvanced" class="flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                </svg>
                고급 검색 옵션 닫기
            </span>
        </button>

        <div x-show="showAdvanced" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 transform scale-95"
             x-transition:enter-end="opacity-100 transform scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 transform scale-100"
             x-transition:leave-end="opacity-0 transform scale-95"
             class="mt-4 pt-4 border-t border-gray-200">
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                {{-- 이메일 인증 필터 --}}
                <div>
                    <label for="filter_verified" class="block text-xs font-medium text-gray-700 mb-1">
                        이메일 인증
                    </label>
                    <select wire:model.live="filters.email_verified"
                            id="filter_verified"
                            class="block w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">전체</option>
                        <option value="1">인증완료</option>
                        <option value="0">미인증</option>
                    </select>
                </div>

                {{-- 가입일 범위 --}}
                <div>
                    <label for="filter_date_from" class="block text-xs font-medium text-gray-700 mb-1">
                        가입일 (시작)
                    </label>
                    <input type="date"
                           wire:model.live="filters.created_from"
                           id="filter_date_from"
                           class="block w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div>
                    <label for="filter_date_to" class="block text-xs font-medium text-gray-700 mb-1">
                        가입일 (종료)
                    </label>
                    <input type="date"
                           wire:model.live="filters.created_to"
                           id="filter_date_to"
                           class="block w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                </div>

                {{-- 마지막 로그인 --}}
                <div>
                    <label for="filter_last_login" class="block text-xs font-medium text-gray-700 mb-1">
                        마지막 로그인
                    </label>
                    <select wire:model.live="filters.last_login"
                            id="filter_last_login"
                            class="block w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">전체</option>
                        <option value="today">오늘</option>
                        <option value="week">최근 1주일</option>
                        <option value="month">최근 1개월</option>
                        <option value="3months">최근 3개월</option>
                        <option value="never">로그인 없음</option>
                    </select>
                </div>

                {{-- 2단계 인증 --}}
                <div>
                    <label for="filter_2fa" class="block text-xs font-medium text-gray-700 mb-1">
                        2단계 인증
                    </label>
                    <select wire:model.live="filters.two_factor"
                            id="filter_2fa"
                            class="block w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">전체</option>
                        <option value="1">활성화</option>
                        <option value="0">비활성화</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- 필터 초기화 버튼 --}}
    <div class="mt-4 flex items-center justify-between">
        <div class="text-xs text-gray-500">
            @if($search || collect($filters)->filter()->isNotEmpty())
                검색 결과: <span class="font-medium">{{ $totalResults ?? 0 }}</span>개
            @endif
        </div>
        
        @if($search || collect($filters)->filter()->isNotEmpty())
            <button wire:click="resetFilters"
                    type="button"
                    class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <svg class="w-3 h-3 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                필터 초기화
            </button>
        @endif
    </div>
</div>