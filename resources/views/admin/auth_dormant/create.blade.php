<div class="space-y-4">
    <div>
        <label class="block text-xs font-medium text-gray-700 mb-1">
            계정 선택 <span class="text-red-500">*</span>
        </label>
        <select wire:model="form.account_id" 
                class="w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
            <option value="">휴면 처리할 계정을 선택하세요</option>
            @if(isset($availableAccounts))
                @foreach($availableAccounts as $account)
                <option value="{{ $account->id }}">
                    {{ $account->email }} ({{ $account->name }})
                </option>
                @endforeach
            @endif
        </select>
        @error('form.account_id')
        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="block text-xs font-medium text-gray-700 mb-1">
            휴면 사유 <span class="text-red-500">*</span>
        </label>
        <select wire:model="form.reason" 
                class="w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
            <option value="inactivity">장기 미접속</option>
            <option value="request">사용자 요청</option>
            <option value="policy">정책 위반</option>
            <option value="security">보안 문제</option>
            <option value="other">기타</option>
        </select>
        @error('form.reason')
        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="block text-xs font-medium text-gray-700 mb-1">
            상세 사유
        </label>
        <textarea wire:model="form.reason_detail" 
                  rows="3"
                  class="w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                  placeholder="휴면 처리 상세 사유를 입력하세요"></textarea>
        @error('form.reason_detail')
        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="block text-xs font-medium text-gray-700 mb-1">
            삭제 예정일 <span class="text-red-500">*</span>
        </label>
        <input type="date" 
               wire:model="form.scheduled_deletion_at"
               min="{{ date('Y-m-d') }}"
               class="w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
        <p class="mt-1 text-xs text-gray-500">
            개인정보보호법에 따라 휴면계정은 일정 기간 후 삭제됩니다
        </p>
        @error('form.scheduled_deletion_at')
        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="flex items-center">
            <input type="checkbox" 
                   wire:model="form.send_notification"
                   class="h-3.5 w-3.5 text-blue-600 focus:ring-1 focus:ring-blue-500 border-gray-200 rounded">
            <span class="ml-2 text-xs text-gray-700">즉시 알림 발송</span>
        </label>
        <p class="mt-1 ml-5 text-xs text-gray-500">
            선택 시 사용자에게 휴면 처리 알림이 즉시 발송됩니다
        </p>
    </div>

    <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" 
                          d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" 
                          clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-xs font-medium text-yellow-800">
                    주의사항
                </h3>
                <div class="mt-2 text-xs text-yellow-700">
                    <ul class="list-disc list-inside space-y-1">
                        <li>휴면 처리된 계정은 로그인할 수 없습니다</li>
                        <li>휴면 계정은 사용자 요청 시 재활성화 가능합니다</li>
                        <li>삭제 예정일이 지난 계정은 자동으로 완전 삭제됩니다</li>
                        <li>삭제된 계정의 데이터는 복구할 수 없습니다</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>