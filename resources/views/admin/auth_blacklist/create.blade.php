<div class="space-y-6">
    {{-- 기본 정보 섹션 --}}
    <div class="bg-white shadow rounded-lg p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">기본 정보</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            {{-- 차단 유형 --}}
            <div>
                <label for="type" class="block text-xs font-medium text-gray-700 mb-1">
                    차단 유형 <span class="text-red-500">*</span>
                </label>
                <select wire:model="form.type" id="type"
                        class="w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                        required>
                    <option value="">선택하세요</option>
                    <option value="email">이메일</option>
                    <option value="ip">IP 주소</option>
                    <option value="phone">전화번호</option>
                    <option value="domain">도메인</option>
                    <option value="user_agent">User Agent</option>
                    <option value="account">계정</option>
                </select>
                @error('form.type')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- 차단 대상 --}}
            <div>
                <label for="value" class="block text-xs font-medium text-gray-700 mb-1">
                    차단 대상 <span class="text-red-500">*</span>
                </label>
                <input type="text" wire:model="form.value" id="value"
                       placeholder="차단할 값을 입력하세요"
                       class="w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                       required>
                <p class="mt-1 text-xs text-gray-500">
                    @if($form['type'] ?? false)
                        @switch($form['type'])
                            @case('email')
                                예: user@example.com
                                @break
                            @case('ip')
                                예: 192.168.1.1
                                @break
                            @case('phone')
                                예: 010-1234-5678
                                @break
                            @case('domain')
                                예: example.com
                                @break
                            @case('user_agent')
                                예: Mozilla/5.0...
                                @break
                            @case('account')
                                예: 사용자 ID 또는 계정명
                                @break
                        @endswitch
                    @else
                        먼저 차단 유형을 선택하세요
                    @endif
                </p>
                @error('form.value')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- 차단 사유 --}}
        <div class="mt-4">
            <label for="reason" class="block text-xs font-medium text-gray-700 mb-1">
                차단 사유 <span class="text-red-500">*</span>
            </label>
            <input type="text" wire:model="form.reason" id="reason"
                   placeholder="차단 사유를 간단히 입력하세요"
                   class="w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                   maxlength="255" required>
            @error('form.reason')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- 상세 설명 --}}
        <div class="mt-4">
            <label for="description" class="block text-xs font-medium text-gray-700 mb-1">
                상세 설명
            </label>
            <textarea wire:model="form.description" id="description"
                      rows="3"
                      placeholder="차단에 대한 상세 설명을 입력하세요 (선택사항)"
                      class="w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500"></textarea>
            @error('form.description')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    {{-- 차단 설정 섹션 --}}
    <div class="bg-white shadow rounded-lg p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">차단 설정</h3>

        <div class="space-y-4">
            {{-- 활성화 상태 --}}
            <div class="flex items-center">
                <input type="checkbox" wire:model="form.is_active" id="is_active"
                       class="h-3.5 w-3.5 text-blue-600 focus:ring-1 focus:ring-blue-500 border-gray-200 rounded">
                <label for="is_active" class="ml-2 text-xs font-medium text-gray-700">
                    즉시 활성화
                </label>
                <p class="ml-4 text-xs text-gray-500">
                    체크하면 저장과 동시에 차단이 활성화됩니다
                </p>
            </div>

            {{-- 영구 차단 --}}
            <div class="flex items-center">
                <input type="checkbox" wire:model="form.is_permanent" id="is_permanent"
                       class="h-3.5 w-3.5 text-blue-600 focus:ring-1 focus:ring-blue-500 border-gray-200 rounded">
                <label for="is_permanent" class="ml-2 text-xs font-medium text-gray-700">
                    영구 차단
                </label>
                <p class="ml-4 text-xs text-gray-500">
                    체크하면 만료 기간 없이 영구적으로 차단됩니다
                </p>
            </div>

            {{-- 만료 일시 --}}
            @if(!($form['is_permanent'] ?? false))
            <div>
                <label for="expires_at" class="block text-xs font-medium text-gray-700 mb-1">
                    만료 일시
                </label>
                <input type="datetime-local" wire:model="form.expires_at" id="expires_at"
                       class="w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                <p class="mt-1 text-xs text-gray-500">
                    비워두면 기본 30일 후 만료됩니다
                </p>
                @error('form.expires_at')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
            @endif
        </div>
    </div>

    {{-- 일괄 추가 섹션 --}}
    <div class="bg-white shadow rounded-lg p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">일괄 추가 (선택사항)</h3>
        
        <div class="space-y-4">
            <div>
                <label for="bulk_values" class="block text-xs font-medium text-gray-700 mb-1">
                    여러 개 한번에 추가
                </label>
                <textarea wire:model="form.bulk_values" id="bulk_values"
                          rows="5"
                          placeholder="한 줄에 하나씩 입력하세요&#10;예:&#10;user1@example.com&#10;user2@example.com&#10;192.168.1.1"
                          class="w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500 font-mono"></textarea>
                <p class="mt-1 text-xs text-gray-500">
                    같은 유형의 여러 항목을 한 번에 추가할 수 있습니다. 한 줄에 하나씩 입력하세요.
                </p>
            </div>
        </div>
    </div>
</div>