@extends('jiny-auth::layouts.auth')

@section('title', '2FA 백업 코드')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-100 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                2FA 백업 코드 관리
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                백업 코드는 Google Authenticator에 접근할 수 없을 때 사용할 수 있습니다
            </p>
        </div>

        <div class="bg-white shadow overflow-hidden rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                <strong>중요:</strong> 백업 코드는 한 번만 사용할 수 있습니다.<br>
                                사용된 백업 코드는 자동으로 무효화됩니다.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="text-center">
                    <div class="inline-flex items-center px-4 py-2 bg-gray-100 rounded-lg">
                        <svg class="h-5 w-5 text-gray-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                        <span class="text-lg font-semibold text-gray-700">
                            사용 가능한 백업 코드: {{ $backupCodesCount }}개
                        </span>
                    </div>
                </div>

                @if($backupCodesCount < 3)
                    <div class="mt-4 bg-red-50 border-l-4 border-red-400 p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-red-700">
                                    백업 코드가 부족합니다. 새로운 백업 코드를 생성하는 것을 권장합니다.
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('2fa.recovery-codes') }}" class="mt-6">
                    @csrf
                    
                    <div class="mb-4">
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                            비밀번호 확인
                        </label>
                        <input type="password" id="password" name="password" 
                            class="appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
                            placeholder="현재 비밀번호를 입력하세요" required>
                    </div>

                    <button type="submit" 
                            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            onclick="return confirm('새로운 백업 코드를 생성하면 이전 백업 코드는 모두 무효화됩니다. 계속하시겠습니까?')">
                        새 백업 코드 생성
                    </button>
                </form>

                <div class="mt-6 pt-6 border-t border-gray-200">
                    <h3 class="text-sm font-medium text-gray-900 mb-3">백업 코드 사용 방법</h3>
                    <ol class="text-sm text-gray-600 space-y-2 list-decimal list-inside">
                        <li>Google Authenticator에 접근할 수 없을 때 사용합니다</li>
                        <li>로그인 시 2FA 인증 화면에서 백업 코드를 입력합니다</li>
                        <li>각 백업 코드는 한 번만 사용 가능합니다</li>
                        <li>백업 코드를 안전한 곳에 보관하세요</li>
                    </ol>
                </div>

                <div class="mt-6 text-center">
                    <a href="{{ route('profile.security') }}" class="text-sm text-indigo-600 hover:text-indigo-500">
                        ← 보안 설정으로 돌아가기
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection