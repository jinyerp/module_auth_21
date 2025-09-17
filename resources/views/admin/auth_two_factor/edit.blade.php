{{-- 2단계 인증 설정 수정 폼 --}}
<div class="space-y-6">
    {{-- 사용자 정보 (읽기 전용) --}}
    <div class="bg-gray-50 p-4 rounded-lg">
        <h3 class="text-sm font-medium text-gray-900 mb-3">사용자 정보</h3>
        @if($data->account)
        <dl class="grid grid-cols-2 gap-4">
            <div>
                <dt class="text-xs font-medium text-gray-500">이름</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $data->account->name }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium text-gray-500">이메일</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $data->account->email }}</dd>
            </div>
        </dl>
        @else
        <p class="text-sm text-gray-500 italic">사용자 정보를 찾을 수 없습니다.</p>
        @endif
    </div>

    {{-- 인증 방법 수정 --}}
    <div>
        <label for="method" class="block text-xs font-medium text-gray-700 mb-1">
            인증 방법
        </label>
        <select wire:model="form.method"
                id="method"
                class="w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
            <option value="totp">TOTP (앱 인증)</option>
            <option value="sms">SMS</option>
            <option value="email">이메일</option>
        </select>
        @error('form.method')
        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
        
        <div class="mt-2 text-xs text-gray-500">
            @if($data->method === 'totp')
            <p>현재 Google Authenticator, Microsoft Authenticator 등의 앱을 사용 중입니다.</p>
            @elseif($data->method === 'sms')
            <p>현재 SMS로 인증 코드를 받고 있습니다.</p>
            @elseif($data->method === 'email')
            <p>현재 이메일로 인증 코드를 받고 있습니다.</p>
            @endif
        </div>
    </div>

    {{-- 활성화 상태 --}}
    <div>
        <label class="flex items-center">
            <input type="checkbox"
                   wire:model="form.enabled"
                   class="h-4 w-4 text-blue-600 focus:ring-1 focus:ring-blue-500 border-gray-300 rounded">
            <span class="ml-2 text-xs font-medium text-gray-700">
                2단계 인증 활성화
            </span>
        </label>
        @if($data->enabled)
        <p class="mt-1 text-xs text-gray-500">
            활성화 일시: {{ \Carbon\Carbon::parse($data->enabled_at)->format('Y년 m월 d일 H:i') }}
        </p>
        @else
        <p class="mt-1 text-xs text-yellow-600">
            ⚠️ 현재 2단계 인증이 비활성화되어 있습니다.
        </p>
        @endif
    </div>

    {{-- 보안 정보 --}}
    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
        <h3 class="text-sm font-medium text-yellow-800 mb-2">보안 정보</h3>
        
        <dl class="space-y-2">
            @if($data->last_used_at)
            <div>
                <dt class="text-xs font-medium text-gray-600">마지막 사용</dt>
                <dd class="text-xs text-gray-900">
                    {{ \Carbon\Carbon::parse($data->last_used_at)->format('Y년 m월 d일 H:i') }}
                    ({{ \Carbon\Carbon::parse($data->last_used_at)->diffForHumans() }})
                </dd>
            </div>
            @endif
            
            <div>
                <dt class="text-xs font-medium text-gray-600">실패 시도</dt>
                <dd class="text-xs text-gray-900">
                    @if($data->failed_attempts > 0)
                    <span class="font-semibold {{ $data->failed_attempts >= 5 ? 'text-red-600' : 'text-yellow-600' }}">
                        {{ $data->failed_attempts }}회
                    </span>
                    @if($data->failed_attempts >= 3)
                    <button wire:click="resetFailedAttempts"
                            class="ml-2 text-xs text-blue-600 hover:text-blue-800 underline">
                        초기화
                    </button>
                    @endif
                    @else
                    <span class="text-green-600">없음</span>
                    @endif
                </dd>
            </div>
            
            @if(isset($form['recovery_codes_count']))
            <div>
                <dt class="text-xs font-medium text-gray-600">복구 코드</dt>
                <dd class="text-xs text-gray-900">
                    {{ $form['recovery_codes_display'] ?? '설정되지 않음' }}
                    <button wire:click="resetRecoveryCodes"
                            onclick="return confirm('복구 코드를 재생성하시겠습니까? 기존 코드는 무효화됩니다.')"
                            class="ml-2 text-xs text-blue-600 hover:text-blue-800 underline">
                        재생성
                    </button>
                </dd>
            </div>
            @endif
        </dl>
    </div>

    {{-- 복구 코드 표시 (재생성된 경우) --}}
    @if(session()->has('recovery_codes'))
    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
        <h3 class="text-sm font-medium text-green-800 mb-2">🔐 새로운 복구 코드</h3>
        <p class="text-xs text-green-700 mb-3">
            아래 코드를 안전한 곳에 보관하세요. 각 코드는 한 번만 사용할 수 있습니다.
        </p>
        <div class="grid grid-cols-2 gap-2 font-mono text-xs">
            @foreach(session('recovery_codes') as $code)
            <div class="bg-white px-2 py-1 rounded border border-green-300">{{ $code }}</div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- 변경 내역 경고 --}}
    @if($data->method === 'totp' && isset($form['method']) && $form['method'] !== 'totp')
    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
        <p class="text-xs text-red-700">
            <strong>경고:</strong> 인증 방법을 변경하면 기존 TOTP 설정이 초기화됩니다.
            사용자가 다시 설정해야 합니다.
        </p>
    </div>
    @endif
</div>