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
                       class="w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                       required>
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
                    활성화
                </label>
                <p class="ml-4 text-xs text-gray-500">
                    체크 해제하면 차단이 비활성화됩니다
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
                @error('form.expires_at')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
            @endif
        </div>
    </div>

    {{-- 통계 정보 섹션 --}}
    @if(isset($data) && $data->hit_count > 0)
    <div class="bg-white shadow rounded-lg p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">차단 통계</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <p class="text-xs font-medium text-gray-500">차단 횟수</p>
                <p class="mt-1 text-lg font-semibold text-gray-900">{{ $data->hit_count }}회</p>
            </div>
            
            @if($data->last_hit_at)
            <div>
                <p class="text-xs font-medium text-gray-500">마지막 차단</p>
                <p class="mt-1 text-sm text-gray-900">{{ $data->last_hit_at->format('Y-m-d H:i:s') }}</p>
                <p class="text-xs text-gray-500">{{ $data->last_hit_at->diffForHumans() }}</p>
            </div>
            @endif
            
            <div>
                <p class="text-xs font-medium text-gray-500">일평균 차단</p>
                <p class="mt-1 text-lg font-semibold text-gray-900">
                    @php
                        $days = $data->created_at->diffInDays(now()) ?: 1;
                        $average = round($data->hit_count / $days, 2);
                    @endphp
                    {{ $average }}회
                </p>
            </div>
        </div>
    </div>
    @endif

    {{-- 감사 정보 섹션 --}}
    @if(isset($data))
    <div class="bg-gray-50 rounded-lg p-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs text-gray-600">
            <div>
                <span class="font-medium">등록일:</span>
                {{ $data->created_at->format('Y-m-d H:i:s') }}
            </div>
            <div>
                <span class="font-medium">수정일:</span>
                {{ $data->updated_at->format('Y-m-d H:i:s') }}
            </div>
            @if($data->added_by)
            <div>
                <span class="font-medium">등록자:</span>
                {{ $data->blocked_by_name ?? 'Unknown' }}
            </div>
            @endif
        </div>
    </div>
    @endif
</div>