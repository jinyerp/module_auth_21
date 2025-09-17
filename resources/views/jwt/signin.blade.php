@extends('jiny-auth::layouts.centered')

@section('title', 'JWT 로그인 - Jiny Auth')

@section('brand-title', 'JWT Auth')
@section('brand-subtitle', '샤딩 시스템과 JWT를 이용한 안전한 로그인')

@section('content')
    <div class="text-center mb-8">
        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 dark:bg-blue-900 mb-4">
            <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
        </div>
        <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">JWT 로그인</h3>
    </div>

    <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg p-8">

        @if (session('success'))
            <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd" />
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
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                clip-rule="evenodd" />
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
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">로그인 오류</h3>
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

        <!-- 알림 메시지 (JavaScript용) -->
        <div id="alert-container" class="mb-4 hidden">
            <div id="alert-box" class="p-4 border rounded-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg id="alert-icon" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"></svg>
                    </div>
                    <div class="ml-3">
                        <p id="alert-message" class="text-sm font-medium"></p>
                    </div>
                    <div class="ml-auto pl-3">
                        <div class="-mx-1.5 -my-1.5">
                            <button id="alert-close" class="inline-flex rounded-md p-1.5 focus:outline-none focus:ring-2 focus:ring-offset-2">
                                <span class="sr-only">닫기</span>
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 로그인 폼 -->
        <form id="login-form" class="space-y-6">
            @csrf

            <div>
                <label for="email" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">이메일</label>
                <input type="email" id="email" name="email"
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                    placeholder="이메일을 입력하세요" autocomplete="username" value="{{ old('email') }}" required />
                <div id="email-error" class="mt-2 text-sm text-red-600 dark:text-red-400 hidden"></div>
            </div>

            <div>
                <label for="password" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">비밀번호</label>
                <input type="password" id="password" name="password"
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                    placeholder="비밀번호를 입력하세요" autocomplete="current-password" required />
                <div id="password-error" class="mt-2 text-sm text-red-600 dark:text-red-400 hidden"></div>
            </div>

            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input id="remember" name="remember" type="checkbox"
                        class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600" />
                    <label for="remember" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">
                        로그인 상태 유지
                    </label>
                </div>
                <a href="{{ route('password.request') }}" class="text-sm font-medium text-blue-600 hover:text-blue-500 dark:text-blue-400 dark:hover:text-blue-300 transition duration-200">
                    비밀번호 찾기
                </a>
            </div>

            <button type="submit" id="login-button"
                class="w-full text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800 transition duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                <span id="login-text" class="flex items-center justify-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                    </svg>
                    로그인
                </span>
                <span id="login-loading" class="flex items-center justify-center hidden">
                    <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    로그인 중...
                </span>
            </button>
        </form>

        <!-- 회원가입 링크 -->
        <div class="mt-8 text-center">
            <p class="text-sm text-gray-600 dark:text-gray-400">
                계정이 없으신가요?
                <a href="{{ route('signup') }}" class="font-medium text-blue-600 hover:text-blue-500 dark:text-blue-400 dark:hover:text-blue-300 transition duration-200">
                    회원가입
                </a>
            </p>
        </div>
    </div>

    <!-- 저작권 정보 -->
    <div class="mt-8 text-xs text-gray-400 text-center">
        <p>본 로그인은 JWT 인증 시스템을 사용합니다. 무단 사용 시 법적 처벌을 받을 수 있습니다.</p>
        <p class="mt-1">© 2025 Jiny Auth. All rights reserved.</p>
    </div>
@endsection

@section('copyright', '© 2025 Jiny Auth. All rights reserved.')
@section('powered-by', 'Powered by Laravel, JWT & Sharding')

<script>
document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('login-form');
    const loginButton = document.getElementById('login-button');
    const loginText = document.getElementById('login-text');
    const loginLoading = document.getElementById('login-loading');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const emailError = document.getElementById('email-error');
    const passwordError = document.getElementById('password-error');
    const alertContainer = document.getElementById('alert-container');
    const alertBox = document.getElementById('alert-box');
    const alertIcon = document.getElementById('alert-icon');
    const alertMessage = document.getElementById('alert-message');
    const alertClose = document.getElementById('alert-close');

    // 알림 표시 함수
    function showAlert(type, message) {
        // 기존 알림 숨기기
        alertContainer.classList.add('hidden');

        // 알림 타입에 따른 스타일 설정
        if (type === 'success') {
            alertBox.className = 'p-4 border rounded-lg bg-green-50 border-green-200 text-green-800';
            alertIcon.className = 'h-5 w-5 text-green-400';
            alertIcon.innerHTML = '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />';
        } else {
            alertBox.className = 'p-4 border rounded-lg bg-red-50 border-red-200 text-red-800';
            alertIcon.className = 'h-5 w-5 text-red-400';
            alertIcon.innerHTML = '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />';
        }

        alertMessage.textContent = message;
        alertContainer.classList.remove('hidden');

        // 5초 후 자동으로 숨기기
        setTimeout(() => {
            alertContainer.classList.add('hidden');
        }, 5000);
    }

    // 에러 표시 함수
    function showError(field, message) {
        const errorElement = document.getElementById(field + '-error');
        const inputElement = document.getElementById(field);

        if (errorElement && inputElement) {
            errorElement.textContent = message;
            errorElement.classList.remove('hidden');
            inputElement.classList.add('border-red-500', 'focus:ring-red-500', 'focus:border-red-500');
        }
    }

    // 에러 숨기기 함수
    function hideError(field) {
        const errorElement = document.getElementById(field + '-error');
        const inputElement = document.getElementById(field);

        if (errorElement && inputElement) {
            errorElement.classList.add('hidden');
            inputElement.classList.remove('border-red-500', 'focus:ring-red-500', 'focus:border-red-500');
        }
    }

    // 모든 에러 숨기기
    function hideAllErrors() {
        hideError('email');
        hideError('password');
    }

    // 로딩 상태 설정
    function setLoading(loading) {
        if (loading) {
            loginButton.disabled = true;
            loginText.classList.add('hidden');
            loginLoading.classList.remove('hidden');
        } else {
            loginButton.disabled = false;
            loginText.classList.remove('hidden');
            loginLoading.classList.add('hidden');
        }
    }

    // 폼 제출 처리
    loginForm.addEventListener('submit', async function(e) {
        e.preventDefault();

        // 기존 에러 숨기기
        hideAllErrors();

        // 폼 데이터 수집
        const formData = {
            email: emailInput.value.trim(),
            password: passwordInput.value,
            remember: document.getElementById('remember').checked
        };

        // 기본 유효성 검사
        let hasError = false;

        if (!formData.email) {
            showError('email', '이메일을 입력해주세요.');
            hasError = true;
        } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.email)) {
            showError('email', '올바른 이메일 형식을 입력해주세요.');
            hasError = true;
        }

        if (!formData.password) {
            showError('password', '비밀번호를 입력해주세요.');
            hasError = true;
        }

        if (hasError) {
            return;
        }

        // 로딩 상태 시작
        setLoading(true);

        try {
            const token = document.querySelector('input[name="_token"]').value;
            const response = await fetch('/signin/jwt', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify(formData)
            });

            const data = await response.json();

            if (response.ok && data.success) {
                // 성공 알림 표시
                showAlert('success', data.message || '로그인에 성공했습니다.');

                // 토큰 저장 (로컬 스토리지 + 쿠키)
                if (data.data) {
                    // 서버에서 쿠키를 직접 설정하므로 JS에서 쿠키를 저장하지 않음
                    // localStorage 저장은 필요시 유지
                    localStorage.setItem('access_token', data.data.access_token);
                    localStorage.setItem('refresh_token', data.data.refresh_token);
                    localStorage.setItem('token_type', data.data.token_type);
                }

                // 1초 후 홈으로 리다이렉트
                setTimeout(() => {
                    window.location.href = '/home';
                }, 1000);
            } else {
                if (data.errors) {
                    // 필드별 에러 표시
                    Object.keys(data.errors).forEach(field => {
                        showError(field, data.errors[field][0]);
                    });
                } else {
                    showAlert('error', data.message || '로그인에 실패했습니다.');
                }
            }

        } catch (error) {
            showAlert('error', '네트워크 오류가 발생했습니다.');
        } finally {
            setLoading(false);
        }
    });

    // 입력 필드 포커스 시 에러 숨기기
    emailInput.addEventListener('focus', () => hideError('email'));
    passwordInput.addEventListener('focus', () => hideError('password'));

    // 쿠키 삭제 함수
    function deleteCookie(name) {
        document.cookie = `${name}=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;`;
    }

    // 알림 닫기 버튼
    alertClose.addEventListener('click', function() {
        alertContainer.classList.add('hidden');
    });
});
</script>
