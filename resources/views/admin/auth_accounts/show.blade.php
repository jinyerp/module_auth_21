{{--
    AuthAccounts 상세보기 뷰
    Tailwind CSS 스타일 적용
    액션 버튼 제외 (Livewire 컴포넌트에서 처리)
--}}
<div class="space-y-6">
    {{-- 기본 정보 섹션 --}}
    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">
                기본 정보
            </h3>
            <p class="mt-1 max-w-2xl text-xs text-gray-500">
                회원의 기본 정보입니다.
            </p>
        </div>
        <div class="border-t border-gray-200">
            <dl>
                <div class="bg-gray-50 px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-xs font-medium text-gray-500">ID</dt>
                    <dd class="mt-1 text-xs text-gray-900 sm:mt-0 sm:col-span-2">{{ $data->id }}</dd>
                </div>
                <div class="bg-white px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-xs font-medium text-gray-500">이름</dt>
                    <dd class="mt-1 text-xs text-gray-900 sm:mt-0 sm:col-span-2">{{ $data->name }}</dd>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-xs font-medium text-gray-500">이메일</dt>
                    <dd class="mt-1 text-xs text-gray-900 sm:mt-0 sm:col-span-2">
                        {{ $data->email }}
                        @if($data->email_verified_at)
                            <span class="ml-2 px-1.5 inline-flex text-xs leading-4 font-medium rounded-full bg-green-100 text-green-800">
                                인증완료
                            </span>
                        @else
                            <span class="ml-2 px-1.5 inline-flex text-xs leading-4 font-medium rounded-full bg-gray-100 text-gray-800">
                                미인증
                            </span>
                        @endif
                    </dd>
                </div>
                <div class="bg-white px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-xs font-medium text-gray-500">전화번호</dt>
                    <dd class="mt-1 text-xs text-gray-900 sm:mt-0 sm:col-span-2">{{ $data->phone ?? '-' }}</dd>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-xs font-medium text-gray-500">상태</dt>
                    <dd class="mt-1 text-xs sm:mt-0 sm:col-span-2">
                        @if($data->status === 'active')
                            <span class="px-1.5 inline-flex text-xs leading-4 font-medium rounded-full bg-green-100 text-green-800">
                                활성
                            </span>
                        @elseif($data->status === 'inactive')
                            <span class="px-1.5 inline-flex text-xs leading-4 font-medium rounded-full bg-gray-100 text-gray-800">
                                비활성
                            </span>
                        @elseif($data->status === 'suspended')
                            <span class="px-1.5 inline-flex text-xs leading-4 font-medium rounded-full bg-red-100 text-red-800">
                                정지
                            </span>
                        @else
                            {{ $data->status }}
                        @endif
                    </dd>
                </div>
                <div class="bg-white px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-xs font-medium text-gray-500">회원 등급</dt>
                    <dd class="mt-1 text-xs text-gray-900 sm:mt-0 sm:col-span-2">
                        {{ $data->grade_name ?? '-' }}
                        @if(isset($data->grade_level))
                            <span class="text-gray-500">(Level {{ $data->grade_level }})</span>
                        @endif
                    </dd>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-xs font-medium text-gray-500">국가</dt>
                    <dd class="mt-1 text-xs text-gray-900 sm:mt-0 sm:col-span-2">
                        {{ $data->country_name ?? '-' }}
                        @if(isset($data->country_code))
                            <span class="text-gray-500">({{ $data->country_code }})</span>
                        @endif
                    </dd>
                </div>
            </dl>
        </div>
    </div>

    {{-- 보안 정보 섹션 --}}
    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">
                보안 정보
            </h3>
            <p class="mt-1 max-w-2xl text-xs text-gray-500">
                계정 보안 관련 정보입니다.
            </p>
        </div>
        <div class="border-t border-gray-200">
            <dl>
                <div class="bg-gray-50 px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-xs font-medium text-gray-500">이메일 인증일</dt>
                    <dd class="mt-1 text-xs text-gray-900 sm:mt-0 sm:col-span-2">
                        {{ $data->email_verified_at ? \Carbon\Carbon::parse($data->email_verified_at)->format('Y-m-d H:i:s') : '미인증' }}
                    </dd>
                </div>
                <div class="bg-white px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-xs font-medium text-gray-500">2단계 인증</dt>
                    <dd class="mt-1 text-xs sm:mt-0 sm:col-span-2">
                        @if($data->two_factor_enabled ?? false)
                            <span class="px-1.5 inline-flex text-xs leading-4 font-medium rounded-full bg-green-100 text-green-800">
                                활성화
                            </span>
                        @else
                            <span class="px-1.5 inline-flex text-xs leading-4 font-medium rounded-full bg-gray-100 text-gray-800">
                                비활성화
                            </span>
                        @endif
                    </dd>
                </div>
                @if(isset($data->roles) && count($data->roles) > 0)
                <div class="bg-gray-50 px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-xs font-medium text-gray-500">역할</dt>
                    <dd class="mt-1 text-xs text-gray-900 sm:mt-0 sm:col-span-2">
                        @foreach($data->roles as $role)
                            <span class="mr-2 px-1.5 inline-flex text-xs leading-4 font-medium rounded-full bg-blue-100 text-blue-800">
                                {{ $role->name }}
                            </span>
                        @endforeach
                    </dd>
                </div>
                @endif
            </dl>
        </div>
    </div>

    {{-- 활동 정보 섹션 --}}
    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">
                활동 정보
            </h3>
            <p class="mt-1 max-w-2xl text-xs text-gray-500">
                회원의 활동 기록입니다.
            </p>
        </div>
        <div class="border-t border-gray-200">
            <dl>
                <div class="bg-gray-50 px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-xs font-medium text-gray-500">가입일</dt>
                    <dd class="mt-1 text-xs text-gray-900 sm:mt-0 sm:col-span-2">
                        {{ \Carbon\Carbon::parse($data->created_at)->format('Y-m-d H:i:s') }}
                        <span class="text-gray-500">({{ \Carbon\Carbon::parse($data->created_at)->diffForHumans() }})</span>
                    </dd>
                </div>
                <div class="bg-white px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-xs font-medium text-gray-500">마지막 수정일</dt>
                    <dd class="mt-1 text-xs text-gray-900 sm:mt-0 sm:col-span-2">
                        {{ \Carbon\Carbon::parse($data->updated_at)->format('Y-m-d H:i:s') }}
                        <span class="text-gray-500">({{ \Carbon\Carbon::parse($data->updated_at)->diffForHumans() }})</span>
                    </dd>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-xs font-medium text-gray-500">마지막 로그인</dt>
                    <dd class="mt-1 text-xs text-gray-900 sm:mt-0 sm:col-span-2">
                        @if($data->last_login_at)
                            {{ \Carbon\Carbon::parse($data->last_login_at)->format('Y-m-d H:i:s') }}
                            <span class="text-gray-500">({{ \Carbon\Carbon::parse($data->last_login_at)->diffForHumans() }})</span>
                        @else
                            로그인 기록 없음
                        @endif
                    </dd>
                </div>
                @if(isset($data->stats))
                <div class="bg-white px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-xs font-medium text-gray-500">통계</dt>
                    <dd class="mt-1 text-xs text-gray-900 sm:mt-0 sm:col-span-2">
                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <span class="text-gray-500">총 로그인:</span>
                                <span class="font-medium">{{ $data->stats->total_logins ?? 0 }}회</span>
                            </div>
                            <div>
                                <span class="text-gray-500">실패 시도:</span>
                                <span class="font-medium">{{ $data->stats->failed_login_attempts ?? 0 }}회</span>
                            </div>
                        </div>
                    </dd>
                </div>
                @endif
            </dl>
        </div>
    </div>

    {{-- 최근 로그인 기록 --}}
    @if(isset($data->recent_logins) && count($data->recent_logins) > 0)
    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">
                최근 로그인 기록
            </h3>
            <p class="mt-1 max-w-2xl text-xs text-gray-500">
                최근 5개의 로그인 기록입니다.
            </p>
        </div>
        <div class="border-t border-gray-200">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">시간</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">IP 주소</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">브라우저</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($data->recent_logins as $login)
                        <tr>
                            <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-900">
                                {{ \Carbon\Carbon::parse($login->created_at)->format('Y-m-d H:i:s') }}
                            </td>
                            <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-900">
                                {{ $login->ip_address ?? '-' }}
                            </td>
                            <td class="px-3 py-2 text-xs text-gray-900">
                                {{ Str::limit($login->user_agent ?? '-', 50) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    {{-- 최근 활동 로그 --}}
    @if(isset($data->recent_activities) && count($data->recent_activities) > 0)
    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">
                최근 활동 로그
            </h3>
            <p class="mt-1 max-w-2xl text-xs text-gray-500">
                최근 10개의 활동 기록입니다.
            </p>
        </div>
        <div class="border-t border-gray-200">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">시간</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">액션</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">설명</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">수행자</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($data->recent_activities as $activity)
                        <tr>
                            <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-900">
                                {{ \Carbon\Carbon::parse($activity->created_at)->format('Y-m-d H:i:s') }}
                            </td>
                            <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-900">
                                {{ $activity->action ?? '-' }}
                            </td>
                            <td class="px-3 py-2 text-xs text-gray-900">
                                {{ $activity->description ?? '-' }}
                            </td>
                            <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-900">
                                {{ $activity->performed_by ?? '-' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>