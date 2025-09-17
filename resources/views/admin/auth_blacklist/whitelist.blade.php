<div class="space-y-6">
    {{-- 화이트리스트 설명 --}}
    <div class="bg-blue-50 border-l-4 border-blue-400 p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800">화이트리스트란?</h3>
                <div class="mt-2 text-xs text-blue-700">
                    <p>화이트리스트는 블랙리스트에서 제외되어야 하는 신뢰할 수 있는 항목들의 목록입니다.</p>
                    <p class="mt-1">화이트리스트에 등록된 항목은 블랙리스트 규칙이 적용되어도 차단되지 않습니다.</p>
                </div>
            </div>
        </div>
    </div>

    {{-- 화이트리스트 추가 폼 --}}
    <div class="bg-white shadow rounded-lg p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">화이트리스트 추가</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="whitelist_type" class="block text-xs font-medium text-gray-700 mb-1">
                    유형 <span class="text-red-500">*</span>
                </label>
                <select wire:model="whitelistForm.type" id="whitelist_type"
                        class="w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">선택하세요</option>
                    <option value="email">이메일</option>
                    <option value="ip">IP 주소</option>
                    <option value="domain">도메인</option>
                    <option value="phone">전화번호</option>
                </select>
            </div>

            <div>
                <label for="whitelist_value" class="block text-xs font-medium text-gray-700 mb-1">
                    값 <span class="text-red-500">*</span>
                </label>
                <input type="text" wire:model="whitelistForm.value" id="whitelist_value"
                       placeholder="화이트리스트에 추가할 값"
                       class="w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div class="md:col-span-2">
                <label for="whitelist_reason" class="block text-xs font-medium text-gray-700 mb-1">
                    사유 <span class="text-red-500">*</span>
                </label>
                <input type="text" wire:model="whitelistForm.reason" id="whitelist_reason"
                       placeholder="화이트리스트 등록 사유"
                       class="w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div class="md:col-span-2">
                <label for="whitelist_description" class="block text-xs font-medium text-gray-700 mb-1">
                    설명
                </label>
                <textarea wire:model="whitelistForm.description" id="whitelist_description"
                          rows="2"
                          placeholder="추가 설명 (선택사항)"
                          class="w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500"></textarea>
            </div>
        </div>

        <div class="mt-4 flex justify-end">
            <button wire:click="addToWhitelist"
                    class="px-4 py-2 text-xs font-medium text-white bg-green-600 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                <svg class="inline-block w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                화이트리스트에 추가
            </button>
        </div>
    </div>

    {{-- 화이트리스트 목록 --}}
    <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-medium text-gray-900">화이트리스트 목록</h3>
                <div class="text-xs text-gray-500">
                    총 <span class="font-medium">{{ $whitelistCount ?? 0 }}</span>개 항목
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">ID</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">유형</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">값</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">사유</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">추가자</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">등록일</th>
                        <th class="px-3 py-2 text-center text-xs font-medium text-gray-600 uppercase">작업</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($whitelistItems ?? [] as $item)
                    <tr class="hover:bg-gray-50">
                        <td class="px-3 py-2.5 whitespace-nowrap text-xs text-gray-900">
                            {{ $item->id }}
                        </td>
                        <td class="px-3 py-2.5 whitespace-nowrap text-xs">
                            <span class="px-1.5 inline-flex text-xs leading-4 font-medium rounded-full bg-green-100 text-green-800">
                                {{ $item->type }}
                            </span>
                        </td>
                        <td class="px-3 py-2.5 whitespace-nowrap text-xs text-gray-900">
                            <code class="bg-gray-100 px-1 py-0.5 rounded">{{ $item->value }}</code>
                        </td>
                        <td class="px-3 py-2.5 text-xs text-gray-900">
                            {{ $item->reason }}
                        </td>
                        <td class="px-3 py-2.5 whitespace-nowrap text-xs text-gray-600">
                            {{ $item->added_by_name ?? 'System' }}
                        </td>
                        <td class="px-3 py-2.5 whitespace-nowrap text-xs text-gray-600">
                            {{ $item->created_at->format('Y-m-d H:i') }}
                        </td>
                        <td class="px-3 py-2.5 whitespace-nowrap text-xs text-center">
                            <button wire:click="removeFromWhitelist({{ $item->id }})"
                                    class="text-red-600 hover:text-red-900"
                                    title="화이트리스트에서 제거">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-3 py-8 text-center text-xs text-gray-500">
                            화이트리스트 항목이 없습니다.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- 화이트리스트 가져오기/내보내기 --}}
    <div class="bg-white shadow rounded-lg p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">가져오기/내보내기</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- 내보내기 --}}
            <div>
                <h4 class="text-sm font-medium text-gray-700 mb-2">화이트리스트 내보내기</h4>
                <p class="text-xs text-gray-500 mb-3">
                    현재 화이트리스트를 CSV 파일로 내보냅니다.
                </p>
                <button wire:click="exportWhitelist"
                        class="px-4 py-2 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                    <svg class="inline-block w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    CSV로 내보내기
                </button>
            </div>

            {{-- 가져오기 --}}
            <div>
                <h4 class="text-sm font-medium text-gray-700 mb-2">화이트리스트 가져오기</h4>
                <p class="text-xs text-gray-500 mb-3">
                    CSV 파일에서 화이트리스트를 가져옵니다.
                </p>
                <input type="file" 
                       wire:model="importFile"
                       accept=".csv"
                       class="text-xs">
                @if($importFile)
                <button wire:click="importWhitelist"
                        class="mt-2 px-4 py-2 text-xs font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">
                    가져오기
                </button>
                @endif
            </div>
        </div>
    </div>

    {{-- 화이트리스트 규칙 --}}
    <div class="bg-gray-50 rounded-lg p-4">
        <h4 class="text-xs font-medium text-gray-700 mb-2">화이트리스트 규칙</h4>
        <ul class="space-y-1 text-xs text-gray-600">
            <li>• 화이트리스트는 블랙리스트보다 우선순위가 높습니다</li>
            <li>• 도메인을 화이트리스트에 추가하면 해당 도메인의 모든 이메일이 허용됩니다</li>
            <li>• IP 범위는 CIDR 표기법을 사용할 수 있습니다 (예: 192.168.1.0/24)</li>
            <li>• 화이트리스트 항목은 정기적으로 검토하여 필요없는 항목은 제거해주세요</li>
            <li>• 화이트리스트에 추가된 항목도 활동 로그에 기록됩니다</li>
        </ul>
    </div>
</div>