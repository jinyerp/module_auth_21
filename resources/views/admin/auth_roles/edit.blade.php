{{-- 폼 필드만 포함 (제출, 취소 버튼은 Livewire 컴포넌트에서 처리) --}}
<div class="space-y-6">
    {{-- 시스템 역할 경고 --}}
    @if(in_array($form['slug'] ?? '', ['super-admin', 'admin', 'user']))
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-xs text-yellow-700">
                        시스템 역할입니다. 슬러그 변경이 제한되며, 삭제할 수 없습니다.
                    </p>
                </div>
            </div>
        </div>
    @endif
    
    {{-- 역할명 --}}
    <div>
        <label for="name" class="block text-xs font-medium text-gray-700 mb-1">
            역할명 <span class="text-red-500">*</span>
        </label>
        <input type="text" 
               id="name"
               wire:model="form.name"
               class="w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500 @error('form.name') border-red-300 @enderror"
               placeholder="예: 관리자, 편집자, 사용자">
        @error('form.name')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>
    
    {{-- 슬러그 --}}
    <div>
        <label for="slug" class="block text-xs font-medium text-gray-700 mb-1">
            슬러그 <span class="text-red-500">*</span>
        </label>
        <input type="text" 
               id="slug"
               wire:model="form.slug"
               @if(in_array($form['slug'] ?? '', ['super-admin', 'admin', 'user'])) readonly @endif
               class="w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500 @error('form.slug') border-red-300 @enderror @if(in_array($form['slug'] ?? '', ['super-admin', 'admin', 'user'])) bg-gray-100 @endif"
               placeholder="예: admin, editor, user">
        <p class="mt-1 text-xs text-gray-500">
            @if(in_array($form['slug'] ?? '', ['super-admin', 'admin', 'user']))
                시스템 역할의 슬러그는 변경할 수 없습니다.
            @else
                URL에 사용되는 고유 식별자입니다. 변경 시 주의하세요.
            @endif
        </p>
        @error('form.slug')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>
    
    {{-- 설명 --}}
    <div>
        <label for="description" class="block text-xs font-medium text-gray-700 mb-1">
            설명
        </label>
        <textarea id="description"
                  wire:model="form.description"
                  rows="3"
                  class="w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500 @error('form.description') border-red-300 @enderror"
                  placeholder="역할에 대한 설명을 입력하세요."></textarea>
        @error('form.description')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>
    
    {{-- 권한 설정 --}}
    <div>
        <label for="permissions" class="block text-xs font-medium text-gray-700 mb-1">
            권한 설정
        </label>
        <div class="border border-gray-300 rounded-md p-3">
            <div class="space-y-2">
                <p class="text-xs text-gray-500 mb-2">JSON 형식으로 권한을 정의하거나, 아래 체크박스를 사용하세요.</p>
                
                {{-- 권한 체크박스 그리드 --}}
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    @php
                        $availablePermissions = [
                            'users.view' => '사용자 보기',
                            'users.create' => '사용자 생성',
                            'users.edit' => '사용자 수정',
                            'users.delete' => '사용자 삭제',
                            'roles.view' => '역할 보기',
                            'roles.create' => '역할 생성',
                            'roles.edit' => '역할 수정',
                            'roles.delete' => '역할 삭제',
                            'settings.view' => '설정 보기',
                            'settings.edit' => '설정 수정',
                        ];
                    @endphp
                    
                    @foreach($availablePermissions as $key => $label)
                        <div class="flex items-center">
                            <input type="checkbox" 
                                   id="perm_{{ $key }}"
                                   wire:model="form.permissions.{{ $key }}"
                                   class="h-3.5 w-3.5 text-blue-600 focus:ring-1 focus:ring-blue-500 border-gray-200 rounded">
                            <label for="perm_{{ $key }}" class="ml-2 text-xs text-gray-700">
                                {{ $label }}
                            </label>
                        </div>
                    @endforeach
                </div>
                
                {{-- 현재 권한 표시 --}}
                @if(isset($form['permissions']) && is_array($form['permissions']) && count($form['permissions']) > 0)
                    <div class="mt-3 p-2 bg-gray-50 rounded">
                        <p class="text-xs font-medium text-gray-700 mb-1">현재 권한:</p>
                        <div class="flex flex-wrap gap-1">
                            @foreach($form['permissions'] as $key => $value)
                                @if($value)
                                    <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded">
                                        {{ $key }}
                                    </span>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif
                
                {{-- JSON 직접 입력 (고급) --}}
                <details class="mt-3">
                    <summary class="text-xs text-blue-600 cursor-pointer hover:text-blue-800">
                        고급: JSON 직접 입력
                    </summary>
                    <div class="mt-2">
                        <textarea id="permissions_json"
                                  wire:model="form.permissions_json"
                                  rows="4"
                                  class="w-full px-3 py-2 text-xs font-mono border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                                  placeholder='{"users.view": true, "users.edit": false}'>{{ json_encode($form['permissions'] ?? [], JSON_PRETTY_PRINT) }}</textarea>
                        @error('form.permissions_json')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </details>
            </div>
        </div>
    </div>
    
    {{-- 활성화 상태 --}}
    <div>
        <label class="flex items-center">
            <input type="checkbox" 
                   wire:model="form.is_active"
                   class="h-3.5 w-3.5 text-blue-600 focus:ring-1 focus:ring-blue-500 border-gray-200 rounded">
            <span class="ml-2 text-xs text-gray-700">
                활성화 (이 역할을 사용할 수 있도록 합니다)
            </span>
        </label>
    </div>
    
    {{-- 구분선 --}}
    <hr class="border-gray-200">
    
    {{-- 역할 정보 --}}
    <div class="bg-gray-50 rounded-lg p-4">
        <h3 class="text-xs font-medium text-gray-700 mb-3">역할 정보</h3>
        
        <div class="grid grid-cols-2 gap-4">
            {{-- 사용자 수 --}}
            <div>
                <p class="text-xs text-gray-500">이 역할을 가진 사용자</p>
                <p class="text-sm font-medium text-gray-900">
                    {{ $userCount ?? 0 }}명
                </p>
            </div>
            
            {{-- 생성일 --}}
            <div>
                <p class="text-xs text-gray-500">생성일</p>
                <p class="text-sm text-gray-900">
                    {{ isset($data->created_at) ? \Carbon\Carbon::parse($data->created_at)->format('Y-m-d H:i') : '-' }}
                </p>
            </div>
            
            {{-- 수정일 --}}
            <div>
                <p class="text-xs text-gray-500">마지막 수정일</p>
                <p class="text-sm text-gray-900">
                    {{ isset($data->updated_at) ? \Carbon\Carbon::parse($data->updated_at)->format('Y-m-d H:i') : '-' }}
                </p>
            </div>
            
            {{-- 시스템 역할 여부 --}}
            <div>
                <p class="text-xs text-gray-500">타입</p>
                <p class="text-sm">
                    @if(in_array($form['slug'] ?? '', ['super-admin', 'admin', 'user']))
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">
                            시스템 역할
                        </span>
                    @else
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                            커스텀 역할
                        </span>
                    @endif
                </p>
            </div>
        </div>
        
        {{-- 권한 템플릿 --}}
        <div class="mt-4 pt-4 border-t border-gray-200">
            <label class="block text-xs font-medium text-gray-700 mb-2">
                빠른 권한 설정
            </label>
            <div class="grid grid-cols-2 gap-2">
                <button type="button"
                        wire:click="applyTemplate('admin')"
                        class="px-3 py-2 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    관리자 템플릿
                </button>
                <button type="button"
                        wire:click="applyTemplate('editor')"
                        class="px-3 py-2 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    편집자 템플릿
                </button>
                <button type="button"
                        wire:click="applyTemplate('viewer')"
                        class="px-3 py-2 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    뷰어 템플릿
                </button>
                <button type="button"
                        wire:click="clearPermissions"
                        class="px-3 py-2 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    모두 지우기
                </button>
            </div>
        </div>
    </div>
</div>