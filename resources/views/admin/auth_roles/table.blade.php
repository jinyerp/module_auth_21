{{-- 순수 테이블 구조만 포함 (페이지네이션, 필터, 검색 제외) --}}
<table class="min-w-full divide-y divide-gray-200">
    <thead class="bg-gray-50">
        <tr>
            {{-- 체크박스 --}}
            <th class="px-3 py-2 text-left">
                <input type="checkbox" 
                       wire:model="selectAll"
                       class="h-3.5 w-3.5 text-blue-600 focus:ring-1 focus:ring-blue-500 border-gray-200 rounded">
            </th>
            
            {{-- ID --}}
            <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">
                <button wire:click="sortBy('id')" class="flex items-center space-x-1">
                    <span>ID</span>
                    @if($sortField === 'id')
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            @if($sortDirection === 'asc')
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                            @else
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            @endif
                        </svg>
                    @endif
                </button>
            </th>
            
            {{-- 역할명 --}}
            <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">
                <button wire:click="sortBy('name')" class="flex items-center space-x-1">
                    <span>역할명</span>
                    @if($sortField === 'name')
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            @if($sortDirection === 'asc')
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                            @else
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            @endif
                        </svg>
                    @endif
                </button>
            </th>
            
            {{-- 슬러그 --}}
            <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">
                <button wire:click="sortBy('slug')" class="flex items-center space-x-1">
                    <span>슬러그</span>
                    @if($sortField === 'slug')
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            @if($sortDirection === 'asc')
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                            @else
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            @endif
                        </svg>
                    @endif
                </button>
            </th>
            
            {{-- 설명 --}}
            <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">
                설명
            </th>
            
            {{-- 권한 수 --}}
            <th class="px-3 py-2 text-center text-xs font-medium text-gray-600 uppercase">
                권한 수
            </th>
            
            {{-- 상태 --}}
            <th class="px-3 py-2 text-center text-xs font-medium text-gray-600 uppercase">
                상태
            </th>
            
            {{-- 생성일 --}}
            <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">
                <button wire:click="sortBy('created_at')" class="flex items-center space-x-1">
                    <span>생성일</span>
                    @if($sortField === 'created_at')
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            @if($sortDirection === 'asc')
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                            @else
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            @endif
                        </svg>
                    @endif
                </button>
            </th>
            
            {{-- 액션 --}}
            <th class="px-3 py-2 text-center text-xs font-medium text-gray-600 uppercase">
                액션
            </th>
        </tr>
    </thead>
    <tbody class="bg-white divide-y divide-gray-200">
        @forelse($rows as $item)
            <tr class="hover:bg-gray-50">
                {{-- 체크박스 --}}
                <td class="px-3 py-2.5 whitespace-nowrap">
                    <input type="checkbox" 
                           wire:model="selected"
                           value="{{ $item->id }}"
                           class="h-3.5 w-3.5 text-blue-600 focus:ring-1 focus:ring-blue-500 border-gray-200 rounded">
                </td>
                
                {{-- ID --}}
                <td class="px-3 py-2.5 whitespace-nowrap text-xs text-gray-900">
                    {{ $item->id }}
                </td>
                
                {{-- 역할명 --}}
                <td class="px-3 py-2.5 whitespace-nowrap text-xs">
                    <a href="{{ route('admin.auth.roles.show', $item->id) }}" 
                       class="text-blue-600 hover:text-blue-900 font-medium">
                        {{ $item->name }}
                    </a>
                    @if(in_array($item->slug, ['super-admin', 'admin', 'user']))
                        <span class="ml-1 px-1.5 inline-flex text-xs leading-4 font-medium rounded-full bg-purple-100 text-purple-800">
                            시스템
                        </span>
                    @endif
                </td>
                
                {{-- 슬러그 --}}
                <td class="px-3 py-2.5 whitespace-nowrap text-xs text-gray-600">
                    <code class="px-1 py-0.5 bg-gray-100 rounded text-xs">{{ $item->slug }}</code>
                </td>
                
                {{-- 설명 --}}
                <td class="px-3 py-2.5 text-xs text-gray-600">
                    <div class="max-w-xs truncate">
                        {{ $item->description ?? '-' }}
                    </div>
                </td>
                
                {{-- 권한 수 --}}
                <td class="px-3 py-2.5 whitespace-nowrap text-xs text-center text-gray-900">
                    @if(isset($item->permission_count))
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                            {{ $item->permission_count }}
                        </span>
                    @else
                        <span class="text-gray-400">0</span>
                    @endif
                </td>
                
                {{-- 상태 --}}
                <td class="px-3 py-2.5 whitespace-nowrap text-xs text-center">
                    @if($item->is_active)
                        <span class="px-1.5 inline-flex text-xs leading-4 font-medium rounded-full bg-green-100 text-green-800">
                            활성
                        </span>
                    @else
                        <span class="px-1.5 inline-flex text-xs leading-4 font-medium rounded-full bg-red-100 text-red-800">
                            비활성
                        </span>
                    @endif
                </td>
                
                {{-- 생성일 --}}
                <td class="px-3 py-2.5 whitespace-nowrap text-xs text-gray-600">
                    {{ \Carbon\Carbon::parse($item->created_at)->format('Y-m-d H:i') }}
                </td>
                
                {{-- 액션 --}}
                <td class="px-3 py-2.5 whitespace-nowrap text-xs text-center">
                    <div class="flex items-center justify-center space-x-2">
                        {{-- 보기 --}}
                        <a href="{{ route('admin.auth.roles.show', $item->id) }}"
                           class="text-gray-600 hover:text-gray-900"
                           title="보기">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </a>
                        
                        {{-- 수정 --}}
                        <a href="{{ route('admin.auth.roles.edit', $item->id) }}"
                           class="text-blue-600 hover:text-blue-900"
                           title="수정">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                        </a>
                        
                        {{-- 삭제 --}}
                        @if(!in_array($item->slug, ['super-admin', 'admin', 'user']))
                            <button wire:click="confirmDelete({{ $item->id }})"
                                    class="text-red-600 hover:text-red-900"
                                    title="삭제">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        @else
                            <span class="text-gray-400" title="시스템 역할">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                          d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                </svg>
                            </span>
                        @endif
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="9" class="px-3 py-8 text-center">
                    <div class="text-gray-500">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                        </svg>
                        <p class="mt-2 text-xs">역할이 없습니다.</p>
                    </div>
                </td>
            </tr>
        @endforelse
    </tbody>
</table>