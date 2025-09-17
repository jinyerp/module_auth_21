{{-- 2단계 인증 상세 정보 --}}
<div class="bg-white shadow overflow-hidden sm:rounded-lg">
    {{-- 헤더 --}}
    <div class="px-4 py-5 sm:px-6">
        <div class="flex justify-between items-center">
            <div>
                <h3 class="text-lg leading-6 font-medium text-gray-900">
                    2단계 인증 상세 정보
                </h3>
                <p class="mt-1 text-sm text-gray-500">
                    @if($data->account)
                    {{ $data->account->name }} ({{ $data->account->email }})
                    @else
                    사용자 정보 없음
                    @endif
                </p>
            </div>
            
            {{-- 상태 배지 --}}
            @if($data->enabled)
            <span class="px-3 py-1 inline-flex text-sm leading-5 font-medium rounded-full bg-green-100 text-green-800">
                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                활성화
            </span>
            @else
            <span class="px-3 py-1 inline-flex text-sm leading-5 font-medium rounded-full bg-gray-100 text-gray-800">
                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
                비활성화
            </span>
            @endif
        </div>
    </div>

    <div class="border-t border-gray-200">
        {{-- 기본 정보 섹션 --}}
        <div class="px-4 py-5 sm:px-6">
            <h4 class="text-sm font-medium text-gray-900 mb-4">기본 정보</h4>
            <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                <div>
                    <dt class="text-xs font-medium text-gray-500">인증 방법</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        @if(isset($data->method_detail))
                        <div class="flex items-center">
                            @if($data->method === 'totp')
                            <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                            @elseif($data->method === 'sms')
                            <svg class="w-5 h-5 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                            </svg>
                            @elseif($data->method === 'email')
                            <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            @endif
                            <div>
                                <div class="font-medium">{{ $data->method_detail['label'] }}</div>
                                <div class="text-xs text-gray-500">{{ $data->method_detail['description'] }}</div>
                            </div>
                        </div>
                        @else
                        {{ $data->method }}
                        @endif
                    </dd>
                </div>
                
                <div>
                    <dt class="text-xs font-medium text-gray-500">활성화 일시</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        {{ $data->enabled_at_formatted ?? '비활성화 상태' }}
                    </dd>
                </div>
                
                <div>
                    <dt class="text-xs font-medium text-gray-500">마지막 사용</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        @if($data->last_used_at)
                        {{ $data->last_used_at_formatted }}
                        <span class="text-xs text-gray-500">({{ $data->last_used_ago }})</span>
                        @else
                        <span class="text-gray-400">사용 기록 없음</span>
                        @endif
                    </dd>
                </div>
                
                <div>
                    <dt class="text-xs font-medium text-gray-500">실패 시도</dt>
                    <dd class="mt-1 text-sm">
                        @if($data->failed_attempts > 0)
                            @if($data->failed_attempts >= 5)
                            <span class="text-red-600 font-semibold">{{ $data->failed_attempts }}회 (주의 필요)</span>
                            @elseif($data->failed_attempts >= 3)
                            <span class="text-yellow-600 font-semibold">{{ $data->failed_attempts }}회</span>
                            @else
                            <span class="text-gray-900">{{ $data->failed_attempts }}회</span>
                            @endif
                        @else
                        <span class="text-green-600">없음</span>
                        @endif
                    </dd>
                </div>
            </dl>
        </div>

        {{-- 보안 평가 섹션 --}}
        @if(isset($data->security_level))
        <div class="px-4 py-5 sm:px-6 bg-gray-50">
            <h4 class="text-sm font-medium text-gray-900 mb-4">보안 수준 평가</h4>
            
            <div class="mb-4">
                <div class="flex items-center">
                    <div class="flex-1">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-xs font-medium text-gray-700">보안 점수</span>
                            <span class="text-xs font-medium text-{{ $data->security_level['color'] }}-600">
                                {{ $data->security_level['score'] }}/100
                            </span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-{{ $data->security_level['color'] }}-600 h-2 rounded-full" 
                                 style="width: {{ $data->security_level['score'] }}%"></div>
                        </div>
                    </div>
                    <div class="ml-4">
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-{{ $data->security_level['color'] }}-100 text-{{ $data->security_level['color'] }}-800">
                            {{ $data->security_level['label'] }}
                        </span>
                    </div>
                </div>
            </div>
            
            @if(count($data->security_level['issues']) > 0)
            <div class="bg-yellow-50 border border-yellow-200 rounded-md p-3">
                <p class="text-xs font-medium text-yellow-800 mb-2">보안 개선 사항:</p>
                <ul class="text-xs text-yellow-700 space-y-1">
                    @foreach($data->security_level['issues'] as $issue)
                    <li class="flex items-start">
                        <svg class="w-3 h-3 mr-1 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                        {{ $issue }}
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif
        </div>
        @endif

        {{-- QR 코드 섹션 (TOTP인 경우) --}}
        @if($data->method === 'totp' && isset($qrCode))
        <div class="px-4 py-5 sm:px-6 border-t">
            <h4 class="text-sm font-medium text-gray-900 mb-4">TOTP 설정 정보</h4>
            <div class="bg-gray-50 rounded-lg p-4">
                @if(strpos($qrCode, 'data:image') === 0)
                <div class="flex justify-center mb-4">
                    <img src="{{ $qrCode }}" alt="TOTP QR Code" class="w-48 h-48">
                </div>
                @else
                <div class="text-xs text-gray-600 font-mono bg-white p-2 rounded break-all">
                    {{ $qrCode }}
                </div>
                @endif
                <p class="text-xs text-gray-500 text-center">
                    * QR 코드는 보안상 관리자에게만 표시됩니다
                </p>
            </div>
        </div>
        @endif

        {{-- 복구 코드 섹션 --}}
        @if(count($recoveryCodes) > 0)
        <div class="px-4 py-5 sm:px-6 border-t">
            <h4 class="text-sm font-medium text-gray-900 mb-4">복구 코드</h4>
            <div class="bg-gray-50 rounded-lg p-4">
                <div class="grid grid-cols-1 gap-2 font-mono text-xs">
                    @foreach($recoveryCodes as $code)
                    <div class="bg-white px-3 py-2 rounded border border-gray-200">
                        {{ $code }}
                    </div>
                    @endforeach
                </div>
                <p class="text-xs text-gray-500 mt-3">
                    * 보안상 코드가 마스킹되어 표시됩니다
                </p>
            </div>
        </div>
        @endif

        {{-- 최근 로그인 기록 --}}
        @if(isset($data->recent_logins) && count($data->recent_logins) > 0)
        <div class="px-4 py-5 sm:px-6 border-t">
            <h4 class="text-sm font-medium text-gray-900 mb-4">최근 로그인 기록</h4>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">시간</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">IP 주소</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">디바이스</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">2FA</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($data->recent_logins as $login)
                        <tr>
                            <td class="px-3 py-2 text-xs text-gray-900">{{ $login['time'] }}</td>
                            <td class="px-3 py-2 text-xs text-gray-900">{{ $login['ip'] }}</td>
                            <td class="px-3 py-2 text-xs text-gray-500">{{ $login['device'] }}</td>
                            <td class="px-3 py-2 text-xs">
                                @if($login['two_factor_used'])
                                <span class="text-green-600">✓ 사용</span>
                                @else
                                <span class="text-gray-400">-</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>
</div>