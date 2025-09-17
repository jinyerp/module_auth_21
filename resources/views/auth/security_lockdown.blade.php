@extends('jiny-auth::layouts.centered')

@section('title', '보안 락다운 - Jiny Auth')

@section('content')
    <!-- 보안 락다운 안내 메시지 -->
    <div class="text-center mb-6">
        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900 mb-4">
            <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
        </div>
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">보안 락다운</h2>
        <p class="text-gray-600 dark:text-gray-400">보안상의 이유로 일시적으로 서비스가 제한되었습니다</p>
    </div>

    <!-- 보안 경고 -->
    <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-red-400 dark:text-red-500" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-red-800 dark:text-red-200">보안 경고</h3>
                <div class="mt-2 text-sm text-red-700 dark:text-red-300">
                    <ul class="list-disc pl-5 space-y-1">
                        <li>의심스러운 활동이 감지되었습니다</li>
                        <li>시스템 보안을 위해 일시적으로 서비스가 제한됩니다</li>
                        <li>관리자가 상황을 점검하고 있습니다</li>
                        <li>정상화 후 서비스가 재개됩니다</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- 보안 조치 -->
    <div class="mb-6 p-4 bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-800 rounded-lg">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-orange-400 dark:text-orange-500" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-orange-800 dark:text-orange-200">보안 조치</h3>
                <div class="mt-2 text-sm text-orange-700 dark:text-orange-300">
                    <ul class="list-disc pl-5 space-y-1">
                        <li>회원가입 및 로그인 일시 중단</li>
                        <li>시스템 접근 제한</li>
                        <li>보안 점검 및 대응 조치</li>
                        <li>정상화 후 서비스 재개</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- 안전 안내 -->
    <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-blue-400 dark:text-blue-500" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">안전 안내</h3>
                <div class="mt-2 text-sm text-blue-700 dark:text-blue-300">
                    <ul class="list-disc pl-5 space-y-1">
                        <li>사용자 데이터는 안전하게 보호됩니다</li>
                        <li>보안 강화를 위한 일시적인 조치입니다</li>
                        <li>빠른 시일 내에 정상 서비스를 제공합니다</li>
                        <li>불편을 끼쳐 죄송합니다</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- 액션 버튼들 -->
    <div class="space-y-3">
        <button onclick="window.location.reload()"
           class="w-full text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800 transition duration-200 block">
            지금 다시 시도
        </button>

        <a href="{{ route('login') }}"
           class="w-full text-white bg-green-700 hover:bg-green-800 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-green-600 dark:hover:bg-green-700 dark:focus:ring-green-800 transition duration-200 block">
            로그인하기
        </a>
    </div>

    <!-- 대안 링크 -->
    <div class="mt-6 text-center">
        <p class="text-sm text-gray-600 dark:text-gray-400">
            기존 계정으로 로그인하시겠습니까?
            <a href="{{ route('login') }}" class="font-medium text-blue-600 hover:text-blue-500 dark:text-blue-400 dark:hover:text-blue-300">
                로그인하기
            </a>
        </p>
        <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">
            도움이 필요하신가요?
            <a href="{{ route('regist.help') }}" class="font-medium text-blue-600 hover:text-blue-500 dark:text-blue-400 dark:hover:text-blue-300">
                도움말 보기
            </a>
        </p>
    </div>
@endsection
