<table class="min-w-full divide-y divide-gray-200">
    <thead class="bg-gray-50">
        <tr>
            <th class="px-3 py-2 text-left">
                <input type="checkbox" wire:model="selectAll" class="h-3.5 w-3.5 text-blue-600 focus:ring-1 focus:ring-blue-500 border-gray-200 rounded">
            </th>
            <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">
                ID
            </th>
            <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">
                차단 유형
            </th>
            <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">
                차단 대상
            </th>
            <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">
                차단 사유
            </th>
            <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">
                차단자
            </th>
            <th class="px-3 py-2 text-center text-xs font-medium text-gray-600 uppercase">
                차단 횟수
            </th>
            <th class="px-3 py-2 text-center text-xs font-medium text-gray-600 uppercase">
                상태
            </th>
            <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">
                만료일
            </th>
            <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">
                등록일
            </th>
            <th class="px-3 py-2 text-center text-xs font-medium text-gray-600 uppercase">
                작업
            </th>
        </tr>
    </thead>
    <tbody class="bg-white divide-y divide-gray-200">
        @forelse($rows as $item)
        <tr class="hover:bg-gray-50">
            <td class="px-3 py-2.5 whitespace-nowrap">
                <input type="checkbox" wire:model="selected" value="{{ $item->id }}" class="h-3.5 w-3.5 text-blue-600 focus:ring-1 focus:ring-blue-500 border-gray-200 rounded">
            </td>
            <td class="px-3 py-2.5 whitespace-nowrap text-xs text-gray-900">
                {{ $item->id }}
            </td>
            <td class="px-3 py-2.5 whitespace-nowrap text-xs">
                <span class="px-1.5 inline-flex text-xs leading-4 font-medium rounded-full 
                    @if($item->type === 'email') bg-blue-100 text-blue-800
                    @elseif($item->type === 'ip') bg-purple-100 text-purple-800
                    @elseif($item->type === 'phone') bg-green-100 text-green-800
                    @elseif($item->type === 'domain') bg-yellow-100 text-yellow-800
                    @else bg-gray-100 text-gray-800
                    @endif">
                    {{ $item->type_label ?? $item->type }}
                </span>
            </td>
            <td class="px-3 py-2.5 whitespace-nowrap text-xs text-gray-900">
                <code class="bg-gray-100 px-1 py-0.5 rounded text-xs">{{ $item->value }}</code>
            </td>
            <td class="px-3 py-2.5 text-xs text-gray-900">
                <div class="max-w-xs truncate" title="{{ $item->reason }}">
                    {{ $item->reason }}
                </div>
            </td>
            <td class="px-3 py-2.5 whitespace-nowrap text-xs text-gray-600">
                {{ $item->blocked_by_name ?? 'System' }}
            </td>
            <td class="px-3 py-2.5 whitespace-nowrap text-xs text-center">
                @if($item->hit_count > 0)
                    <span class="px-1.5 inline-flex text-xs leading-4 font-medium rounded-full bg-red-100 text-red-800">
                        {{ $item->hit_count }}
                    </span>
                @else
                    <span class="text-gray-400">0</span>
                @endif
            </td>
            <td class="px-3 py-2.5 whitespace-nowrap text-xs text-center">
                @if($item->is_active)
                    @if($item->expires_at && $item->expires_at <= now())
                        <span class="px-1.5 inline-flex text-xs leading-4 font-medium rounded-full bg-yellow-100 text-yellow-800">
                            만료됨
                        </span>
                    @else
                        <span class="px-1.5 inline-flex text-xs leading-4 font-medium rounded-full bg-green-100 text-green-800">
                            활성
                        </span>
                    @endif
                @else
                    <span class="px-1.5 inline-flex text-xs leading-4 font-medium rounded-full bg-gray-100 text-gray-800">
                        비활성
                    </span>
                @endif
            </td>
            <td class="px-3 py-2.5 whitespace-nowrap text-xs text-gray-600">
                @if($item->expires_at)
                    {{ $item->expires_at->format('Y-m-d H:i') }}
                    @if($item->expires_at > now())
                        <br><span class="text-xs text-gray-500">({{ $item->expires_at->diffForHumans() }})</span>
                    @endif
                @else
                    <span class="px-1.5 inline-flex text-xs leading-4 font-medium rounded-full bg-blue-100 text-blue-800">
                        영구
                    </span>
                @endif
            </td>
            <td class="px-3 py-2.5 whitespace-nowrap text-xs text-gray-600">
                {{ $item->created_at->format('Y-m-d H:i') }}
            </td>
            <td class="px-3 py-2.5 whitespace-nowrap text-xs text-center">
                <div class="flex justify-center space-x-2">
                    <a href="{{ route('admin.auth.blacklist.show', $item->id) }}" 
                       class="text-blue-600 hover:text-blue-900" 
                       title="상세보기">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </a>
                    <a href="{{ route('admin.auth.blacklist.edit', $item->id) }}" 
                       class="text-green-600 hover:text-green-900" 
                       title="수정">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </a>
                    <button wire:click="testBlock({{ $item->id }})" 
                            class="text-purple-600 hover:text-purple-900" 
                            title="차단 테스트">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </button>
                    <button wire:click="toggleActive({{ $item->id }})" 
                            class="{{ $item->is_active ? 'text-yellow-600 hover:text-yellow-900' : 'text-green-600 hover:text-green-900' }}" 
                            title="{{ $item->is_active ? '비활성화' : '활성화' }}">
                        @if($item->is_active)
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                            </svg>
                        @else
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        @endif
                    </button>
                    <a href="{{ route('admin.auth.blacklist.delete', $item->id) }}" 
                       class="text-red-600 hover:text-red-900" 
                       title="삭제">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </a>
                </div>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="11" class="px-3 py-8 text-center text-xs text-gray-500">
                블랙리스트 항목이 없습니다.
            </td>
        </tr>
        @endforelse
    </tbody>
</table>