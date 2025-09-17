{{-- 상세 내용만 포함, 액션 버튼 제외 --}}
<div class="bg-white shadow overflow-hidden sm:rounded-lg">
    {{-- 헤더 섹션 --}}
    <div class="px-4 py-5 sm:px-6">
        <h3 class="text-lg leading-6 font-medium text-gray-900">
            로그 상세 정보
        </h3>
        <p class="mt-1 max-w-2xl text-sm text-gray-500">
            활동 로그 #{{ $data->id }}의 상세 내용
        </p>
        
        {{-- 의심스러운 활동 경고 --}}
        @if(isset($data->is_suspicious) && $data->is_suspicious)
            <div class="mt-3 p-2 bg-red-50 border border-red-200 rounded-md">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">
                            의심스러운 활동 감지됨
                        </h3>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- 기본 정보 섹션 --}}
    <div class="border-t border-gray-200">
        <dl>
            {{-- 로그 ID --}}
            <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-xs font-medium text-gray-500">로그 ID</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                    {{ $data->id }}
                </dd>
            </div>

            {{-- 회원 정보 --}}
            <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-xs font-medium text-gray-500">회원 ID</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                    @if($data->account_id)
                        <a href="{{ route('admin.auth.accounts.show', $data->account_id) }}" 
                           class="text-blue-600 hover:text-blue-900">
                            #{{ $data->account_id }}
                            @if($data->account)
                                ({{ $data->account->email ?? '이메일 없음' }})
                            @endif
                        </a>
                    @else
                        <span class="text-gray-500">-</span>
                    @endif
                </dd>
            </div>

            {{-- 활동 유형 --}}
            <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-xs font-medium text-gray-500">활동 유형</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                    @php
                        $badgeColor = match($data->action) {
                            'login' => 'bg-green-100 text-green-800',
                            'logout' => 'bg-blue-100 text-blue-800',
                            'login_failed' => 'bg-red-100 text-red-800',
                            'password_reset' => 'bg-yellow-100 text-yellow-800',
                            'email_change' => 'bg-purple-100 text-purple-800',
                            'profile_update' => 'bg-gray-100 text-gray-800',
                            'account_created' => 'bg-teal-100 text-teal-800',
                            'account_deleted' => 'bg-red-100 text-red-800',
                            'permission_changed' => 'bg-orange-100 text-orange-800',
                            default => 'bg-gray-100 text-gray-800'
                        };
                    @endphp
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $badgeColor }}">
                        {{ $data->action }}
                    </span>
                </dd>
            </div>

            {{-- 설명 --}}
            <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-xs font-medium text-gray-500">설명</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                    {{ $data->description ?? '-' }}
                </dd>
            </div>

            {{-- 상태 --}}
            <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-xs font-medium text-gray-500">상태</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                    @php
                        $statusColor = match($data->status) {
                            'success' => 'bg-green-100 text-green-800',
                            'failed' => 'bg-red-100 text-red-800',
                            'pending' => 'bg-yellow-100 text-yellow-800',
                            default => 'bg-gray-100 text-gray-800'
                        };
                    @endphp
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusColor }}">
                        {{ $data->status }}
                    </span>
                </dd>
            </div>

            {{-- 오류 메시지 (실패한 경우) --}}
            @if($data->status == 'failed' && $data->error_message)
                <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-xs font-medium text-gray-500">오류 메시지</dt>
                    <dd class="mt-1 text-sm text-red-600 sm:mt-0 sm:col-span-2">
                        {{ $data->error_message }}
                    </dd>
                </div>
            @endif

            {{-- IP 주소 및 위치 --}}
            <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-xs font-medium text-gray-500">IP 주소</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                    {{ $data->ip_address ?? '-' }}
                    @if(isset($data->location) && $data->location !== 'Unknown')
                        <span class="text-gray-500 ml-2">({{ $data->location }})</span>
                    @endif
                </dd>
            </div>

            {{-- 브라우저 정보 --}}
            <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-xs font-medium text-gray-500">브라우저 정보</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                    @if(isset($data->browser_info))
                        <div>
                            <span class="font-medium">브라우저:</span> {{ $data->browser_info['browser'] ?? 'Unknown' }}<br>
                            <span class="font-medium">OS:</span> {{ $data->browser_info['os'] ?? 'Unknown' }}
                        </div>
                    @endif
                    @if($data->user_agent)
                        <div class="mt-2">
                            <span class="font-medium">User Agent:</span>
                            <pre class="mt-1 text-xs text-gray-600 whitespace-pre-wrap">{{ $data->user_agent }}</pre>
                        </div>
                    @else
                        <span class="text-gray-500">-</span>
                    @endif
                </dd>
            </div>

            {{-- 발생 시간 --}}
            <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-xs font-medium text-gray-500">발생 시간</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                    {{ $data->performed_at ? $data->performed_at->format('Y-m-d H:i:s') : '-' }}
                    @if($data->performed_at)
                        <span class="text-gray-500 ml-2">({{ $data->performed_at->diffForHumans() }})</span>
                    @endif
                </dd>
            </div>

            {{-- 생성 시간 --}}
            <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-xs font-medium text-gray-500">생성 시간</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                    {{ $data->created_at ? $data->created_at->format('Y-m-d H:i:s') : '-' }}
                </dd>
            </div>
        </dl>
    </div>

    {{-- 변경 내역 섹션 (있는 경우) --}}
    @if($data->old_values || $data->new_values)
        <div class="border-t border-gray-200">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">
                    변경 내역
                </h3>
            </div>
            <div class="border-t border-gray-200">
                <dl>
                    {{-- 이전 값 --}}
                    @if($data->old_values)
                        <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-xs font-medium text-gray-500">이전 값</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                <pre class="bg-gray-100 p-2 rounded text-xs overflow-x-auto">{{ isset($data->old_values_formatted) ? $data->old_values_formatted : json_encode($data->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                            </dd>
                        </div>
                    @endif

                    {{-- 변경된 값 --}}
                    @if($data->new_values)
                        <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-xs font-medium text-gray-500">변경된 값</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                <pre class="bg-gray-100 p-2 rounded text-xs overflow-x-auto">{{ isset($data->new_values_formatted) ? $data->new_values_formatted : json_encode($data->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                            </dd>
                        </div>
                    @endif
                </dl>
            </div>
        </div>
    @endif

    {{-- 메타 데이터 섹션 (있는 경우) --}}
    @if($data->meta)
        <div class="border-t border-gray-200">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">
                    메타 데이터
                </h3>
            </div>
            <div class="border-t border-gray-200">
                <div class="bg-gray-50 px-4 py-5 sm:px-6">
                    <pre class="bg-gray-100 p-2 rounded text-xs overflow-x-auto">{{ isset($data->meta_formatted) ? $data->meta_formatted : json_encode($data->meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                </div>
            </div>
        </div>
    @endif

    {{-- 동일 IP 최근 활동 섹션 --}}
    @if(isset($data->recent_activities_from_ip) && count($data->recent_activities_from_ip) > 0)
        <div class="border-t border-gray-200">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">
                    동일 IP 최근 활동
                </h3>
                <p class="mt-1 text-sm text-gray-500">
                    IP {{ $data->ip_address }}에서의 최근 활동 내역
                </p>
            </div>
            <div class="border-t border-gray-200">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">활동</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">상태</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">시간</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($data->recent_activities_from_ip as $activity)
                            <tr>
                                <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-900">
                                    <a href="{{ route('admin.auth.account.logs.show', $activity->id) }}" 
                                       class="text-blue-600 hover:text-blue-900">
                                        #{{ $activity->id }}
                                    </a>
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-900">
                                    {{ $activity->action }}
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap text-xs">
                                    @php
                                        $statusColor = match($activity->status) {
                                            'success' => 'text-green-600',
                                            'failed' => 'text-red-600',
                                            default => 'text-gray-600'
                                        };
                                    @endphp
                                    <span class="{{ $statusColor }}">{{ $activity->status }}</span>
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-900">
                                    {{ $activity->performed_at ? $activity->performed_at->format('Y-m-d H:i:s') : '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>