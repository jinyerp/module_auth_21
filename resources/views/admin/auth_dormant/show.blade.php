<div class="bg-white shadow overflow-hidden sm:rounded-lg">
    {{-- 기본 정보 --}}
    <div class="px-4 py-5 sm:px-6">
        <h3 class="text-lg leading-6 font-medium text-gray-900">
            휴면계정 상세 정보
        </h3>
        <p class="mt-1 max-w-2xl text-sm text-gray-500">
            계정 ID: #{{ $data->id }}
        </p>
    </div>
    
    <div class="border-t border-gray-200">
        <dl>
            {{-- 상태 정보 --}}
            <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-xs font-medium text-gray-500">상태</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                    @php
                        $statusColors = [
                            'dormant' => 'bg-gray-100 text-gray-800',
                            'notified' => 'bg-yellow-100 text-yellow-800',
                            'reactivated' => 'bg-green-100 text-green-800',
                            'deleted' => 'bg-red-100 text-red-800'
                        ];
                        $statusLabels = [
                            'dormant' => '휴면',
                            'notified' => '알림발송',
                            'reactivated' => '재활성화',
                            'deleted' => '삭제됨'
                        ];
                    @endphp
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusColors[$data->status] ?? 'bg-gray-100 text-gray-800' }}">
                        {{ $statusLabels[$data->status] ?? $data->status }}
                    </span>
                </dd>
            </div>
            
            {{-- 계정 정보 --}}
            @if($data->account)
            <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-xs font-medium text-gray-500">계정 정보</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                    <div class="space-y-2">
                        <div><span class="text-gray-500">이메일:</span> {{ $data->account->email }}</div>
                        <div><span class="text-gray-500">이름:</span> {{ $data->account->name }}</div>
                        @if($data->account->phone)
                        <div><span class="text-gray-500">전화번호:</span> {{ $data->account->phone }}</div>
                        @endif
                        <div><span class="text-gray-500">계정 상태:</span> {{ $data->account->status }}</div>
                    </div>
                </dd>
            </div>
            @endif
            
            {{-- 휴면 정보 --}}
            <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-xs font-medium text-gray-500">휴면 정보</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                    <div class="space-y-2">
                        <div><span class="text-gray-500">휴면 사유:</span> {{ $data->reason }}</div>
                        <div><span class="text-gray-500">휴면 기간:</span> {{ $data->getDaysSinceDormant() }}일</div>
                        @if($data->getDaysUntilDeletion() !== null)
                        <div class="{{ $data->getDaysUntilDeletion() < 30 ? 'text-red-600 font-medium' : '' }}">
                            <span class="text-gray-500">삭제까지:</span> {{ $data->getDaysUntilDeletion() }}일
                        </div>
                        @endif
                    </div>
                </dd>
            </div>
            
            {{-- 날짜 정보 --}}
            <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-xs font-medium text-gray-500">날짜 정보</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                    <div class="space-y-2">
                        @if($data->last_activity_at)
                        <div><span class="text-gray-500">마지막 활동:</span> {{ $data->last_activity_at->format('Y-m-d H:i:s') }}</div>
                        @endif
                        <div><span class="text-gray-500">휴면 처리일:</span> {{ $data->dormant_at->format('Y-m-d H:i:s') }}</div>
                        @if($data->notified_at)
                        <div><span class="text-gray-500">알림 발송일:</span> {{ $data->notified_at->format('Y-m-d H:i:s') }}</div>
                        @endif
                        @if($data->scheduled_deletion_at)
                        <div class="{{ $data->getDaysUntilDeletion() < 30 ? 'text-red-600 font-medium' : '' }}">
                            <span class="text-gray-500">삭제 예정일:</span> {{ $data->scheduled_deletion_at->format('Y-m-d') }}
                        </div>
                        @endif
                        @if($data->reactivated_at)
                        <div><span class="text-gray-500">재활성화일:</span> {{ $data->reactivated_at->format('Y-m-d H:i:s') }}</div>
                        @endif
                    </div>
                </dd>
            </div>
            
            {{-- 알림 정보 --}}
            <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-xs font-medium text-gray-500">알림 정보</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                    <div class="space-y-2">
                        <div><span class="text-gray-500">알림 횟수:</span> {{ $data->notification_count }}회</div>
                        @if($data->notified_at)
                        <div><span class="text-gray-500">마지막 알림:</span> {{ $data->notified_at->format('Y-m-d H:i:s') }}</div>
                        @endif
                    </div>
                </dd>
            </div>
            
            {{-- 재활성화 정보 --}}
            @if($data->reactivated_at)
            <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-xs font-medium text-gray-500">재활성화 정보</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                    <div class="space-y-2">
                        <div><span class="text-gray-500">재활성화일:</span> {{ $data->reactivated_at->format('Y-m-d H:i:s') }}</div>
                        @if($data->reactivatedByAccount)
                        <div><span class="text-gray-500">처리자:</span> {{ $data->reactivatedByAccount->name }} ({{ $data->reactivatedByAccount->email }})</div>
                        @endif
                        @if($data->reactivation_reason)
                        <div><span class="text-gray-500">사유:</span> {{ $data->reactivation_reason }}</div>
                        @endif
                    </div>
                </dd>
            </div>
            @endif
            
            {{-- 백업 데이터 --}}
            @if($data->backup_data)
            <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-xs font-medium text-gray-500">백업 데이터</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                    <details class="cursor-pointer">
                        <summary class="text-blue-600 hover:text-blue-900">데이터 보기</summary>
                        <pre class="mt-2 p-3 bg-gray-100 rounded text-xs overflow-x-auto">{{ json_encode($data->backup_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    </details>
                </dd>
            </div>
            @endif
            
            {{-- 메타데이터 --}}
            @if($data->meta)
            <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-xs font-medium text-gray-500">메타데이터</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                    <pre class="p-3 bg-gray-100 rounded text-xs overflow-x-auto">{{ json_encode($data->meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                </dd>
            </div>
            @endif
        </dl>
    </div>
</div>

{{-- 타임라인 --}}
@if(isset($data->timeline))
<div class="mt-6 bg-white shadow overflow-hidden sm:rounded-lg">
    <div class="px-4 py-5 sm:px-6">
        <h3 class="text-lg leading-6 font-medium text-gray-900">
            타임라인
        </h3>
    </div>
    <div class="border-t border-gray-200 px-4 py-5 sm:px-6">
        <div class="flow-root">
            <ul role="list" class="-mb-8">
                @foreach($data->timeline as $index => $event)
                <li>
                    <div class="relative pb-8">
                        @if($index < count($data->timeline) - 1)
                        <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                        @endif
                        <div class="relative flex space-x-3">
                            <div>
                                @php
                                    $colors = [
                                        'info' => 'bg-blue-500',
                                        'success' => 'bg-green-500',
                                        'warning' => 'bg-yellow-500',
                                        'danger' => 'bg-red-500',
                                        'default' => 'bg-gray-400'
                                    ];
                                    $color = $colors[$event['type']] ?? 'bg-gray-400';
                                @endphp
                                <span class="h-8 w-8 rounded-full {{ $color }} flex items-center justify-center ring-8 ring-white">
                                    <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                    </svg>
                                </span>
                            </div>
                            <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                <div>
                                    <p class="text-sm text-gray-900">{{ $event['event'] }}</p>
                                </div>
                                <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                    @if(isset($event['future']) && $event['future'])
                                        <span class="text-red-600 font-medium">{{ $event['date']->format('Y-m-d') }}</span>
                                    @else
                                        {{ $event['date']->format('Y-m-d H:i') }}
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
@endif