{{-- 폼 필드만 포함 (제출, 취소 버튼은 Livewire 컴포넌트에서 처리) --}}
<div class="space-y-6">
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
               class="w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500 @error('form.slug') border-red-300 @enderror"
               placeholder="예: admin, editor, user">
        <p class="mt-1 text-xs text-gray-500">URL에 사용되는 고유 식별자입니다. 소문자, 숫자, 하이픈만 사용하세요.</p>
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
                                  placeholder='{"users.view": true, "users.edit": false}'></textarea>
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
                활성화 (이 역할을 즉시 사용할 수 있도록 합니다)
            </span>
        </label>
    </div>
    
    {{-- 구분선 --}}
    <hr class="border-gray-200">
    
    {{-- 추가 옵션 --}}
    <div class="bg-gray-50 rounded-lg p-4">
        <h3 class="text-xs font-medium text-gray-700 mb-3">추가 옵션</h3>
        
        <div class="space-y-3">
            {{-- 기존 역할 복사 --}}
            <div>
                <label for="copy_from" class="block text-xs font-medium text-gray-700 mb-1">
                    기존 역할에서 권한 복사
                </label>
                <select id="copy_from"
                        wire:model="copy_from"
                        wire:change="copyPermissionsFrom"
                        class="w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">선택하세요...</option>
                    @foreach($existingRoles ?? [] as $role)
                        <option value="{{ $role->id }}">{{ $role->name }} ({{ $role->slug }})</option>
                    @endforeach
                </select>
            </div>
            
            {{-- 템플릿 선택 --}}
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">
                    권한 템플릿
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
</div>