@extends('jiny-auth::layouts.centered')

@section('title', '약관 동의 - Jiny Auth')

@section('content')
    @if (session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if (isset($errors) && $errors->any())
        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">약관 동의 오류</h3>
                    <div class="mt-2 text-sm text-red-700">
                        <ul class="list-disc pl-5 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <form method="POST"
        action="{{ route('regist.terms.store') }}"
        class="space-y-6"
        id="termsForm">

        @csrf

        <!-- 필수 약관 -->
        @if(isset($requiredTerms) && $requiredTerms->count() > 0)
        <div class="mb-4">
            <h6 class="text-red-600 font-semibold mb-2">필수 약관 (모두 동의해야 합니다)</h6>
            @foreach($requiredTerms as $term)
            <div class="flex items-start mb-2">
                <input class="required-term w-4 h-4 mt-1 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500" type="checkbox" name="agreed_terms[]" value="{{ $term->id }}" id="term_{{ $term->id }}" required>
                <label for="term_{{ $term->id }}" class="ml-2 block text-sm text-gray-900 dark:text-gray-300">
                    <strong>{{ $term->title }}</strong>
                    <button type="button" class="ml-2 text-xs text-blue-600 underline hover:text-blue-800" onclick="showTermDetail({{ $term->id }})">상세보기</button>
                </label>
            </div>
            @endforeach
        </div>
        @endif

        <!-- 선택 약관 -->
        @if(isset($optionalTerms) && $optionalTerms->count() > 0)
        <div class="mb-4">
            <h6 class="text-blue-600 font-semibold mb-2">선택 약관 (선택적으로 동의할 수 있습니다)</h6>
            @foreach($optionalTerms as $term)
            <div class="flex items-start mb-2">
                <input class="optional-term w-4 h-4 mt-1 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500" type="checkbox" name="agreed_terms[]" value="{{ $term->id }}" id="term_{{ $term->id }}">
                <label for="term_{{ $term->id }}" class="ml-2 block text-sm text-gray-900 dark:text-gray-300">
                    {{ $term->title }}
                    <button type="button" class="ml-2 text-xs text-blue-600 underline hover:text-blue-800" onclick="showTermDetail({{ $term->id }})">상세보기</button>
                </label>
            </div>
            @endforeach
        </div>
        @endif

        <!-- 전체 동의 -->
        <div class="mb-4">
            <div class="flex items-center">
                <input class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500" type="checkbox" id="agreeAll">
                <label for="agreeAll" class="ml-2 text-sm text-gray-900 dark:text-gray-300 font-medium">모든 약관에 동의합니다</label>
            </div>
        </div>

        <!-- 버튼 -->
        <button type="submit"
            class="w-full text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800 transition duration-200">
            동의하고 회원가입 진행
        </button>
    </form>

    <!-- 하단 링크 -->
    <div class="text-center mt-4">
        <p class="text-gray-500 dark:text-gray-400 text-sm">
            이미 계정이 있으신가요?
            <a href="{{ route('login') }}" class="text-blue-600 hover:text-blue-500 dark:text-blue-400 dark:hover:text-blue-300">로그인</a>
        </p>
    </div>

    <script>

    document.addEventListener('DOMContentLoaded', function() {
        console.log("test2");
        const form = document.getElementById('termsForm');
        console.log(form);

        // 약관 동의 폼 제출 처리
        form.addEventListener('submit', function(e) {
        e.preventDefault();

        console.log("test3");

        // 체크된 약관 ID 수집
        const checkedTerms = Array.from(document.querySelectorAll('input[name="agreed_terms[]"]:checked'))
            .map(checkbox => checkbox.value);

        // AJAX 요청
        console.log("{{ route('regist.terms.store') }}");
        const token = document.querySelector('input[name="_token"]').value;
        console.log(token);

        fetch("{{ route('regist.terms.store') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
            },
            body: JSON.stringify({
                agreed_terms: checkedTerms
            })
        })
        .then(response => {
            if (!response.ok) throw new Error('서버 오류');
            return response.json();
        })
        .then(data => {
            if(data.status === 'success' || data.status === 'ok') {
                window.location.href = "{{ route('regist') }}";
            } else {
                alert(data.message || '알 수 없는 오류');
            }
        })
        .catch(error => {
            console.error('약관 동의 처리 중 오류가 발생했습니다:', error);
            alert('약관 동의 처리 중 오류가 발생했습니다. 다시 시도해주세요.');
        });
    });
    });


    </script>
@endsection

@section('scripts')
{{-- <!-- 약관 상세 모달 -->
<div id="termModal" class="fixed z-50 inset-0 overflow-y-auto hidden">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-900 opacity-50"></div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg overflow-hidden shadow-xl transform transition-all max-w-lg w-full">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                <h5 class="text-lg font-semibold text-gray-900 dark:text-white" id="termModalLabel">약관 상세</h5>
                <button type="button" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200" onclick="closeTermModal()">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="p-6 overflow-y-auto max-h-96" id="termModalBody">
                <!-- 약관 내용 -->
            </div>
            <div class="p-4 border-t border-gray-200 dark:border-gray-700 text-right">
                <button type="button" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700" onclick="closeTermModal()">닫기</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const requiredTerms = document.querySelectorAll('.required-term');
    const optionalTerms = document.querySelectorAll('.optional-term');
    const agreeAllCheckbox = document.getElementById('agreeAll');
    const submitBtn = document.getElementById('submitBtn');
    const termsForm = document.getElementById('termsForm');

    // 약관이 하나도 없는 경우 바로 회원가입 페이지로 이동
    @if(($requiredTerms->count() ?? 0) === 0 && ($optionalTerms->count() ?? 0) === 0)
        window.location.href = "{{ route('regist') }}";
    @endif

    function checkRequiredTerms() {
        const allRequiredChecked = Array.from(requiredTerms).every(checkbox => checkbox.checked);
        submitBtn.disabled = !allRequiredChecked;
        return allRequiredChecked;
    }

    agreeAllCheckbox.addEventListener('change', function() {
        const allCheckboxes = document.querySelectorAll('input[name="agreed_terms[]"]');
        allCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        checkRequiredTerms();

        // 약관이 없거나 모두 체크된 경우 바로 회원가입 이동
        if (allCheckboxes.length === 0 || Array.from(allCheckboxes).every(cb => cb.checked)) {
            window.location.href = "{{ route('regist') }}";
        }
    });

    function handleTermCheckbox() {
        const allCheckboxes = document.querySelectorAll('input[name="agreed_terms[]"]');
        const allChecked = Array.from(allCheckboxes).every(checkbox => checkbox.checked);
        agreeAllCheckbox.checked = allChecked;
        checkRequiredTerms();
    }

    requiredTerms.forEach(checkbox => {
        checkbox.addEventListener('change', handleTermCheckbox);
    });

    optionalTerms.forEach(checkbox => {
        checkbox.addEventListener('change', handleTermCheckbox);
    });

    checkRequiredTerms();

    // 폼 제출 시 필수 약관 체크 확인
    termsForm.addEventListener('submit', function(e) {
        if (requiredTerms.length > 0 && !checkRequiredTerms()) {
            e.preventDefault();
            alert('필수 약관에 모두 동의해 주세요.');
        }
    });
});

function showTermDetail(termId) {
    fetch(`/auth/regist/terms/${termId}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('termModalLabel').textContent = data.title;
            document.getElementById('termModalBody').innerHTML = `
                <div class="mb-3">
                    <span class="inline-block px-2 py-1 rounded text-xs font-semibold ${data.type === 'required' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800'}">
                        ${data.type === 'required' ? '필수' : '선택'}
                    </span>
                </div>
                <div style="max-height: 400px; overflow-y: auto;">
                    ${data.content.replace(/\n/g, '<br>')}
                </div>
            `;
            document.getElementById('termModal').classList.remove('hidden');
        })
        .catch(error => {
            alert('약관 정보를 불러오는데 실패했습니다.');
        });
}

function closeTermModal() {
    document.getElementById('termModal').classList.add('hidden');
}
</script> --}}
@endsection
