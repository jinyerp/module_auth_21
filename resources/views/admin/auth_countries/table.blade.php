{{-- 순수 테이블 구조만 포함 --}}
<table class="min-w-full divide-y divide-gray-200">
    <thead class="bg-gray-50">
        <tr>
            <th class="w-10 px-3 py-2">
                <input type="checkbox" wire:model.live="selectAll" 
                       class="h-3.5 w-3.5 text-blue-600 focus:ring-1 focus:ring-blue-500 border-gray-200 rounded">
            </th>
            <th class="px-3 py-2 text-center text-xs font-medium text-gray-600 uppercase">
                국기
            </th>
            <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">
                <div class="flex items-center cursor-pointer" wire:click="sortBy('code')">
                    코드
                    @if($sortField === 'code')
                        @if($sortDirection === 'asc')
                            <svg class="w-3 h-3 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M5 9l5-5 5 5H5z"/>
                            </svg>
                        @else
                            <svg class="w-3 h-3 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M15 11l-5 5-5-5h10z"/>
                            </svg>
                        @endif
                    @endif
                </div>
            </th>
            <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">
                <div class="flex items-center cursor-pointer" wire:click="sortBy('name')">
                    국가명
                    @if($sortField === 'name')
                        @if($sortDirection === 'asc')
                            <svg class="w-3 h-3 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M5 9l5-5 5 5H5z"/>
                            </svg>
                        @else
                            <svg class="w-3 h-3 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M15 11l-5 5-5-5h10z"/>
                            </svg>
                        @endif
                    @endif
                </div>
            </th>
            <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">
                수도
            </th>
            <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">
                대륙
            </th>
            <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">
                통화
            </th>
            <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">
                국가번호
            </th>
            <th class="px-3 py-2 text-center text-xs font-medium text-gray-600 uppercase">
                <div class="flex items-center justify-center cursor-pointer" wire:click="sortBy('display_order')">
                    순서
                    @if($sortField === 'display_order')
                        @if($sortDirection === 'asc')
                            <svg class="w-3 h-3 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M5 9l5-5 5 5H5z"/>
                            </svg>
                        @else
                            <svg class="w-3 h-3 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M15 11l-5 5-5-5h10z"/>
                            </svg>
                        @endif
                    @endif
                </div>
            </th>
            <th class="px-3 py-2 text-center text-xs font-medium text-gray-600 uppercase">
                사용자
            </th>
            <th class="px-3 py-2 text-center text-xs font-medium text-gray-600 uppercase">
                <div class="flex items-center justify-center cursor-pointer" wire:click="sortBy('is_active')">
                    상태
                    @if($sortField === 'is_active')
                        @if($sortDirection === 'asc')
                            <svg class="w-3 h-3 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M5 9l5-5 5 5H5z"/>
                            </svg>
                        @else
                            <svg class="w-3 h-3 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M15 11l-5 5-5-5h10z"/>
                            </svg>
                        @endif
                    @endif
                </div>
            </th>
            <th class="px-3 py-2 text-right text-xs font-medium text-gray-600 uppercase">
                작업
            </th>
        </tr>
    </thead>
    <tbody class="bg-white divide-y divide-gray-200">
        @forelse($rows as $country)
        <tr class="hover:bg-gray-50">
            <td class="w-10 px-3 py-2">
                <input type="checkbox" value="{{ $country->id }}" wire:model.live="selected"
                       class="h-3.5 w-3.5 text-blue-600 focus:ring-1 focus:ring-blue-500 border-gray-200 rounded">
            </td>
            <td class="px-3 py-2 text-center whitespace-nowrap text-sm">
                @if($country->flag_emoji)
                    <span class="text-2xl">{{ $country->flag_emoji }}</span>
                @else
                    <span class="text-xs text-gray-400">-</span>
                @endif
            </td>
            <td class="px-3 py-2 whitespace-nowrap">
                <div class="flex flex-col">
                    <span class="text-xs font-medium text-gray-900">{{ $country->code }}</span>
                    @if($country->code3)
                        <span class="text-xs text-gray-500">{{ $country->code3 }}</span>
                    @endif
                </div>
            </td>
            <td class="px-3 py-2 whitespace-nowrap">
                <a href="{{ route('admin.auth.countries.show', $country->id) }}" 
                   class="text-xs text-blue-600 hover:text-blue-900 font-medium">
                    {{ $country->name }}
                </a>
                @if($country->native_name && $country->native_name !== $country->name)
                    <div class="text-xs text-gray-500">{{ $country->native_name }}</div>
                @endif
            </td>
            <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-900">
                {{ $country->capital ?: '-' }}
            </td>
            <td class="px-3 py-2 whitespace-nowrap">
                @if($country->region)
                    <span class="px-1.5 inline-flex text-xs leading-4 font-medium rounded-full 
                           @switch($country->region)
                               @case('Asia') bg-yellow-100 text-yellow-800 @break
                               @case('Europe') bg-blue-100 text-blue-800 @break
                               @case('Americas') bg-green-100 text-green-800 @break
                               @case('Africa') bg-orange-100 text-orange-800 @break
                               @case('Oceania') bg-purple-100 text-purple-800 @break
                               @default bg-gray-100 text-gray-800
                           @endswitch">
                        {{ $country->region }}
                    </span>
                @else
                    <span class="text-xs text-gray-400">-</span>
                @endif
            </td>
            <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-900">
                @if($country->currency_code)
                    <span class="font-medium">{{ $country->currency_code }}</span>
                    @if($country->currency_symbol)
                        <span class="text-gray-500 ml-1">{{ $country->currency_symbol }}</span>
                    @endif
                @else
                    <span class="text-gray-400">-</span>
                @endif
            </td>
            <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-900">
                @if($country->phone_code)
                    +{{ $country->phone_code }}
                @else
                    <span class="text-gray-400">-</span>
                @endif
            </td>
            <td class="px-3 py-2 text-center whitespace-nowrap">
                <input type="number" value="{{ $country->display_order }}" 
                       wire:change="updateDisplayOrder({{ $country->id }}, $event.target.value)"
                       class="w-16 px-1 py-0.5 text-xs text-center border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
            </td>
            <td class="px-3 py-2 text-center whitespace-nowrap text-xs text-gray-900">
                @if(isset($country->user_count) && $country->user_count > 0)
                    <span class="font-medium">{{ number_format($country->user_count) }}</span>
                @else
                    <span class="text-gray-400">0</span>
                @endif
            </td>
            <td class="px-3 py-2 text-center whitespace-nowrap">
                <button wire:click="toggleStatus({{ $country->id }})" 
                        class="inline-flex items-center">
                    @if($country->is_active)
                        <span class="px-1.5 inline-flex text-xs leading-4 font-medium rounded-full bg-green-100 text-green-800">
                            활성
                        </span>
                    @else
                        <span class="px-1.5 inline-flex text-xs leading-4 font-medium rounded-full bg-gray-100 text-gray-800">
                            비활성
                        </span>
                    @endif
                </button>
            </td>
            <td class="px-3 py-2 text-right whitespace-nowrap text-xs font-medium">
                <div class="flex justify-end space-x-2">
                    <a href="{{ route('admin.auth.countries.show', $country->id) }}" 
                       class="text-gray-600 hover:text-gray-900">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </a>
                    <a href="{{ route('admin.auth.countries.edit', $country->id) }}" 
                       class="text-blue-600 hover:text-blue-900">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </a>
                    @if(!isset($country->user_count) || $country->user_count == 0)
                    <button wire:click="confirmDelete({{ $country->id }})" 
                            class="text-red-600 hover:text-red-900">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                    @else
                    <button disabled 
                            class="text-gray-300 cursor-not-allowed" 
                            title="사용자가 있는 국가는 삭제할 수 없습니다">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                    @endif
                </div>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="12" class="px-3 py-8 text-center">
                <div class="text-gray-500">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="mt-2 text-xs text-gray-500">등록된 국가가 없습니다</p>
                </div>
            </td>
        </tr>
        @endforelse
    </tbody>
</table>