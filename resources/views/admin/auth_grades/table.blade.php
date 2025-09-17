{{-- 회원 등급 목록 테이블 --}}
<table class="min-w-full divide-y divide-gray-200">
    <thead class="bg-gray-50">
        <tr>
            {{-- 체크박스 --}}
            <th class="px-3 py-2 text-left">
                <input type="checkbox" 
                       wire:model="selectAll"
                       class="h-3.5 w-3.5 text-blue-600 focus:ring-1 focus:ring-blue-500 border-gray-200 rounded">
            </th>
            
            {{-- 레벨 --}}
            <th class="px-3 py-2 text-center text-xs font-medium text-gray-600 uppercase cursor-pointer"
                wire:click="sortBy('level')">
                <div class="flex items-center justify-center">
                    레벨
                    @if($sortField === 'level')
                        @if($sortDirection === 'asc')
                            <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                            </svg>
                        @else
                            <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        @endif
                    @endif
                </div>
            </th>
            
            {{-- 등급 색상 --}}
            <th class="px-3 py-2 text-center text-xs font-medium text-gray-600 uppercase">
                색상
            </th>
            
            {{-- 등급명 --}}
            <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase cursor-pointer"
                wire:click="sortBy('name')">
                <div class="flex items-center">
                    등급명
                    @if($sortField === 'name')
                        @if($sortDirection === 'asc')
                            <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                            </svg>
                        @else
                            <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        @endif
                    @endif
                </div>
            </th>
            
            {{-- 등급 코드 --}}
            <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase cursor-pointer"
                wire:click="sortBy('code')">
                <div class="flex items-center">
                    코드
                    @if($sortField === 'code')
                        @if($sortDirection === 'asc')
                            <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                            </svg>
                        @else
                            <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        @endif
                    @endif
                </div>
            </th>
            
            {{-- 포인트 적립률 --}}
            <th class="px-3 py-2 text-right text-xs font-medium text-gray-600 uppercase cursor-pointer"
                wire:click="sortBy('point_rate')">
                <div class="flex items-center justify-end">
                    포인트
                    @if($sortField === 'point_rate')
                        @if($sortDirection === 'asc')
                            <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                            </svg>
                        @else
                            <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        @endif
                    @endif
                </div>
            </th>
            
            {{-- 할인율 --}}
            <th class="px-3 py-2 text-right text-xs font-medium text-gray-600 uppercase cursor-pointer"
                wire:click="sortBy('discount_rate')">
                <div class="flex items-center justify-end">
                    할인율
                    @if($sortField === 'discount_rate')
                        @if($sortDirection === 'asc')
                            <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                            </svg>
                        @else
                            <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        @endif
                    @endif
                </div>
            </th>
            
            {{-- 최소 구매금액 --}}
            <th class="px-3 py-2 text-right text-xs font-medium text-gray-600 uppercase">
                최소 구매금액
            </th>
            
            {{-- 회원 수 --}}
            <th class="px-3 py-2 text-center text-xs font-medium text-gray-600 uppercase">
                회원 수
            </th>
            
            {{-- 상태 --}}
            <th class="px-3 py-2 text-center text-xs font-medium text-gray-600 uppercase cursor-pointer"
                wire:click="sortBy('is_active')">
                <div class="flex items-center justify-center">
                    상태
                    @if($sortField === 'is_active')
                        @if($sortDirection === 'asc')
                            <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                            </svg>
                        @else
                            <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        @endif
                    @endif
                </div>
            </th>
            
            {{-- 액션 --}}
            <th class="px-3 py-2 text-center text-xs font-medium text-gray-600 uppercase">
                관리
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
            
            {{-- 레벨 --}}
            <td class="px-3 py-2.5 whitespace-nowrap text-center">
                <span class="inline-flex items-center justify-center w-8 h-8 text-xs font-bold text-white bg-gradient-to-r from-blue-500 to-blue-600 rounded-full">
                    {{ $item->level }}
                </span>
            </td>
            
            {{-- 등급 색상 --}}
            <td class="px-3 py-2.5 whitespace-nowrap text-center">
                @if($item->color)
                <div class="inline-block w-6 h-6 rounded-full border border-gray-300"
                     style="background-color: {{ $item->color }};"
                     title="{{ $item->color }}"></div>
                @else
                <span class="text-xs text-gray-400">-</span>
                @endif
            </td>
            
            {{-- 등급명 --}}
            <td class="px-3 py-2.5 whitespace-nowrap">
                <a href="{{ route('admin.auth.grades.show', $item->id) }}" 
                   class="text-xs text-blue-600 hover:text-blue-900 font-medium">
                    {{ $item->name }}
                    @if($item->icon)
                    <span class="ml-1 text-gray-400">
                        <i class="{{ $item->icon }}"></i>
                    </span>
                    @endif
                </a>
                @if($item->description)
                <p class="text-xs text-gray-500 mt-1">{{ Str::limit($item->description, 50) }}</p>
                @endif
            </td>
            
            {{-- 등급 코드 --}}
            <td class="px-3 py-2.5 whitespace-nowrap">
                <span class="text-xs text-gray-900 font-mono">{{ $item->code }}</span>
            </td>
            
            {{-- 포인트 적립률 --}}
            <td class="px-3 py-2.5 whitespace-nowrap text-right">
                <span class="text-xs text-gray-900">{{ number_format($item->point_rate * 100, 1) }}%</span>
            </td>
            
            {{-- 할인율 --}}
            <td class="px-3 py-2.5 whitespace-nowrap text-right">
                <span class="text-xs text-gray-900">{{ number_format($item->discount_rate, 0) }}%</span>
            </td>
            
            {{-- 최소 구매금액 --}}
            <td class="px-3 py-2.5 whitespace-nowrap text-right">
                <span class="text-xs text-gray-900">₩{{ number_format($item->min_purchase) }}</span>
            </td>
            
            {{-- 회원 수 --}}
            <td class="px-3 py-2.5 whitespace-nowrap text-center">
                @if(isset($item->user_count))
                <span class="text-xs text-gray-900">{{ number_format($item->user_count) }}</span>
                @else
                <span class="text-xs text-gray-400">0</span>
                @endif
            </td>
            
            {{-- 상태 --}}
            <td class="px-3 py-2.5 whitespace-nowrap text-center">
                @if($item->is_active)
                    <span class="px-1.5 inline-flex text-xs leading-4 font-medium rounded-full bg-green-100 text-green-800">
                        활성
                    </span>
                @else
                    <span class="px-1.5 inline-flex text-xs leading-4 font-medium rounded-full bg-gray-100 text-gray-800">
                        비활성
                    </span>
                @endif
            </td>
            
            {{-- 액션 --}}
            <td class="px-3 py-2.5 whitespace-nowrap text-center">
                <div class="flex items-center justify-center space-x-1">
                    {{-- 보기 --}}
                    <a href="{{ route('admin.auth.grades.show', $item->id) }}"
                       class="text-gray-600 hover:text-gray-900"
                       title="상세 보기">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                    </a>
                    
                    {{-- 수정 --}}
                    <a href="{{ route('admin.auth.grades.edit', $item->id) }}"
                       class="text-gray-600 hover:text-gray-900"
                       title="수정">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                    </a>
                    
                    {{-- 삭제 --}}
                    @if($item->level != 1)
                    <a href="{{ route('admin.auth.grades.delete', $item->id) }}"
                       class="text-red-600 hover:text-red-900"
                       title="삭제">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </a>
                    @else
                    <span class="text-gray-300" title="기본 등급은 삭제할 수 없습니다">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </span>
                    @endif
                </div>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="11" class="px-3 py-8 text-center">
                <div class="text-gray-500 text-xs">
                    <svg class="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                    </svg>
                    등록된 회원 등급이 없습니다.
                </div>
            </td>
        </tr>
        @endforelse
    </tbody>
</table>