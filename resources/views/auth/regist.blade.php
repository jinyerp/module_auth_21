@extends('jiny-auth::layouts.centered')

@section('title', '회원가입 - Jiny Auth')

@section('brand-title', 'Jiny Auth')
@section('brand-subtitle', '새로운 계정을 만들어보세요')

{{-- 페이지 상태 스크립트 --}}
@section('page-script', 'Regist')
@section('content')
    <div class="text-center mb-8">
        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 dark:bg-blue-900 mb-4">
            <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
            </svg>
        </div>
        <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">회원가입</h3>
        <p class="text-gray-600 dark:text-gray-400">새로운 계정을 만들어보세요</p>
    </div>

    <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg p-8">

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

        @if ($errors->any())
            <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">회원가입 오류</h3>
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

        <form id="regist-form" data-regist-form action="{{ route('register.post') }}" method="POST" class="space-y-6">
            @csrf

            <!-- 브라우저 정보 수집용 숨겨진 필드들 -->
            <input type="hidden" name="country" id="browser-country" value="">
            <input type="hidden" name="language" id="browser-language" value="">
            <input type="hidden" name="timezone" id="browser-timezone" value="">
            <input type="hidden" name="locale" id="browser-locale" value="">

            <!-- 이름 입력 -->
            <div>
                <label for="name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                    이름
                </label>
                <input type="text" id="name" name="name"
                       class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 @error('name') border-red-500 @enderror"
                       placeholder="홍길동"
                       value="{{ old('name') }}"
                       required />
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- 이메일 입력 -->
            <div>
                <label for="email" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                    이메일 주소
                </label>
                <input type="email" id="email" name="email"
                       class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 @error('email') border-red-500 @enderror"
                       placeholder="name@example.com"
                       value="{{ old('email') }}"
                       required />
                @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- 비밀번호 입력 -->
            <div>
                <label for="password" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                    비밀번호
                </label>
                <input type="password" id="password" name="password"
                       class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 @error('password') border-red-500 @enderror"
                       placeholder="••••••••"
                       required />
                @error('password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror

                <!-- 비밀번호 정책 안내 -->
                <div id="password-policy-container" class="mt-3">
                    {{-- 비밀번호 정책 안내 표시하고, js로 실시간 검증 표시 --}}
                    @include('jiny-auth::auth.password_policy', ['password_policy' => $password_policy])
                </div>

            </div>

            <!-- 비밀번호 확인 -->
            <div>
                <label for="password_confirmation" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                    비밀번호 확인
                </label>
                <input type="password" id="password_confirmation" name="password_confirmation"
                       class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 @error('password_confirmation') border-red-500 @enderror"
                       placeholder="••••••••"
                       required />
                @error('password_confirmation')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- <!-- 약관 동의 -->
            @if(config('jiny-auth.settings.regist.terms_agreement_required', false))
            <div class="flex items-start">
                <div class="flex items-center h-5">
                    <input id="terms_agreement" name="terms_agreement" type="checkbox"
                           class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600 @error('terms_agreement') border-red-500 @enderror"
                           required />
                </div>
                <div class="ml-3 text-sm">
                    <label for="terms_agreement" class="font-medium text-gray-900 dark:text-gray-300">
                        <a href="{{ route('auth.terms') }}" class="text-blue-600 hover:text-blue-500 dark:text-blue-400 dark:hover:text-blue-300" target="_blank">
                            이용약관
                        </a>과
                        <a href="{{ route('auth.privacy') }}" class="text-blue-600 hover:text-blue-500 dark:text-blue-400 dark:hover:text-blue-300" target="_blank">
                            개인정보처리방침
                        </a>에 동의합니다
                    </label>
                    @error('terms_agreement')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            @endif --}}

            <!-- 회원가입 버튼 -->
            <button type="submit" class="w-full text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800 transition duration-200">
                회원가입
            </button>
        </form>

        <!-- 로그인 링크 -->
        <div class="mt-8 text-center">
            <p class="text-sm text-gray-600 dark:text-gray-400">
                이미 계정이 있으신가요?
                <a href="{{ route('login') }}" class="font-medium text-blue-600 hover:text-blue-500 dark:text-blue-400 dark:hover:text-blue-300">
                    로그인하기
                </a>
            </p>
        </div>
    </div>

    <!-- 저작권 정보 -->
    <div class="mt-8 text-xs text-gray-400 text-center">
        <p>회원가입 시 이용약관과 개인정보처리방침에 동의한 것으로 간주됩니다.</p>
        <p class="mt-1">© 2025 Jiny Auth. All rights reserved.</p>
    </div>
@endsection
