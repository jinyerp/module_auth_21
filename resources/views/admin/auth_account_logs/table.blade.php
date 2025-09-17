{{-- 순수 테이블 구조만 포함, 페이지네이션 제외 --}}
<table class="min-w-full divide-y divide-gray-200">
    <thead class="bg-gray-50">
        <tr>
            {{-- ID 컬럼 --}}
            <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">
                <button wire:click="sortBy('id')" class="flex items-center space-x-1">
                    <span>ID</span>
                    @if($sortField === 'id')
                        @if($sortDirection === 'asc')
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                            </svg>
                        @else
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        @endif
                    @endif
                </button>
            </th>

            {{-- 회원 ID 컬럼 --}}
            <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">
                <button wire:click="sortBy('account_id')" class="flex items-center space-x-1">
                    <span>회원ID</span>
                    @if($sortField === 'account_id')
                        @if($sortDirection === 'asc')
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                            </svg>
                        @else
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        @endif
                    @endif
                </button>
            </th>

            {{-- 활동 컬럼 --}}
            <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">
                <button wire:click="sortBy('action')" class="flex items-center space-x-1">
                    <span>활동</span>
                    @if($sortField === 'action')
                        @if($sortDirection === 'asc')
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                            </svg>
                        @else
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        @endif
                    @endif
                </button>
            </th>

            {{-- 설명 컬럼 --}}
            <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">
                설명
            </th>

            {{-- 상태 컬럼 --}}
            <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">
                <button wire:click="sortBy('status')" class="flex items-center space-x-1">
                    <span>상태</span>
                    @if($sortField === 'status')
                        @if($sortDirection === 'asc')
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                            </svg>
                        @else
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        @endif
                    @endif
                </button>
            </th>

            {{-- IP 주소 컬럼 (lg 이상에서 표시) --}}
            <th class="hidden lg:table-cell px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">
                IP 주소
            </th>

            {{-- 발생시간 컬럼 --}}
            <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">
                <button wire:click="sortBy('performed_at')" class="flex items-center space-x-1">
                    <span>발생시간</span>
                    @if($sortField === 'performed_at')
                        @if($sortDirection === 'asc')
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                            </svg>
                        @else
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        @endif
                    @endif
                </button>
            </th>

            {{-- 액션 컬럼 --}}
            <th class="px-3 py-2 text-center text-xs font-medium text-gray-600 uppercase">
                작업
            </th>
        </tr>
    </thead>
    <tbody class="bg-white divide-y divide-gray-200">
        @forelse($rows as $item)
            <tr class="hover:bg-gray-50 {{ isset($item->is_suspicious) && $item->is_suspicious ? 'bg-red-50' : '' }}">
                {{-- ID --}}
                <td class="px-3 py-2.5 whitespace-nowrap text-xs text-gray-900">
                    {{ $item->id }}
                </td>

                {{-- 회원 ID (링크 포함) --}}
                <td class="px-3 py-2.5 whitespace-nowrap text-xs">
                    @if($item->account_id)
                        <a href="{{ route('admin.auth.accounts.show', $item->account_id) }}" 
                           class="text-blue-600 hover:text-blue-900">
                            #{{ $item->account_id }}
                        </a>
                    @else
                        <span class="text-gray-500">-</span>
                    @endif
                </td>

                {{-- 활동 (배지) --}}
                <td class="px-3 py-2.5 whitespace-nowrap text-xs">
                    @php
                        $badgeColor = match($item->action) {
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
                    <span class="px-1.5 inline-flex text-xs leading-4 font-medium rounded-full {{ $badgeColor }}">
                        {{ $item->action }}
                    </span>
                    @if(isset($item->is_suspicious) && $item->is_suspicious)
                        <span class="ml-1 text-red-600" title="의심스러운 활동">
                            <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                        </span>
                    @endif
                </td>

                {{-- 설명 --}}
                <td class="px-3 py-2.5 text-xs text-gray-900">
                    @if($item->description)
                        <span class="truncate block max-w-xs" title="{{ $item->description }}">
                            {{ Str::limit($item->description, 50) }}
                        </span>
                    @else
                        <span class="text-gray-500">-</span>
                    @endif
                </td>

                {{-- 상태 (배지) --}}
                <td class="px-3 py-2.5 whitespace-nowrap text-xs">
                    @php
                        $statusColor = match($item->status) {
                            'success' => 'bg-green-100 text-green-800',
                            'failed' => 'bg-red-100 text-red-800',
                            'pending' => 'bg-yellow-100 text-yellow-800',
                            default => 'bg-gray-100 text-gray-800'
                        };
                    @endphp
                    <span class="px-1.5 inline-flex text-xs leading-4 font-medium rounded-full {{ $statusColor }}">
                        {{ $item->status }}
                    </span>
                </td>

                {{-- IP 주소 (위치 정보 포함) --}}
                <td class="hidden lg:table-cell px-3 py-2.5 whitespace-nowrap text-xs text-gray-900">
                    @if($item->ip_address)
                        <div>
                            <span>{{ $item->ip_address }}</span>
                            @if(isset($item->location) && $item->location !== 'Unknown')
                                <span class="text-gray-500 text-xs block">{{ $item->location }}</span>
                            @endif
                        </div>
                    @else
                        <span class="text-gray-500">-</span>
                    @endif
                </td>

                {{-- 발생시간 --}}
                <td class="px-3 py-2.5 whitespace-nowrap text-xs text-gray-900">
                    @if($item->performed_at)
                        <div>
                            <span>{{ $item->performed_at->format('Y-m-d') }}</span>
                            <span class="text-gray-500 text-xs block">{{ $item->performed_at->format('H:i:s') }}</span>
                        </div>
                    @else
                        <span class="text-gray-500">-</span>
                    @endif
                </td>

                {{-- 액션 버튼들 --}}
                <td class="px-3 py-2.5 whitespace-nowrap text-center text-xs">
                    <div class="flex items-center justify-center space-x-2">
                        {{-- 보기 버튼 --}}
                        <a href="{{ route('admin.auth.account.logs.show', $item->id) }}" 
                           class="text-blue-600 hover:text-blue-900" title="상세보기">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </a>

                        {{-- 삭제 버튼 (권한이 있는 경우만 표시) --}}
                        @if($jsonData['features']['enableDelete'] ?? false)
                            <button wire:click="confirmDelete({{ $item->id }})" 
                                    class="text-red-600 hover:text-red-900" title="삭제">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        @endif
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="8" class="px-3 py-8 text-center text-xs text-gray-500">
                    데이터가 없습니다.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>