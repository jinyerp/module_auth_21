{{-- 2단계 인증 비활성화 확인 --}}
<div class="bg-white shadow sm:rounded-lg">
    <div class="px-4 py-5 sm:p-6">
        <div class="sm:flex sm:items-start">
            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100 sm:mx-0 sm:h-10 sm:w-10">
                <svg class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                <h3 class="text-lg leading-6 font-medium text-gray-900">
                    2단계 인증 비활성화
                </h3>
                
                <div class="mt-4">
                    @if($data->account)
                    <div class="bg-gray-50 rounded-lg p-4 mb-4">
                        <dl class="grid grid-cols-2 gap-4">
                            <div>
                                <dt class="text-xs font-medium text-gray-500">사용자</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $data->account->name }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium text-gray-500">이메일</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $data->account->email }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium text-gray-500">현재 방법</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    @if($data->method === 'totp')
                                    TOTP (앱 인증)
                                    @elseif($data->method === 'sms')
                                    SMS
                                    @elseif($data->method === 'email')
                                    이메일
                                    @else
                                    {{ $data->method }}
                                    @endif
                                </dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium text-gray-500">활성화 기간</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    @if($data->enabled_at)
                                    {{ \Carbon\Carbon::parse($data->enabled_at)->diffForHumans() }}부터
                                    @else
                                    -
                                    @endif
                                </dd>
                            </div>
                        </dl>
                    </div>
                    @endif
                    
                    <div class="text-sm text-gray-700">
                        <p class="mb-2">
                            이 사용자의 2단계 인증을 비활성화하려고 합니다.
                        </p>
                        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-3 mb-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-4 w-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-xs text-yellow-700">
                                        <strong>경고:</strong> 비활성화 후 이 사용자는 2단계 인증 없이 로그인할 수 있습니다.
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <p class="text-xs text-gray-600 mb-2">비활성화 시 다음 작업이 수행됩니다:</p>
                        <ul class="text-xs text-gray-600 space-y-1 ml-4">
                            <li>• 2단계 인증이 즉시 비활성화됩니다</li>
                            <li>• 실패 시도 횟수가 초기화됩니다</li>
                            <li>• 기존 설정은 유지되어 재활성화 시 사용 가능합니다</li>
                            <li>• 사용자에게 알림이 전송될 수 있습니다</li>
                        </ul>
                    </div>
                </div>
                
                @if($data->last_used_at)
                <div class="mt-4 p-3 bg-blue-50 rounded-lg">
                    <p class="text-xs text-blue-700">
                        <strong>참고:</strong> 마지막 사용: {{ \Carbon\Carbon::parse($data->last_used_at)->format('Y년 m월 d일 H:i') }}
                        ({{ \Carbon\Carbon::parse($data->last_used_at)->diffForHumans() }})
                    </p>
                </div>
                @endif
                
                @if($data->failed_attempts > 0)
                <div class="mt-4 p-3 bg-red-50 rounded-lg">
                    <p class="text-xs text-red-700">
                        <strong>주의:</strong> 현재 실패 시도 {{ $data->failed_attempts }}회가 기록되어 있습니다.
                    </p>
                </div>
                @endif
            </div>
        </div>
        
        {{-- 비활성화 이유 입력 (선택사항) --}}
        <div class="mt-6">
            <label for="reason" class="block text-xs font-medium text-gray-700 mb-1">
                비활성화 이유 (선택사항)
            </label>
            <textarea wire:model="disableReason"
                      id="reason"
                      rows="2"
                      placeholder="비활성화 이유를 입력하세요..."
                      class="w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500"></textarea>
        </div>
        
        {{-- 액션 버튼은 Livewire 컴포넌트에서 처리 --}}
    </div>
</div>