{{-- 순수한 데이터 표시 내용만 작성, 액션 버튼 없음 --}}
<div class="bg-white shadow overflow-hidden sm:rounded-lg">
    {{-- 국가 헤더 정보 --}}
    <div class="px-4 py-5 sm:px-6 bg-gray-50">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                @if($data->flag_emoji)
                    <span class="text-4xl">{{ $data->flag_emoji }}</span>
                @endif
                <div>
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        {{ $data->name }}
                        @if($data->native_name && $data->native_name !== $data->name)
                            <span class="text-sm text-gray-500">({{ $data->native_name }})</span>
                        @endif
                    </h3>
                    <p class="mt-1 text-sm text-gray-500">
                        {{ $data->code }} / {{ $data->code3 }}
                        @if($data->numeric_code)
                            / {{ $data->numeric_code }}
                        @endif
                    </p>
                </div>
            </div>
            <div class="flex items-center space-x-2">
                @if($data->is_active)
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                        활성
                    </span>
                @else
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                        비활성
                    </span>
                @endif
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                    순서: {{ $data->display_order }}
                </span>
            </div>
        </div>
    </div>

    {{-- 탭 네비게이션 --}}
    <div class="border-b border-gray-200">
        <nav class="-mb-px flex" aria-label="Tabs">
            <button wire:click="$set('activeTab', 'overview')"
                    class="@if($activeTab === 'overview') border-blue-500 text-blue-600 @else border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 @endif whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm">
                개요
            </button>
            <button wire:click="$set('activeTab', 'users')"
                    class="@if($activeTab === 'users') border-blue-500 text-blue-600 @else border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 @endif whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm">
                사용자
                @if(isset($data->statistics['total_users']) && $data->statistics['total_users'] > 0)
                    <span class="ml-2 bg-gray-100 text-gray-600 py-0.5 px-2 rounded-full text-xs">
                        {{ number_format($data->statistics['total_users']) }}
                    </span>
                @endif
            </button>
            <button wire:click="$set('activeTab', 'related')"
                    class="@if($activeTab === 'related') border-blue-500 text-blue-600 @else border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 @endif whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm">
                관련 국가
            </button>
            <button wire:click="$set('activeTab', 'activity')"
                    class="@if($activeTab === 'activity') border-blue-500 text-blue-600 @else border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 @endif whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm">
                활동 로그
            </button>
        </nav>
    </div>

    {{-- 탭 컨텐츠 --}}
    <div class="px-4 py-5 sm:p-6">
        {{-- 개요 탭 --}}
        @if($activeTab === 'overview')
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            {{-- 기본 정보 --}}
            <div>
                <h4 class="text-sm font-medium text-gray-900 mb-4">기본 정보</h4>
                <dl class="grid grid-cols-1 gap-x-4 gap-y-3">
                    <div>
                        <dt class="text-xs font-medium text-gray-500">국가 코드</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                {{ $data->code }}
                            </span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 ml-1">
                                {{ $data->code3 }}
                            </span>
                            @if($data->numeric_code)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 ml-1">
                                    {{ $data->numeric_code }}
                                </span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500">국가명</dt>
                        <dd class="mt-1 text-sm text-gray-900 font-medium">{{ $data->name }}</dd>
                    </div>
                    @if($data->native_name)
                    <div>
                        <dt class="text-xs font-medium text-gray-500">현지 국가명</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $data->native_name }}</dd>
                    </div>
                    @endif
                </dl>
            </div>

            {{-- 지리 정보 --}}
            <div>
                <h4 class="text-sm font-medium text-gray-900 mb-4">지리 정보</h4>
                <dl class="grid grid-cols-1 gap-x-4 gap-y-3">
                    @if($data->capital)
                    <div>
                        <dt class="text-xs font-medium text-gray-500">수도</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $data->capital }}</dd>
                    </div>
                    @endif
                    @if($data->region)
                    <div>
                        <dt class="text-xs font-medium text-gray-500">대륙</dt>
                        <dd class="mt-1">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium 
                                   @switch($data->region)
                                       @case('Asia') bg-yellow-100 text-yellow-800 @break
                                       @case('Europe') bg-blue-100 text-blue-800 @break
                                       @case('Americas') bg-green-100 text-green-800 @break
                                       @case('Africa') bg-orange-100 text-orange-800 @break
                                       @case('Oceania') bg-purple-100 text-purple-800 @break
                                       @default bg-gray-100 text-gray-800
                                   @endswitch">
                                {{ $data->region }}
                            </span>
                        </dd>
                    </div>
                    @endif
                    @if($data->subregion)
                    <div>
                        <dt class="text-xs font-medium text-gray-500">하위 지역</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $data->subregion }}</dd>
                    </div>
                    @endif
                    @if($data->latitude && $data->longitude)
                    <div>
                        <dt class="text-xs font-medium text-gray-500">좌표</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ number_format($data->latitude, 6) }}, {{ number_format($data->longitude, 6) }}
                        </dd>
                    </div>
                    @endif
                </dl>
            </div>

            {{-- 경제 정보 --}}
            <div>
                <h4 class="text-sm font-medium text-gray-900 mb-4">경제 정보</h4>
                <dl class="grid grid-cols-1 gap-x-4 gap-y-3">
                    @if($data->currency_display ?? null)
                    <div>
                        <dt class="text-xs font-medium text-gray-500">통화</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $data->currency_display }}</dd>
                    </div>
                    @elseif($data->currency_code)
                    <div>
                        <dt class="text-xs font-medium text-gray-500">통화</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $data->currency_name ?? $data->currency_code }}
                            ({{ $data->currency_code }})
                            @if($data->currency_symbol)
                                {{ $data->currency_symbol }}
                            @endif
                        </dd>
                    </div>
                    @endif
                    @if($data->phone_code_display ?? $data->phone_code)
                    <div>
                        <dt class="text-xs font-medium text-gray-500">국가번호</dt>
                        <dd class="mt-1">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                +{{ $data->phone_code }}
                            </span>
                        </dd>
                    </div>
                    @endif
                </dl>
            </div>

            {{-- 문화 정보 --}}
            <div>
                <h4 class="text-sm font-medium text-gray-900 mb-4">문화 정보</h4>
                <dl class="grid grid-cols-1 gap-x-4 gap-y-3">
                    @if($data->languages_display ?? $data->languages)
                    <div>
                        <dt class="text-xs font-medium text-gray-500">언어</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $data->languages_display ?? (is_array($data->languages) ? implode(', ', $data->languages) : $data->languages) }}
                        </dd>
                    </div>
                    @endif
                    @if($data->timezone)
                    <div>
                        <dt class="text-xs font-medium text-gray-500">시간대</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $data->timezone }}</dd>
                    </div>
                    @endif
                    @if($data->timezones_display ?? $data->timezones)
                    <div>
                        <dt class="text-xs font-medium text-gray-500">모든 시간대</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $data->timezones_display ?? (is_array($data->timezones) ? implode(', ', $data->timezones) : $data->timezones) }}
                        </dd>
                    </div>
                    @endif
                </dl>
            </div>

            {{-- 통계 정보 --}}
            @if(isset($data->statistics))
            <div class="lg:col-span-2">
                <h4 class="text-sm font-medium text-gray-900 mb-4">사용자 통계</h4>
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                    <div class="bg-gray-50 rounded-lg p-3">
                        <dt class="text-xs font-medium text-gray-500">전체 사용자</dt>
                        <dd class="mt-1 text-2xl font-semibold text-gray-900">
                            {{ number_format($data->statistics['total_users'] ?? 0) }}
                        </dd>
                    </div>
                    <div class="bg-green-50 rounded-lg p-3">
                        <dt class="text-xs font-medium text-gray-500">활성 사용자</dt>
                        <dd class="mt-1 text-2xl font-semibold text-green-600">
                            {{ number_format($data->statistics['active_users'] ?? 0) }}
                        </dd>
                    </div>
                    <div class="bg-blue-50 rounded-lg p-3">
                        <dt class="text-xs font-medium text-gray-500">이번 달 신규</dt>
                        <dd class="mt-1 text-2xl font-semibold text-blue-600">
                            {{ number_format($data->statistics['new_users_this_month'] ?? 0) }}
                        </dd>
                    </div>
                    <div class="bg-yellow-50 rounded-lg p-3">
                        <dt class="text-xs font-medium text-gray-500">최근 가입</dt>
                        <dd class="mt-1 text-xs text-gray-600">
                            @if($data->statistics['last_registration'] ?? null)
                                {{ \Carbon\Carbon::parse($data->statistics['last_registration'])->diffForHumans() }}
                            @else
                                없음
                            @endif
                        </dd>
                    </div>
                </div>
            </div>
            @endif

            {{-- 시스템 정보 --}}
            <div class="lg:col-span-2">
                <h4 class="text-sm font-medium text-gray-900 mb-4">시스템 정보</h4>
                <dl class="grid grid-cols-2 gap-x-4 gap-y-3 sm:grid-cols-4">
                    <div>
                        <dt class="text-xs font-medium text-gray-500">표시 순서</dt>
                        <dd class="mt-1">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                {{ $data->display_order }}
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500">상태</dt>
                        <dd class="mt-1">
                            @if($data->is_active)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                    활성
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                    비활성
                                </span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500">생성일</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $data->created_at->format('Y-m-d') }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500">수정일</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $data->updated_at->format('Y-m-d') }}</dd>
                    </div>
                </dl>
            </div>
        </div>
        @endif

        {{-- 사용자 탭 --}}
        @if($activeTab === 'users')
        <div>
            @if(isset($relatedData['recent_users']) && count($relatedData['recent_users']) > 0)
                <h4 class="text-sm font-medium text-gray-900 mb-4">최근 가입 사용자</h4>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">ID</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">이름</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">이메일</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">가입일</th>
                                <th class="px-3 py-2 text-center text-xs font-medium text-gray-600 uppercase">상태</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($relatedData['recent_users'] as $user)
                            <tr>
                                <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-900">{{ $user->id }}</td>
                                <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-900">{{ $user->name }}</td>
                                <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-900">{{ $user->email }}</td>
                                <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-500">
                                    {{ \Carbon\Carbon::parse($user->created_at)->format('Y-m-d H:i') }}
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap text-center">
                                    @if($user->is_active)
                                        <span class="px-1.5 inline-flex text-xs leading-4 font-medium rounded-full bg-green-100 text-green-800">
                                            활성
                                        </span>
                                    @else
                                        <span class="px-1.5 inline-flex text-xs leading-4 font-medium rounded-full bg-gray-100 text-gray-800">
                                            비활성
                                        </span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    <p class="mt-2 text-xs text-gray-500">이 국가의 사용자가 없습니다</p>
                </div>
            @endif
        </div>
        @endif

        {{-- 관련 국가 탭 --}}
        @if($activeTab === 'related')
        <div class="space-y-6">
            {{-- 같은 지역 국가 --}}
            @if(isset($data->neighbors) && count($data->neighbors) > 0)
            <div>
                <h4 class="text-sm font-medium text-gray-900 mb-4">같은 지역 국가</h4>
                <div class="grid grid-cols-2 gap-2 sm:grid-cols-3 lg:grid-cols-4">
                    @foreach($data->neighbors as $neighbor)
                    <a href="{{ route('admin.auth.countries.show', $neighbor->id) }}" 
                       class="flex items-center space-x-2 p-2 rounded-lg hover:bg-gray-50">
                        @if($neighbor->flag_emoji)
                            <span class="text-xl">{{ $neighbor->flag_emoji }}</span>
                        @endif
                        <span class="text-xs text-gray-900">{{ $neighbor->name }}</span>
                        <span class="text-xs text-gray-500">({{ $neighbor->code }})</span>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- 같은 통화 사용 국가 --}}
            @if(isset($relatedData['same_currency']) && count($relatedData['same_currency']) > 0)
            <div>
                <h4 class="text-sm font-medium text-gray-900 mb-4">같은 통화 사용 국가</h4>
                <div class="grid grid-cols-2 gap-2 sm:grid-cols-3 lg:grid-cols-4">
                    @foreach($relatedData['same_currency'] as $country)
                    <a href="{{ route('admin.auth.countries.show', $country->id) }}" 
                       class="flex items-center space-x-2 p-2 rounded-lg hover:bg-gray-50">
                        @if($country->flag_emoji)
                            <span class="text-xl">{{ $country->flag_emoji }}</span>
                        @endif
                        <span class="text-xs text-gray-900">{{ $country->name }}</span>
                        <span class="text-xs text-gray-500">({{ $country->code }})</span>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
        @endif

        {{-- 활동 로그 탭 --}}
        @if($activeTab === 'activity')
        <div>
            @if(isset($relatedData['activity_logs']) && count($relatedData['activity_logs']) > 0)
                <h4 class="text-sm font-medium text-gray-900 mb-4">활동 로그</h4>
                <div class="flow-root">
                    <ul class="-mb-8">
                        @foreach($relatedData['activity_logs'] as $index => $log)
                        <li>
                            <div class="relative pb-8">
                                @if($index < count($relatedData['activity_logs']) - 1)
                                <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200"></span>
                                @endif
                                <div class="relative flex space-x-3">
                                    <div>
                                        <span class="h-8 w-8 rounded-full bg-gray-400 flex items-center justify-center ring-8 ring-white">
                                            <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                                            </svg>
                                        </span>
                                    </div>
                                    <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                        <div>
                                            <p class="text-xs text-gray-900">{{ $log->description }}</p>
                                        </div>
                                        <div class="text-right text-xs whitespace-nowrap text-gray-500">
                                            {{ \Carbon\Carbon::parse($log->created_at)->diffForHumans() }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                </div>
            @else
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <p class="mt-2 text-xs text-gray-500">활동 로그가 없습니다</p>
                </div>
            @endif
        </div>
        @endif
    </div>
</div>