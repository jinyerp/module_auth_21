{{-- 회원 등급 상세 정보 --}}
<div class="bg-white shadow overflow-hidden sm:rounded-lg">
    {{-- 헤더 섹션 --}}
    <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                @if($data->color)
                <div class="w-4 h-4 rounded-full mr-3" style="background-color: {{ $data->color }};"></div>
                @endif
                <div>
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        {{ $data->name }}
                        <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                            Level {{ $data->level }}
                        </span>
                        @if($data->is_active)
                        <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                            활성
                        </span>
                        @else
                        <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                            비활성
                        </span>
                        @endif
                    </h3>
                    <p class="mt-1 text-xs text-gray-500">
                        코드: {{ $data->code }}
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    {{-- 기본 정보 섹션 --}}
    <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
        <h4 class="text-sm font-medium text-gray-900 mb-4">기본 정보</h4>
        <dl class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-2">
            <div>
                <dt class="text-xs font-medium text-gray-500">등급명</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $data->name }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium text-gray-500">등급 코드</dt>
                <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $data->code }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium text-gray-500">레벨</dt>
                <dd class="mt-1 text-sm text-gray-900">
                    <span class="inline-flex items-center justify-center w-8 h-8 text-xs font-bold text-white bg-gradient-to-r from-blue-500 to-blue-600 rounded-full">
                        {{ $data->level }}
                    </span>
                </dd>
            </div>
            <div>
                <dt class="text-xs font-medium text-gray-500">상태</dt>
                <dd class="mt-1 text-sm">
                    @if($data->is_active)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            활성
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                            비활성
                        </span>
                    @endif
                </dd>
            </div>
            @if($data->description)
            <div class="sm:col-span-2">
                <dt class="text-xs font-medium text-gray-500">설명</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $data->description }}</dd>
            </div>
            @endif
        </dl>
    </div>
    
    {{-- 혜택 정보 섹션 --}}
    <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
        <h4 class="text-sm font-medium text-gray-900 mb-4">혜택 정보</h4>
        <dl class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-2">
            <div>
                <dt class="text-xs font-medium text-gray-500">포인트 적립률</dt>
                <dd class="mt-1 text-sm text-gray-900">
                    <span class="text-green-600 font-semibold">{{ number_format($data->point_rate * 100, 1) }}%</span>
                </dd>
            </div>
            <div>
                <dt class="text-xs font-medium text-gray-500">할인율</dt>
                <dd class="mt-1 text-sm text-gray-900">
                    <span class="text-red-600 font-semibold">{{ number_format($data->discount_rate, 0) }}%</span>
                </dd>
            </div>
            <div>
                <dt class="text-xs font-medium text-gray-500">최소 구매금액</dt>
                <dd class="mt-1 text-sm text-gray-900">
                    <span class="font-semibold">₩{{ number_format($data->min_purchase) }}</span>
                </dd>
            </div>
            @if($data->benefits)
            <div class="sm:col-span-2">
                <dt class="text-xs font-medium text-gray-500 mb-2">추가 혜택</dt>
                <dd class="mt-1">
                    @php
                        $benefits = is_string($data->benefits) ? json_decode($data->benefits, true) : $data->benefits;
                    @endphp
                    @if($benefits && is_array($benefits))
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                        @if(!empty($benefits['free_shipping']))
                        <div class="flex items-center">
                            <svg class="h-4 w-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="text-xs text-gray-900">무료 배송</span>
                        </div>
                        @endif
                        @if(!empty($benefits['birthday_coupon']))
                        <div class="flex items-center">
                            <svg class="h-4 w-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="text-xs text-gray-900">생일 쿠폰</span>
                        </div>
                        @endif
                        @if(!empty($benefits['exclusive_sale']))
                        <div class="flex items-center">
                            <svg class="h-4 w-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="text-xs text-gray-900">전용 세일</span>
                        </div>
                        @endif
                        @if(!empty($benefits['priority_support']))
                        <div class="flex items-center">
                            <svg class="h-4 w-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="text-xs text-gray-900">우선 고객지원</span>
                        </div>
                        @endif
                        @if(!empty($benefits['early_access']))
                        <div class="flex items-center">
                            <svg class="h-4 w-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="text-xs text-gray-900">신제품 우선 구매</span>
                        </div>
                        @endif
                    </div>
                    @else
                    <span class="text-xs text-gray-500">추가 혜택 없음</span>
                    @endif
                </dd>
            </div>
            @endif
        </dl>
    </div>
    
    {{-- 시각적 설정 섹션 --}}
    <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
        <h4 class="text-sm font-medium text-gray-900 mb-4">시각적 설정</h4>
        <dl class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-2">
            <div>
                <dt class="text-xs font-medium text-gray-500">등급 색상</dt>
                <dd class="mt-1 flex items-center">
                    @if($data->color)
                    <div class="w-8 h-8 rounded border border-gray-300 mr-2" 
                         style="background-color: {{ $data->color }};"></div>
                    <span class="text-sm text-gray-900 font-mono">{{ $data->color }}</span>
                    @else
                    <span class="text-sm text-gray-500">설정되지 않음</span>
                    @endif
                </dd>
            </div>
            <div>
                <dt class="text-xs font-medium text-gray-500">아이콘</dt>
                <dd class="mt-1 text-sm text-gray-900">
                    @if($data->icon)
                    {{ $data->icon }}
                    @else
                    <span class="text-gray-500">설정되지 않음</span>
                    @endif
                </dd>
            </div>
        </dl>
    </div>
    
    {{-- 통계 정보 섹션 --}}
    @if(isset($data->user_count) || isset($data->active_user_count))
    <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
        <h4 class="text-sm font-medium text-gray-900 mb-4">통계 정보</h4>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            @if(isset($data->user_count))
            <div class="bg-gray-50 px-4 py-3 rounded-lg">
                <dt class="text-xs font-medium text-gray-500">전체 회원 수</dt>
                <dd class="mt-1 text-2xl font-semibold text-gray-900">{{ number_format($data->user_count) }}</dd>
            </div>
            @endif
            @if(isset($data->active_user_count))
            <div class="bg-gray-50 px-4 py-3 rounded-lg">
                <dt class="text-xs font-medium text-gray-500">활성 회원 수</dt>
                <dd class="mt-1 text-2xl font-semibold text-gray-900">{{ number_format($data->active_user_count) }}</dd>
            </div>
            @endif
            @if(isset($data->statistics['avg_purchase']))
            <div class="bg-gray-50 px-4 py-3 rounded-lg">
                <dt class="text-xs font-medium text-gray-500">평균 구매금액</dt>
                <dd class="mt-1 text-2xl font-semibold text-gray-900">₩{{ number_format($data->statistics['avg_purchase']) }}</dd>
            </div>
            @endif
        </div>
    </div>
    @endif
    
    {{-- 승급 규칙 섹션 --}}
    @if(isset($data->next_grade) || isset($data->prev_grade))
    <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
        <h4 class="text-sm font-medium text-gray-900 mb-4">등급 체계</h4>
        <div class="flex items-center justify-between">
            <div class="flex-1">
                @if(isset($data->prev_grade))
                <div class="text-xs text-gray-500">이전 등급</div>
                <div class="mt-1 text-sm text-gray-900">
                    {{ $data->prev_grade->name }} (Level {{ $data->prev_grade->level }})
                </div>
                @else
                <div class="text-xs text-gray-500">이전 등급</div>
                <div class="mt-1 text-sm text-gray-400">없음 (최하위 등급)</div>
                @endif
            </div>
            <div class="px-4">
                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </div>
            <div class="flex-1 text-center">
                <div class="text-xs text-gray-500">현재 등급</div>
                <div class="mt-1 text-sm font-semibold text-gray-900">
                    {{ $data->name }} (Level {{ $data->level }})
                </div>
            </div>
            <div class="px-4">
                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </div>
            <div class="flex-1 text-right">
                @if(isset($data->next_grade))
                <div class="text-xs text-gray-500">다음 등급</div>
                <div class="mt-1 text-sm text-gray-900">
                    {{ $data->next_grade->name }} (Level {{ $data->next_grade->level }})
                    <div class="text-xs text-gray-500 mt-1">
                        승급 조건: ₩{{ number_format($data->next_grade->min_purchase) }} 이상
                    </div>
                </div>
                @else
                <div class="text-xs text-gray-500">다음 등급</div>
                <div class="mt-1 text-sm text-gray-400">없음 (최고 등급)</div>
                @endif
            </div>
        </div>
    </div>
    @endif
    
    {{-- 메타 정보 섹션 --}}
    <div class="px-4 py-5 sm:px-6">
        <h4 class="text-sm font-medium text-gray-900 mb-4">메타 정보</h4>
        <dl class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-2">
            <div>
                <dt class="text-xs font-medium text-gray-500">생성일</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $data->created_at->format('Y-m-d H:i:s') }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium text-gray-500">최종 수정일</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $data->updated_at->format('Y-m-d H:i:s') }}</dd>
            </div>
        </dl>
    </div>
</div>