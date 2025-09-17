{{-- 2단계 인증 목록 테이블 --}}
<table class="min-w-full divide-y divide-gray-200">
    <thead class="bg-gray-50">
        <tr>
            @if($jsonData['index']['bulkActions'] ?? false)
            <th scope="col" class="px-3 py-2 text-left">
                <input type="checkbox"
                       wire:model="selectAll"
                       class="h-3.5 w-3.5 text-blue-600 focus:ring-1 focus:ring-blue-500 border-gray-200 rounded">
            </th>
            @endif
            
            <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">
                ID
            </th>
            
            <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">
                사용자
            </th>
            
            <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">
                인증 방법
            </th>
            
            <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">
                상태
            </th>
            
            <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">
                마지막 사용
            </th>
            
            <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">
                실패 시도
            </th>
            
            <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">
                생성일
            </th>
            
            <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">
                작업
            </th>
        </tr>
    </thead>
    <tbody class="bg-white divide-y divide-gray-200">
        @forelse($rows as $item)
        <tr class="hover:bg-gray-50">
            @if($jsonData['index']['bulkActions'] ?? false)
            <td class="px-3 py-2 whitespace-nowrap">
                <input type="checkbox"
                       wire:model="selected"
                       value="{{ $item->id }}"
                       class="h-3.5 w-3.5 text-blue-600 focus:ring-1 focus:ring-blue-500 border-gray-200 rounded">
            </td>
            @endif
            
            <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-900">
                {{ $item->id }}
            </td>
            
            <td class="px-3 py-2 whitespace-nowrap text-xs">
                @if($item->account)
                <div>
                    <div class="text-gray-900 font-medium">{{ $item->account->name }}</div>
                    <div class="text-gray-500 text-xs">{{ $item->account->email }}</div>
                </div>
                @else
                <span class="text-gray-400 italic">삭제된 사용자</span>
                @endif
            </td>
            
            <td class="px-3 py-2 whitespace-nowrap text-xs">
                @php
                    $methodBadges = [
                        'totp' => 'bg-blue-100 text-blue-800',
                        'sms' => 'bg-purple-100 text-purple-800',
                        'email' => 'bg-indigo-100 text-indigo-800'
                    ];
                    $badgeClass = $methodBadges[$item->method] ?? 'bg-gray-100 text-gray-800';
                @endphp
                <span class="px-1.5 inline-flex text-xs leading-4 font-medium rounded-full {{ $badgeClass }}">
                    {{ $item->method_label ?? $item->method }}
                </span>
            </td>
            
            <td class="px-3 py-2 whitespace-nowrap text-xs">
                @if($item->enabled)
                <span class="px-1.5 inline-flex text-xs leading-4 font-medium rounded-full bg-green-100 text-green-800">
                    <svg class="w-3 h-3 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    활성화
                </span>
                @else
                <span class="px-1.5 inline-flex text-xs leading-4 font-medium rounded-full bg-gray-100 text-gray-800">
                    <svg class="w-3 h-3 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                    비활성화
                </span>
                @endif
            </td>
            
            <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-600">
                {{ $item->last_used_display ?? '-' }}
            </td>
            
            <td class="px-3 py-2 whitespace-nowrap text-xs">
                @if($item->failed_attempts > 0)
                    @if($item->failed_attempts >= 5)
                    <span class="px-1.5 inline-flex text-xs leading-4 font-medium rounded-full bg-red-100 text-red-800">
                        {{ $item->failed_attempts }}회
                    </span>
                    @elseif($item->failed_attempts >= 3)
                    <span class="px-1.5 inline-flex text-xs leading-4 font-medium rounded-full bg-yellow-100 text-yellow-800">
                        {{ $item->failed_attempts }}회
                    </span>
                    @else
                    <span class="text-gray-600">{{ $item->failed_attempts }}회</span>
                    @endif
                @else
                <span class="text-gray-400">-</span>
                @endif
            </td>
            
            <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-600">
                {{ \Carbon\Carbon::parse($item->created_at)->format('Y-m-d') }}
            </td>
            
            <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-900">
                <div class="flex items-center space-x-2">
                    <a href="{{ route('admin.auth.two_factor.show', $item->id) }}"
                       class="text-blue-600 hover:text-blue-900">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </a>
                    
                    <a href="{{ route('admin.auth.two_factor.edit', $item->id) }}"
                       class="text-gray-600 hover:text-gray-900">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </a>
                    
                    @if($item->enabled)
                    <a href="{{ route('admin.auth.two_factor.disable', $item->id) }}"
                       class="text-yellow-600 hover:text-yellow-900"
                       title="비활성화">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </a>
                    @endif
                    
                    @if($item->failed_attempts >= 3)
                    <button wire:click="resetFailedAttempts({{ $item->id }})"
                            class="text-green-600 hover:text-green-900"
                            title="실패 시도 초기화">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                    </button>
                    @endif
                </div>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="9" class="px-3 py-8 text-center text-xs text-gray-500">
                2단계 인증 데이터가 없습니다.
            </td>
        </tr>
        @endforelse
    </tbody>
</table>

{{-- 통계 정보 표시 (옵션) --}}
@if(isset($statistics))
<div class="mt-4 p-4 bg-gray-50 rounded-lg">
    <h3 class="text-sm font-medium text-gray-900 mb-2">2FA 현황</h3>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div>
            <dt class="text-xs text-gray-500">전체 사용자</dt>
            <dd class="text-lg font-semibold text-gray-900">{{ $statistics['total_users'] ?? 0 }}</dd>
        </div>
        <div>
            <dt class="text-xs text-gray-500">2FA 활성화</dt>
            <dd class="text-lg font-semibold text-green-600">{{ $statistics['enabled_users'] ?? 0 }}</dd>
        </div>
        <div>
            <dt class="text-xs text-gray-500">활성화 비율</dt>
            <dd class="text-lg font-semibold text-blue-600">{{ $statistics['enabled_percentage'] ?? 0 }}%</dd>
        </div>
        <div>
            <dt class="text-xs text-gray-500">최근 7일 사용</dt>
            <dd class="text-lg font-semibold text-gray-900">{{ $statistics['recent_usage'] ?? 0 }}</dd>
        </div>
    </div>
</div>
@endif