@extends('jiny-auth::layouts.resource.create')

@section('title', '사용자 샤딩 설정 생성')
@section('description', '새로운 사용자 샤딩 설정을 생성합니다.')

@section('heading')
<div class="w-full">
    <div class="sm:flex sm:items-end justify-between">
        <div class="sm:flex-auto">
            <h1 class="text-2xl font-semibold text-gray-900">사용자 샤딩 설정 생성</h1>
            <p class="mt-2 text-base text-gray-700">새로운 사용자 샤딩 설정을 생성합니다.</p>
        </div>
        <div class="mt-4 sm:mt-0 flex gap-2">
            <x-ui::button-secondary href="{{ route('admin.auth.users.index') }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                </svg>
                사용자 관리
            </x-ui::button-secondary>
            <x-ui::button-light href="{{ route($route.'index') }}">
                <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                샤딩 목록
            </x-ui::button-light>
        </div>
    </div>
</div>
@endsection

@section('form')
    {{-- 기본 설정 --}}
    <x-ui::form-section title="기본 설정" description="샤딩 설정의 기본 정보를 입력하세요.">
        <div class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
            <div class="sm:col-span-3">
                <label for="table_name" class="block text-sm font-medium text-gray-700 mb-1">
                    테이블 이름 <span class="text-red-500 ml-1" aria-label="필수 항목">*</span>
                </label>
                <div class="mt-2 relative">
                    <input type="text" name="table_name" id="table_name"
                           class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm @error('table_name') outline-red-500 @enderror"
                           placeholder="예: users" value="{{ old('table_name') }}" required>
                </div>
                @error('table_name')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-2 text-sm text-gray-500">샤딩할 테이블의 이름을 입력하세요.</p>
            </div>

            <div class="sm:col-span-3">
                <label for="shard_count" class="block text-sm font-medium text-gray-700 mb-1">
                    샤드 개수 <span class="text-red-500 ml-1" aria-label="필수 항목">*</span>
                </label>
                <div class="mt-2 relative">
                    <input type="number" name="shard_count" id="shard_count" min="1" max="1000"
                           class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm @error('shard_count') outline-red-500 @enderror"
                           placeholder="예: 100" value="{{ old('shard_count') }}" required>
                </div>
                @error('shard_count')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-2 text-sm text-gray-500">생성할 샤드 테이블의 개수를 입력하세요. (1-1000)</p>
            </div>

            <div class="sm:col-span-3">
                <label for="shard_key" class="block text-sm font-medium text-gray-700 mb-1">
                    샤딩 키 <span class="text-red-500 ml-1" aria-label="필수 항목">*</span>
                </label>
                <div class="mt-2 relative">
                    <input type="text" name="shard_key" id="shard_key"
                           class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm @error('shard_key') outline-red-500 @enderror"
                           placeholder="예: id" value="{{ old('shard_key', 'id') }}" required>
                </div>
                @error('shard_key')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-2 text-sm text-gray-500">샤딩에 사용할 컬럼 이름을 입력하세요.</p>
            </div>

            <div class="sm:col-span-3">
                <label for="shard_strategy" class="block text-sm font-medium text-gray-700 mb-1">
                    샤딩 전략 <span class="text-red-500 ml-1" aria-label="필수 항목">*</span>
                </label>
                <div class="mt-2 relative">
                    <select name="shard_strategy" id="shard_strategy"
                            class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm @error('shard_strategy') outline-red-500 @enderror">
                        <option value="hash" {{ old('shard_strategy', 'hash') === 'hash' ? 'selected' : '' }}>해시 기반 (Hash-based)</option>
                        <option value="range" {{ old('shard_strategy') === 'range' ? 'selected' : '' }}>범위 기반 (Range-based)</option>
                    </select>
                </div>
                @error('shard_strategy')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-2 text-sm text-gray-500">샤딩 전략을 선택하세요. 해시는 균등 분배, 범위는 순차적 분배입니다.</p>
            </div>

            <div class="sm:col-span-6">
                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">설명</label>
                <div class="mt-2 relative">
                    <textarea name="description" id="description" rows="3"
                              class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm @error('description') outline-red-500 @enderror"
                              placeholder="샤딩 설정에 대한 설명을 입력하세요.">{{ old('description') }}</textarea>
                </div>
                @error('description')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-2 text-sm text-gray-500">선택사항: 샤딩 설정에 대한 설명을 입력하세요.</p>
            </div>
        </div>
    </x-ui::form-section>

    {{-- 샤딩 전략 설명 --}}
    <x-ui::form-section class="mt-6" title="샤딩 전략 설명" description="각 샤딩 전략의 특징을 확인하세요.">
        <div class="space-y-4">
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800">해시 기반 (Hash-based)</h3>
                        <div class="mt-2 text-sm text-blue-700">
                            <p>데이터의 해시값을 기반으로 샤드를 결정합니다. 데이터가 균등하게 분산되어 성능이 일정합니다.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-green-800">범위 기반 (Range-based)</h3>
                        <div class="mt-2 text-sm text-green-700">
                            <p>데이터의 범위를 기반으로 샤드를 결정합니다. 순차적 데이터 조회에 유리하지만 분산이 불균등할 수 있습니다.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-ui::form-section>

    {{-- 주의사항 --}}
    <x-ui::form-section class="mt-6" title="주의사항" description="샤딩 설정 생성 시 주의해야 할 사항들입니다.">
        <div class="rounded-md bg-yellow-50 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800">중요한 주의사항</h3>
                    <div class="mt-2 text-sm text-yellow-700">
                        <ul class="list-disc pl-5 space-y-1">
                            <li>샤딩 설정을 생성하면 기존 설정이 비활성화됩니다.</li>
                            <li>샤드 테이블들이 자동으로 생성됩니다.</li>
                            <li>각 샤드 테이블에는 중복 방지를 위한 <code class="bg-gray-100 px-1 rounded">shard_uuid</code> 컬럼이 추가됩니다.</li>
                            <li>테이블 이름은 정확히 입력해야 합니다.</li>
                            <li>샤딩 키는 해당 테이블에 존재하는 컬럼이어야 합니다.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </x-ui::form-section>

    {{-- 추가 작업 --}}
    <x-ui::form-section class="mt-6" title="추가 작업" description="샤딩 설정 생성 후 수행할 수 있는 추가 작업들입니다.">
        <div class="mt-6 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <button type="submit" formaction="{{ route('admin.auth.sharding.recreate') }}"
                        class="inline-flex justify-center py-2 px-4 border border-yellow-300 shadow-sm text-sm font-medium rounded-md text-yellow-700 bg-white hover:bg-yellow-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500"
                        onclick="return confirm('기존 설정을 삭제하고 새로 생성하시겠습니까?')">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    다시 생성
                </button>
            </div>
        </div>
    </x-ui::form-section>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const tableNameInput = document.getElementById('table_name');
        const shardKeyInput = document.getElementById('shard_key');
        const form = document.querySelector('form');

        // 테이블 이름이 변경되면 샤딩 키를 자동으로 설정
        tableNameInput.addEventListener('input', function() {
            const tableName = this.value.trim();
            if (tableName && !shardKeyInput.value) {
                shardKeyInput.value = 'id'; // 기본값
            }
        });

        // 샤딩 전략 변경 시 설명 업데이트
        const strategySelect = document.getElementById('shard_strategy');
        strategySelect.addEventListener('change', function() {
            const strategy = this.value;
            // 필요시 전략별 추가 설명을 표시할 수 있습니다
        });

        // 폼 제출 처리
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(form);
            const submitButton = form.querySelector('button[type="submit"]');
            const originalText = submitButton.innerHTML;

            // 버튼 비활성화 및 로딩 표시
            submitButton.disabled = true;
            submitButton.innerHTML = `
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                생성 중...
            `;

            // AJAX 요청
            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // 성공 메시지 표시
                    showSuccessMessage(data.message, data.data);

                    // 3초 후 목록 페이지로 이동
                    setTimeout(() => {
                        window.location.href = '{{ route($route."index") }}';
                    }, 3000);
                } else {
                    // 오류 메시지 표시
                    showErrorMessage(data.message, data.errors);

                    // 버튼 복원
                    submitButton.disabled = false;
                    submitButton.innerHTML = originalText;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showErrorMessage('요청 처리 중 오류가 발생했습니다.');

                // 버튼 복원
                submitButton.disabled = false;
                submitButton.innerHTML = originalText;
            });
        });

        // 성공 메시지 표시 함수
        function showSuccessMessage(message, data) {
            const successDiv = document.createElement('div');
            successDiv.className = 'fixed top-4 right-4 z-50 bg-green-50 border border-green-200 rounded-md p-4 max-w-md';
            successDiv.innerHTML = `
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-green-800">성공!</h3>
                        <div class="mt-2 text-sm text-green-700">
                            <p>${message}</p>
                            ${data ? `
                                <div class="mt-3 text-xs">
                                    <p><strong>생성된 샤드:</strong> ${data.table_stats.created_tables}개</p>
                                    <p><strong>기존 샤드:</strong> ${data.table_stats.existing_tables}개</p>
                                    <p><strong>총 샤드:</strong> ${data.table_stats.total_shards}개</p>
                                </div>
                            ` : ''}
                        </div>
                    </div>
                </div>
            `;

            document.body.appendChild(successDiv);

            // 5초 후 자동 제거
            setTimeout(() => {
                successDiv.remove();
            }, 5000);
        }

        // 오류 메시지 표시 함수
        function showErrorMessage(message, errors = null) {
            const errorDiv = document.createElement('div');
            errorDiv.className = 'fixed top-4 right-4 z-50 bg-red-50 border border-red-200 rounded-md p-4 max-w-md';

            let errorHtml = `
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">오류 발생</h3>
                        <div class="mt-2 text-sm text-red-700">
                            <p>${message}</p>
            `;

            if (errors) {
                errorHtml += '<ul class="mt-2 list-disc list-inside">';
                Object.keys(errors).forEach(field => {
                    errors[field].forEach(error => {
                        errorHtml += `<li>${error}</li>`;
                    });
                });
                errorHtml += '</ul>';
            }

            errorHtml += `
                        </div>
                    </div>
                </div>
            `;

            errorDiv.innerHTML = errorHtml;
            document.body.appendChild(errorDiv);

            // 5초 후 자동 제거
            setTimeout(() => {
                errorDiv.remove();
            }, 5000);
        }
    });
</script>

@endsection



