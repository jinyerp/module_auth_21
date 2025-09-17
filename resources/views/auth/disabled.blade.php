@extends('jiny-auth::layouts.centered')

@section('title', '로그인 기능 비활성화 - Jiny Auth')
@section('page-script', 'LoginDisabled')

@section('content')
<div class="text-center mb-8">
    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100 dark:bg-yellow-900 mb-4">
        <svg class="h-8 w-8 text-yellow-600 dark:text-yellow-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z" />
        </svg>
    </div>
    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">로그인 기능이 비활성화되었습니다</h3>
    <p class="text-sm text-gray-600 dark:text-gray-400">
        현재 로그인 기능이 비활성화되어 있습니다.
    </p>
</div>

<div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg p-8">
    <div class="flex items-center">
        <div class="flex-shrink-0">
            <svg class="h-8 w-8 text-yellow-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z" />
            </svg>
        </div>
        <div class="ml-3">
            <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                접근 불가
            </h3>
            <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-300">
                <p>
                    관리자에 의해 로그인 기능이 일시적으로 비활성화되었습니다. 
                    잠시 후 다시 시도해주시거나, 관리자에게 문의해주세요.
                </p>
            </div>
        </div>
    </div>
    
    <div class="mt-6 text-center">
        <a href="{{ url('/') }}" class="text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300 font-medium">
            홈으로 돌아가기
        </a>
    </div>
</div>

<div class="mt-8 text-xs text-gray-400 text-center">
    <p class="mt-1">© 2025 Jiny Admin. All rights reserved.</p>
</div>
@endsection
