@extends('jiny-auth::layouts.centered')

@section('title', '시스템 점검 중 - Jiny Auth')

@section('content')
    <!-- 점검 안내 메시지 -->
    <div class="text-center mb-6">
        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100 dark:bg-yellow-900 mb-4">
            <svg class="h-6 w-6 text-yellow-600 dark:text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0l1.403 5.784c.426 1.756-1.315 3.216-2.96 2.944l-5.784-1.403c-1.756-.426-1.756-2.924 0-3.35l5.784-1.403zM12 15a3 3 0 100-6 3 3 0 000 6z" />
            </svg>
        </div>
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">시스템 점검 중</h2>
        <p class="text-gray-600 dark:text-gray-400">더 나은 서비스를 위해 시스템 점검을 진행하고 있습니다</p>
    </div>

    <!-- 점검 메시지 -->
    @if(isset($message) && $message)
    <div class="mb-6 p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-yellow-400 dark:text-yellow-500" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">점검 안내</h3>
                <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-300">
                    {{ $message }}
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- 점검 내용 -->
    <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-blue-400 dark:text-blue-500" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">점검 내용</h3>
                <div class="mt-2 text-sm text-blue-700 dark:text-blue-300">
                    <ul class="list-disc pl-5 space-y-1">
                        <li>시스템 성능 최적화</li>
                        <li>보안 업데이트 적용</li>
                        <li>새로운 기능 추가</li>
                        <li>데이터베이스 정리</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- 예상 완료 시간 -->
    <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-green-400 dark:text-green-500" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-green-800 dark:text-green-200">점검 완료 예정</h3>
                <div class="mt-2 text-sm text-green-700 dark:text-green-300">
                    <ul class="list-disc pl-5 space-y-1">
                        <li>빠른 시일 내에 정상 서비스 제공</li>
                        <li>점검 완료 시 자동으로 서비스 재개</li>
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
