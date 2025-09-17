@extends('jiny-auth::layouts.resource.create')
@section('title', '약관 등록')
@section('description', '새로운 약관 정보를 입력하고 등록하세요.')

{{-- 페이지 상태 스크립트 --}}
@section('page-script', 'create')

@section('heading')
    <section class="w-full">
        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
            <article class="flex-1 min-w-0">
                <h1 class="text-2xl font-semibold text-gray-900">약관 등록</h1>
                <p class="mt-2 text-base text-gray-700 leading-relaxed">새로운 약관 정보를 입력하고 등록하세요. 약관명, 타입, 내용, 버전 등의 정보를 관리할 수 있습니다.</p>
            </article>
            <aside class="flex-shrink-0">
                <x-ui::button-light href="{{ route($route . 'index') }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    약관 목록
                </x-ui::button-light>
            </aside>
        </div>
    </section>
@endsection

@section('content')
    <div class="pt-2 pb-4">
        <form action="{{ route($route.'store') }}" method="POST" class="mt-6" id="create-form" data-list-url="{{ route($route.'index') }}">
            @csrf
            <div class="space-y-12">
                <x-ui::form-section
                    title="기본 정보"
                    description="약관의 기본 정보를 입력하세요.">
                    <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6 md:col-span-2">
                        <div class="sm:col-span-6">
                            <label for="title" class="block text-sm font-medium text-gray-700 mb-1">
                                약관명 <span class="text-red-500 ml-1" aria-label="필수 항목">*</span>
                            </label>
                            <div class="mt-2 relative">
                                <input type="text" name="title" id="title" value="{{ old('title') }}"
                                    class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm {{ $errors->has('title') ? 'outline-red-300 focus:outline-red-500' : '' }}"
                                    required aria-describedby="title-error" placeholder="예: 서비스 이용약관" />
                                @if($errors->has('title'))
                                    <div id="title-error" class="mt-1 text-sm text-red-600">{{ $errors->first('title') }}</div>
                                @endif
                            </div>
                        </div>
                        <div class="sm:col-span-3">
                            <label for="type" class="block text-sm font-medium text-gray-700 mb-1">
                                약관 타입 <span class="text-red-500 ml-1" aria-label="필수 항목">*</span>
                            </label>
                            <div class="mt-2 relative">
                                <select name="type" id="type"
                                    class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm {{ $errors->has('type') ? 'outline-red-300 focus:outline-red-500' : '' }}"
                                    required aria-describedby="type-error">
                                    <option value="optional" {{ old('type', 'optional') == 'optional' ? 'selected' : '' }}>선택</option>
                                    <option value="required" {{ old('type') == 'required' ? 'selected' : '' }}>필수</option>
                                </select>
                                @if($errors->has('type'))
                                    <div id="type-error" class="mt-1 text-sm text-red-600">{{ $errors->first('type') }}</div>
                                @endif
                            </div>
                        </div>
                        <div class="sm:col-span-3">
                            <label for="version" class="block text-sm font-medium text-gray-700 mb-1">버전</label>
                            <div class="mt-2 relative">
                                <input type="text" name="version" id="version" value="{{ old('version', '1.0') }}"
                                    class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm {{ $errors->has('version') ? 'outline-red-300 focus:outline-red-500' : '' }}"
                                    aria-describedby="version-error" placeholder="예: 1.0" />
                                @if($errors->has('version'))
                                    <div id="version-error" class="mt-1 text-sm text-red-600">{{ $errors->first('version') }}</div>
                                @endif
                            </div>
                        </div>
                    </div>
                </x-ui::form-section>

                <x-ui::form-section
                    title="약관 내용"
                    description="약관의 상세 내용을 입력하세요.">
                    <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6 md:col-span-2">
                        <div class="sm:col-span-6">
                            <label for="content" class="block text-sm font-medium text-gray-700 mb-1">
                                약관 내용 <span class="text-red-500 ml-1" aria-label="필수 항목">*</span>
                            </label>
                            <div class="mt-2 relative">
                                <textarea name="content" id="content" rows="8"
                                    class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm {{ $errors->has('content') ? 'outline-red-300 focus:outline-red-500' : '' }}"
                                    required aria-describedby="content-error" placeholder="약관 내용을 입력하세요."
                                    data-quill-editor>{{ old('content') }}</textarea>
                                @if($errors->has('content'))
                                    <div id="content-error" class="mt-1 text-sm text-red-600">{{ $errors->first('content') }}</div>
                                @endif
                            </div>
                        </div>
                        <div class="sm:col-span-6">
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">설명</label>
                            <div class="mt-2 relative">
                                <input type="text" name="description" id="description" value="{{ old('description') }}"
                                    class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm {{ $errors->has('description') ? 'outline-red-300 focus:outline-red-500' : '' }}"
                                    aria-describedby="description-error" placeholder="관리자용 설명(선택)" />
                                @if($errors->has('description'))
                                    <div id="description-error" class="mt-1 text-sm text-red-600">{{ $errors->first('description') }}</div>
                                @endif
                            </div>
                        </div>
                    </div>
                </x-ui::form-section>

                <x-ui::form-section
                    title="설정 정보"
                    description="약관의 표시 및 활성화 설정을 관리하세요.">
                    <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6 md:col-span-2">
                        <div class="sm:col-span-3">
                            <label for="display_order" class="block text-sm font-medium text-gray-700 mb-1">표시 순서</label>
                            <div class="mt-2 relative">
                                <input type="number" name="display_order" id="display_order" value="{{ old('display_order', 0) }}"
                                    class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm {{ $errors->has('display_order') ? 'outline-red-300 focus:outline-red-500' : '' }}"
                                    aria-describedby="display_order-error" placeholder="0" />
                                @if($errors->has('display_order'))
                                    <div id="display_order-error" class="mt-1 text-sm text-red-600">{{ $errors->first('display_order') }}</div>
                                @endif
                            </div>
                        </div>
                        <div class="sm:col-span-3">
                            <label for="is_active" class="block text-sm font-medium text-gray-700 mb-1">활성화</label>
                            <div class="mt-2 relative">
                                <select name="is_active" id="is_active"
                                    class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm {{ $errors->has('is_active') ? 'outline-red-300 focus:outline-red-500' : '' }}"
                                    aria-describedby="is_active-error">
                                    <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>활성</option>
                                    <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>비활성</option>
                                </select>
                                @if($errors->has('is_active'))
                                    <div id="is_active-error" class="mt-1 text-sm text-red-600">{{ $errors->first('is_active') }}</div>
                                @endif
                            </div>
                        </div>
                        <div class="sm:col-span-3">
                            <label for="effective_date" class="block text-sm font-medium text-gray-700 mb-1">시행일</label>
                            <div class="mt-2 relative">
                                <input type="date" name="effective_date" id="effective_date" value="{{ old('effective_date') }}"
                                    class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm {{ $errors->has('effective_date') ? 'outline-red-300 focus:outline-red-500' : '' }}"
                                    aria-describedby="effective_date-error" />
                                @if($errors->has('effective_date'))
                                    <div id="effective_date-error" class="mt-1 text-sm text-red-600">{{ $errors->first('effective_date') }}</div>
                                @endif
                            </div>
                        </div>
                        <div class="sm:col-span-3">
                            <label for="expiry_date" class="block text-sm font-medium text-gray-700 mb-1">만료일</label>
                            <div class="mt-2 relative">
                                <input type="date" name="expiry_date" id="expiry_date" value="{{ old('expiry_date') }}"
                                    class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm {{ $errors->has('expiry_date') ? 'outline-red-300 focus:outline-red-500' : '' }}"
                                    aria-describedby="expiry_date-error" />
                                @if($errors->has('expiry_date'))
                                    <div id="expiry_date-error" class="mt-1 text-sm text-red-600">{{ $errors->first('expiry_date') }}</div>
                                @endif
                            </div>
                        </div>
                    </div>
                </x-ui::form-section>
            </div>

            <!-- 제어 버튼 -->
            <div class="mt-6 flex items-center justify-end gap-x-6">
                <x-ui::button-light href="{{ route($route.'index') }}">취소</x-ui::button-light>
                <x-ui::button-primary type="button" id="submitCreateAjax">
                    <span class="inline-flex items-center">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white hidden" id="loadingIcon" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span id="submitText">등록</span>
                    </span>
                </x-ui::button-primary>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <!-- Quill.js CSS -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">

    <!-- Quill.js JavaScript -->
    <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>

    <style>
        /* Quill 에디터 커스텀 스타일 */
        .quill-editor-container {
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            background: white;
            min-height: 300px;
            margin-bottom: 1rem;
        }

        .quill-editor-container .ql-toolbar {
            border-top: none;
            border-left: none;
            border-right: none;
            border-bottom: 1px solid #d1d5db;
            background-color: #f9fafb;
            border-radius: 0.375rem 0.375rem 0 0;
        }

        .quill-editor-container .ql-container {
            border: none;
            font-size: 0.875rem;
            line-height: 1.5;
            border-radius: 0 0 0.375rem 0.375rem;
        }

        .quill-editor-container .ql-editor {
            min-height: 250px;
            padding: 1rem;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }

        .quill-editor-container .ql-editor.ql-blank::before {
            color: #9ca3af;
            font-style: italic;
            left: 1rem;
        }

        /* 에러 상태 스타일 */
        .quill-editor-container.has-error {
            border-color: #ef4444;
        }

        .quill-editor-container.has-error .ql-toolbar {
            border-bottom-color: #ef4444;
        }

        /* 툴바 버튼 스타일 개선 */
        .quill-editor-container .ql-toolbar button {
            margin: 0 0.125rem;
        }

        .quill-editor-container .ql-toolbar button:hover {
            color: #3b82f6;
        }

        /* 에디터 내용 스타일 */
        .quill-editor-container .ql-editor h1,
        .quill-editor-container .ql-editor h2,
        .quill-editor-container .ql-editor h3 {
            margin-top: 1rem;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .quill-editor-container .ql-editor p {
            margin-bottom: 0.75rem;
        }

        .quill-editor-container .ql-editor blockquote {
            border-left: 4px solid #3b82f6;
            padding-left: 1rem;
            margin: 1rem 0;
            font-style: italic;
            color: #6b7280;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 기존 textarea 숨기기
            const textarea = document.getElementById('content');
            if (textarea) {
                textarea.style.display = 'none';

                // Quill 에디터 컨테이너 생성
                const container = document.createElement('div');
                container.id = 'quill-editor';
                container.className = 'quill-editor-container';

                // 에러 상태 확인
                if (textarea.classList.contains('outline-red-300') || textarea.classList.contains('focus:outline-red-500')) {
                    container.classList.add('has-error');
                }

                // textarea 다음에 컨테이너 삽입
                textarea.parentNode.insertBefore(container, textarea.nextSibling);

                // Quill 에디터 초기화
                const quill = new Quill('#quill-editor', {
                    theme: 'snow',
                    modules: {
                        toolbar: [
                            ['bold', 'italic', 'underline'],
                            ['blockquote'],
                            [{ 'header': [1, 2, 3, false] }],
                            [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                            ['link'],
                            ['clean']
                        ]
                    },
                    placeholder: '약관 내용을 입력하세요.',
                    bounds: '#quill-editor'
                });

                // 기존 내용 설정
                if (textarea.value) {
                    quill.root.innerHTML = textarea.value;
                }

                // Quill 내용을 textarea에 동기화하는 함수
                const syncQuillContent = function() {
                    textarea.value = quill.root.innerHTML;
                };

                // 내용 변경 시 textarea 업데이트
                quill.on('text-change', function(delta, oldDelta, source) {
                    if (source === 'user') {
                        syncQuillContent();

                        // change 이벤트 발생
                        const event = new Event('change', { bubbles: true });
                        textarea.dispatchEvent(event);

                        // 에러 상태 제거 (사용자가 내용을 수정하면)
                        container.classList.remove('has-error');
                    }
                });

                // CreateState.js와 호환을 위한 폼 제출 이벤트 처리
                const form = document.getElementById('create-form');
                if (form) {
                    // 폼 제출 시 Quill 에디터 내용을 textarea에 동기화
                    form.addEventListener('submit', syncQuillContent);

                    // submitCreateAjax 버튼 클릭 시에도 동기화
                    const submitBtn = document.getElementById('submitCreateAjax');
                    if (submitBtn) {
                        submitBtn.addEventListener('click', syncQuillContent);
                        submitBtn.addEventListener('mousedown', syncQuillContent);
                    }
                }

                // 에디터 포커스 시 에러 상태 제거
                quill.on('focus', function() {
                    container.classList.remove('has-error');
                });
            }
        });
    </script>
@endpush
