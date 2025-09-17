@extends('jiny-auth::layouts.centered')

@section('title', '회원가입 제한 - Jiny Auth')

@section('content')
    <!-- 안내 메시지 -->
    <div class="text-center mb-6">
        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-orange-100 dark:bg-orange-900 mb-4">
            <svg class="h-6 w-6 text-orange-600 dark:text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
            </svg>
        </div>
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">회원가입 제한</h2>
        <p class="text-gray-600 dark:text-gray-400">시스템에서 일시적으로 회원가입을 제한하고 있습니다</p>
    </div>

    <!-- 제한 사유 -->
    <div class="mb-6 p-4 bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-800 rounded-lg">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-orange-400 dark:text-orange-500" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-orange-800 dark:text-orange-200">제한 사유</h3>
                <div class="mt-2 text-sm text-orange-700 dark:text-orange-300">
                    <ul class="list-disc pl-5 space-y-1">
                        <li>시스템 점검 및 유지보수</li>
                        <li>회원 관리 정책 변경</li>
                        <li>서버 부하 분산</li>
                        <li>일시적인 서비스 제한</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- 해제 예정 -->
    <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-blue-400 dark:text-blue-500" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">서비스 재개 예정</h3>
                <div class="mt-2 text-sm text-blue-700 dark:text-blue-300">
                    <ul class="list-disc pl-5 space-y-1">
                        <li>시스템 안정화 후 회원가입 재개</li>
                        <li>정상 서비스 제공 예정</li>
                        <li>불편을 끼쳐 죄송합니다</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- 액션 버튼들 -->
    <div class="space-y-3">
        {{-- <button onclick="window.location.reload()"
           class="w-full text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800 transition duration-200 block">
            지금 다시 시도
        </button> --}}

        <a href="{{ route('login') }}"
           class="w-full text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800 transition duration-200 block">
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
