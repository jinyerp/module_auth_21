@extends('jiny-auth::layouts.auth')

@section('title', '2단계 인증')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-100 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                2단계 인증
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Google Authenticator 앱에서 6자리 코드를 입력하세요
            </p>
        </div>

        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form class="mt-8 space-y-6" action="{{ route('login.2fa.verify') }}" method="POST">
            @csrf
            
            <div>
                <label for="code" class="block text-sm font-medium text-gray-700">
                    인증 코드
                </label>
                <input id="code" name="code" type="text" 
                    autocomplete="one-time-code" 
                    inputmode="numeric"
                    pattern="[0-9]*"
                    maxlength="6"
                    required
                    class="appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm text-center text-2xl tracking-widest @error('code') border-red-500 @enderror"
                    placeholder="000000">
                @error('code')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="text-sm text-gray-600">
                <details>
                    <summary class="cursor-pointer text-indigo-600 hover:text-indigo-500">
                        백업 코드 사용
                    </summary>
                    <div class="mt-2">
                        <p class="text-gray-600 mb-2">
                            Google Authenticator에 접근할 수 없는 경우, 백업 코드를 입력하세요.
                        </p>
                        <input type="text" name="backup_code" 
                            class="appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
                            placeholder="XXXX-XXXX 형식의 백업 코드">
                    </div>
                </details>
            </div>

            <div>
                <button type="submit" class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                        <svg class="h-5 w-5 text-indigo-500 group-hover:text-indigo-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                        </svg>
                    </span>
                    인증하기
                </button>
            </div>

            <div class="text-center">
                <a href="{{ route('login.2fa.cancel') }}" class="font-medium text-indigo-600 hover:text-indigo-500">
                    취소하고 로그인 화면으로 돌아가기
                </a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 자동 포커스
    document.getElementById('code').focus();
    
    // 숫자만 입력 허용
    document.getElementById('code').addEventListener('input', function(e) {
        this.value = this.value.replace(/[^0-9]/g, '');
        
        // 6자리 입력 완료시 자동 제출 (선택적)
        if (this.value.length === 6) {
            // document.querySelector('form').submit();
        }
    });
    
    // 백업 코드 입력시 일반 코드 입력 비활성화
    document.querySelector('input[name="backup_code"]')?.addEventListener('input', function(e) {
        const codeInput = document.getElementById('code');
        if (this.value) {
            codeInput.disabled = true;
            codeInput.required = false;
        } else {
            codeInput.disabled = false;
            codeInput.required = true;
        }
    });
});
</script>
@endsection