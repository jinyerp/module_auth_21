<div class="space-y-4">
    @if($data->account)
    <div class="bg-gray-50 rounded-lg p-4">
        <h3 class="text-xs font-medium text-gray-700 mb-2">계정 정보</h3>
        <dl class="grid grid-cols-2 gap-x-4 gap-y-2">
            <div>
                <dt class="text-xs text-gray-500">이메일</dt>
                <dd class="text-xs font-medium text-gray-900">{{ $data->account->email }}</dd>
            </div>
            <div>
                <dt class="text-xs text-gray-500">이름</dt>
                <dd class="text-xs font-medium text-gray-900">{{ $data->account->name }}</dd>
            </div>
            <div>
                <dt class="text-xs text-gray-500">휴면 기간</dt>
                <dd class="text-xs font-medium text-gray-900">{{ $data->getDaysSinceDormant() }}일</dd>
            </div>
            <div>
                <dt class="text-xs text-gray-500">삭제까지</dt>
                <dd class="text-xs font-medium {{ $data->getDaysUntilDeletion() < 30 ? 'text-red-600' : 'text-gray-900' }}">
                    {{ $data->getDaysUntilDeletion() ?? '-' }}일
                </dd>
            </div>
        </dl>
    </div>
    @endif

    <div>
        <label class="block text-xs font-medium text-gray-700 mb-1">
            상태
        </label>
        <select wire:model="form.status" 
                class="w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                {{ $data->status === 'deleted' ? 'disabled' : '' }}>
            <option value="dormant">휴면</option>
            <option value="notified">알림발송</option>
            <option value="reactivated">재활성화</option>
            @if($data->status === 'deleted')
            <option value="deleted" selected>삭제됨</option>
            @endif
        </select>
        @if($data->status === 'deleted')
        <p class="mt-1 text-xs text-red-600">삭제된 계정은 상태를 변경할 수 없습니다</p>
        @endif
        @error('form.status')
        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="block text-xs font-medium text-gray-700 mb-1">
            휴면 사유
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
            삭제 예정일
        </label>
        <input type="datetime-local" 
               wire:model="form.scheduled_deletion_at"
               class="w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
        <p class="mt-1 text-xs text-gray-500">
            비워두면 자동 삭제가 중지됩니다
        </p>
        @error('form.scheduled_deletion_at')
        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    @if($data->notified_at)
    <div>
        <label class="block text-xs font-medium text-gray-700 mb-1">
            알림 정보
        </label>
        <div class="bg-blue-50 rounded-md p-3">
            <dl class="space-y-1">
                <div class="flex justify-between">
                    <dt class="text-xs text-gray-600">마지막 알림 발송</dt>
                    <dd class="text-xs font-medium text-gray-900">{{ $data->notified_at->format('Y-m-d H:i') }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-xs text-gray-600">총 알림 횟수</dt>
                    <dd class="text-xs font-medium text-gray-900">{{ $data->notification_count }}회</dd>
                </div>
            </dl>
        </div>
    </div>
    @endif

    <div>
        <label class="block text-xs font-medium text-gray-700 mb-1">
            추가 정보 (JSON)
        </label>
        <textarea wire:model="form.meta" 
                  rows="4"
                  class="w-full px-3 py-2 text-xs font-mono border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                  placeholder='{"key": "value"}'>{{ json_encode($data->meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</textarea>
        <p class="mt-1 text-xs text-gray-500">
            JSON 형식의 추가 메타데이터
        </p>
        @error('form.meta')
        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4">
        <h3 class="text-xs font-medium text-yellow-800 mb-2">
            타임라인
        </h3>
        <ul class="space-y-1 text-xs text-yellow-700">
            <li>• 휴면 처리: {{ $data->dormant_at->format('Y-m-d H:i') }}</li>
            @if($data->notified_at)
            <li>• 알림 발송: {{ $data->notified_at->format('Y-m-d H:i') }} ({{ $data->notification_count }}회)</li>
            @endif
            @if($data->reactivated_at)
            <li>• 재활성화: {{ $data->reactivated_at->format('Y-m-d H:i') }}</li>
            @endif
            @if($data->scheduled_deletion_at)
            <li class="{{ $data->getDaysUntilDeletion() < 30 ? 'text-red-600 font-medium' : '' }}">
                • 삭제 예정: {{ $data->scheduled_deletion_at->format('Y-m-d') }} 
                ({{ $data->getDaysUntilDeletion() }}일 후)
            </li>
            @endif
        </ul>
    </div>
</div>