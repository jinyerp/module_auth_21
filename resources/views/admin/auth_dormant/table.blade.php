<table class="min-w-full divide-y divide-gray-200">
    <thead class="bg-gray-50">
        <tr>
            <th class="px-3 py-2 text-left">
                <input type="checkbox" wire:model="selectAll" 
                       class="h-3.5 w-3.5 text-blue-600 focus:ring-1 focus:ring-blue-500 border-gray-200 rounded">
            </th>
            <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">ID</th>
            <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">이메일</th>
            <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">이름</th>
            <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">마지막 활동</th>
            <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">휴면 처리일</th>
            <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">휴면 기간</th>
            <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">상태</th>
            <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">알림</th>
            <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">삭제 예정</th>
            <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">액션</th>
        </tr>
    </thead>
    <tbody class="bg-white divide-y divide-gray-200">
        @forelse($rows as $item)
        <tr class="hover:bg-gray-50">
            <td class="px-3 py-2.5">
                <input type="checkbox" wire:model="selected" value="{{ $item->id }}"
                       class="h-3.5 w-3.5 text-blue-600 focus:ring-1 focus:ring-blue-500 border-gray-200 rounded">
            </td>
            <td class="px-3 py-2.5 whitespace-nowrap text-xs text-gray-900">
                {{ $item->id }}
            </td>
            <td class="px-3 py-2.5 whitespace-nowrap text-xs">
                @if($item->account)
                <a href="{{ route('admin.auth.dormant.show', $item->id) }}" 
                   class="text-blue-600 hover:text-blue-900">
                    {{ $item->account->email }}
                </a>
                @else
                <span class="text-gray-400">-</span>
                @endif
            </td>
            <td class="px-3 py-2.5 whitespace-nowrap text-xs text-gray-900">
                {{ $item->account->name ?? '-' }}
            </td>
            <td class="px-3 py-2.5 whitespace-nowrap text-xs text-gray-600">
                @if($item->last_activity_at)
                    {{ $item->last_activity_at->format('Y-m-d H:i') }}
                @else
                    <span class="text-gray-400">-</span>
                @endif
            </td>
            <td class="px-3 py-2.5 whitespace-nowrap text-xs text-gray-600">
                {{ $item->dormant_at->format('Y-m-d') }}
            </td>
            <td class="px-3 py-2.5 whitespace-nowrap text-xs text-gray-900">
                <span class="font-medium">{{ $item->getDaysSinceDormant() }}</span> 일
            </td>
            <td class="px-3 py-2.5 whitespace-nowrap">
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
                <span class="px-1.5 inline-flex text-xs leading-4 font-medium rounded-full {{ $statusColors[$item->status] ?? 'bg-gray-100 text-gray-800' }}">
                    {{ $statusLabels[$item->status] ?? $item->status }}
                </span>
            </td>
            <td class="px-3 py-2.5 whitespace-nowrap text-xs text-gray-600">
                @if($item->notification_count > 0)
                    <span class="text-blue-600 font-medium">{{ $item->notification_count }}회</span>
                    @if($item->notified_at)
                        <br><span class="text-gray-400 text-xs">{{ $item->notified_at->format('m-d') }}</span>
                    @endif
                @else
                    <span class="text-gray-400">미발송</span>
                @endif
            </td>
            <td class="px-3 py-2.5 whitespace-nowrap text-xs">
                @if($item->scheduled_deletion_at)
                    @php
                        $daysUntilDeletion = $item->getDaysUntilDeletion();
                    @endphp
                    @if($daysUntilDeletion === 0)
                        <span class="text-red-600 font-bold">오늘</span>
                    @elseif($daysUntilDeletion < 30)
                        <span class="text-orange-600 font-medium">{{ $daysUntilDeletion }}일 후</span>
                    @else
                        <span class="text-gray-600">{{ $item->scheduled_deletion_at->format('Y-m-d') }}</span>
                    @endif
                @else
                    <span class="text-gray-400">-</span>
                @endif
            </td>
            <td class="px-3 py-2.5 whitespace-nowrap text-xs">
                <div class="flex space-x-2">
                    <a href="{{ route('admin.auth.dormant.show', $item->id) }}" 
                       class="text-gray-600 hover:text-gray-900" title="보기">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                    </a>
                    
                    @if(in_array($item->status, ['dormant', 'notified']))
                    <a href="{{ route('admin.auth.dormant.edit', $item->id) }}" 
                       class="text-blue-600 hover:text-blue-900" title="수정">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                    </a>
                    
                    <button wire:click="activate({{ $item->id }})" 
                            class="text-green-600 hover:text-green-900" title="활성화">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </button>
                    
                    <button wire:click="sendNotification({{ $item->id }})" 
                            class="text-yellow-600 hover:text-yellow-900" title="알림 발송">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                        </svg>
                    </button>
                    @endif
                    
                    @if($item->status !== 'deleted')
                    <button wire:click="confirmDelete({{ $item->id }})" 
                            class="text-red-600 hover:text-red-900" title="삭제">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </button>
                    @endif
                </div>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="11" class="px-3 py-8 text-center text-xs text-gray-500">
                휴면계정이 없습니다.
            </td>
        </tr>
        @endforelse
    </tbody>
</table>