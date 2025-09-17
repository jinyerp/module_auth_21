{{--
    AuthAccounts 테이블 뷰
    Tailwind CSS 스타일 적용 및 Livewire 기능 통합
--}}
<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th scope="col" class="px-3 py-2 text-left">
                    <input type="checkbox"
                           wire:model.live="selectedAll"
                           class="h-3.5 w-3.5 text-blue-600 focus:ring-1 focus:ring-blue-500 border-gray-200 rounded">
                </th>
                <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">
                    <button wire:click="sortBy('id')" class="flex items-center">
                        ID
                        @if($sortField === 'id')
                            @if($sortDirection === 'asc')
                                <svg class="w-3 h-3 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L10 13.586l3.293-3.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            @else
                                <svg class="w-3 h-3 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L10 6.414l-3.293 3.293a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                                </svg>
                            @endif
                        @endif
                    </button>
                </th>
                <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">
                    <button wire:click="sortBy('name')" class="flex items-center">
                        이름
                        @if($sortField === 'name')
                            @if($sortDirection === 'asc')
                                <svg class="w-3 h-3 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L10 13.586l3.293-3.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            @else
                                <svg class="w-3 h-3 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L10 6.414l-3.293 3.293a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                                </svg>
                            @endif
                        @endif
                    </button>
                </th>
                <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">
                    <button wire:click="sortBy('email')" class="flex items-center">
                        이메일
                        @if($sortField === 'email')
                            @if($sortDirection === 'asc')
                                <svg class="w-3 h-3 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L10 13.586l3.293-3.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            @else
                                <svg class="w-3 h-3 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L10 6.414l-3.293 3.293a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                                </svg>
                            @endif
                        @endif
                    </button>
                </th>
                <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase hidden md:table-cell">
                    전화번호
                </th>
                <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">
                    <button wire:click="sortBy('status')" class="flex items-center">
                        상태
                        @if($sortField === 'status')
                            @if($sortDirection === 'asc')
                                <svg class="w-3 h-3 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L10 13.586l3.293-3.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            @else
                                <svg class="w-3 h-3 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L10 6.414l-3.293 3.293a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                                </svg>
                            @endif
                        @endif
                    </button>
                </th>
                <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase hidden md:table-cell">
                    등급
                </th>
                <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase hidden lg:table-cell">
                    국가
                </th>
                <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase hidden lg:table-cell">
                    <button wire:click="sortBy('last_login_at')" class="flex items-center">
                        마지막 로그인
                        @if($sortField === 'last_login_at')
                            @if($sortDirection === 'asc')
                                <svg class="w-3 h-3 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L10 13.586l3.293-3.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            @else
                                <svg class="w-3 h-3 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L10 6.414l-3.293 3.293a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                                </svg>
                            @endif
                        @endif
                    </button>
                </th>
                <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase hidden md:table-cell">
                    <button wire:click="sortBy('created_at')" class="flex items-center">
                        가입일
                        @if($sortField === 'created_at')
                            @if($sortDirection === 'asc')
                                <svg class="w-3 h-3 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L10 13.586l3.293-3.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            @else
                                <svg class="w-3 h-3 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L10 6.414l-3.293 3.293a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                                </svg>
                            @endif
                        @endif
                    </button>
                </th>
                <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">
                    작업
                </th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($rows as $item)
                <tr class="hover:bg-gray-50">
                    <td class="px-3 py-2.5 whitespace-nowrap">
                        <input type="checkbox"
                               wire:model.live="selected"
                               value="{{ $item->id }}"
                               class="h-3.5 w-3.5 text-blue-600 focus:ring-1 focus:ring-blue-500 border-gray-200 rounded">
                    </td>
                    <td class="px-3 py-2.5 whitespace-nowrap text-xs text-gray-900">
                        {{ $item->id }}
                    </td>
                    <td class="px-3 py-2.5 whitespace-nowrap text-xs text-gray-900">
                        <a href="{{ route('admin.auth.accounts.show', $item->id) }}" class="text-blue-600 hover:text-blue-900">
                            {{ $item->name }}
                        </a>
                    </td>
                    <td class="px-3 py-2.5 whitespace-nowrap text-xs text-gray-900">
                        {{ $item->email }}
                    </td>
                    <td class="px-3 py-2.5 whitespace-nowrap text-xs text-gray-900 hidden md:table-cell">
                        {{ $item->phone ?? '-' }}
                    </td>
                    <td class="px-3 py-2.5 whitespace-nowrap">
                        @if($item->status === 'active')
                            <span class="px-1.5 inline-flex text-xs leading-4 font-medium rounded-full bg-green-100 text-green-800">
                                활성
                            </span>
                        @elseif($item->status === 'inactive')
                            <span class="px-1.5 inline-flex text-xs leading-4 font-medium rounded-full bg-gray-100 text-gray-800">
                                비활성
                            </span>
                        @elseif($item->status === 'suspended')
                            <span class="px-1.5 inline-flex text-xs leading-4 font-medium rounded-full bg-red-100 text-red-800">
                                정지
                            </span>
                        @else
                            <span class="px-1.5 inline-flex text-xs leading-4 font-medium rounded-full bg-gray-100 text-gray-800">
                                {{ $item->status }}
                            </span>
                        @endif
                    </td>
                    <td class="px-3 py-2.5 whitespace-nowrap text-xs text-gray-900 hidden md:table-cell">
                        {{ $item->grade_name ?? '-' }}
                    </td>
                    <td class="px-3 py-2.5 whitespace-nowrap text-xs text-gray-900 hidden lg:table-cell">
                        {{ $item->country_name ?? '-' }}
                    </td>
                    <td class="px-3 py-2.5 whitespace-nowrap text-xs text-gray-500 hidden lg:table-cell">
                        @if($item->last_login_at)
                            {{ \Carbon\Carbon::parse($item->last_login_at)->format('Y-m-d H:i') }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="px-3 py-2.5 whitespace-nowrap text-xs text-gray-500 hidden md:table-cell">
                        {{ \Carbon\Carbon::parse($item->created_at)->format('Y-m-d') }}
                    </td>
                    <td class="px-3 py-2.5 whitespace-nowrap text-xs font-medium">
                        <div class="flex items-center space-x-2">
                            <a href="{{ route('admin.auth.accounts.show', $item->id) }}" 
                               class="text-gray-600 hover:text-gray-900"
                               title="보기">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </a>
                            <a href="{{ route('admin.auth.accounts.edit', $item->id) }}"
                               class="text-gray-600 hover:text-gray-900"
                               title="수정">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </a>
                            <button wire:click="delete({{ $item->id }})"
                                    wire:confirm="정말로 이 회원을 삭제하시겠습니까?"
                                    class="text-red-600 hover:text-red-900"
                                    title="삭제">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" class="px-3 py-8 text-center text-xs text-gray-500">
                        등록된 회원이 없습니다.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>