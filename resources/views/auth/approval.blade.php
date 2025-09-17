@extends('jiny-auth::layouts.centered')

@section('title', '승인 대기 - Jiny Auth')
@section('brand-title', 'Jiny Auth')
@section('brand-subtitle', '승인 대기 페이지')

{{-- CSRF 토큰 메타 태그 추가 --}}
<meta name="csrf-token" content="{{ csrf_token() }}">

{{-- 페이지 상태 스크립트 --}}
@section('page-script', 'LoginApproval')
@section('content')
    {{-- 워크플로우 단계 표시 --}}
    <div class="mb-6">
        <div class="flex items-center justify-center mb-4">
            <h3 class="text-2xl font-bold text-gray-900 dark:text-white">승인 진행 상황</h3>
        </div>

        {{-- 워크플로우 단계 표시 --}}
        <div class="flex items-center justify-center mb-4">
            <div class="flex items-center space-x-4">
                @for($i = 1; $i <= 5; $i++)
                    <div class="flex items-center">
                        <div class="workflow-step flex items-center justify-center h-10 w-10 rounded-full
                            @if($i <= $workflowStep) bg-blue-600 text-white @else bg-gray-200 dark:bg-gray-700 text-gray-500 @endif">
                            {{ $i }}
                        </div>
                        @if($i < 5)
                            <div class="w-8 h-0.5 @if($i < $workflowStep) bg-blue-600 @else bg-gray-200 dark:bg-gray-700 @endif"></div>
                        @endif
                    </div>
                @endfor
            </div>
        </div>

        <div class="text-center">
            <p class="workflow-description text-lg font-medium text-gray-900 dark:text-white">{{ $workflowDescription }} (단계 {{ $workflowStep }}/5)</p>
        </div>
    </div>

    {{-- 승인 상태별 메시지 --}}
    @if($approval)
        @if($approval->isPending())
            {{-- 승인 대기 상태 --}}
            <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg p-6">
                <div class="flex items-center justify-center mb-4">
                    <div class="flex items-center justify-center h-10 w-10 rounded-full bg-yellow-100 dark:bg-yellow-900 mr-3">
                        <svg class="h-5 w-5 text-yellow-600 dark:text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">승인 대기 중</h3>
                </div>

                <p class="text-gray-600 dark:text-gray-400 mb-4 text-center">
                    회원가입이 완료되었습니다. 관리자 승인 후 로그인할 수 있습니다.
                </p>

                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-3 mb-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-4 w-4 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-2">
                            <p class="text-sm text-blue-800 dark:text-blue-200">
                                승인 처리에는 보통 1-2일이 소요됩니다. 승인 완료 시 이메일로 알림을 받으실 수 있습니다.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- 상태 확인 및 로그아웃 버튼 --}}
                <div class="flex justify-center space-x-3">
                    <button id="checkStatusBtn" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-1.5 px-3 rounded-md transition duration-200 text-sm">
                        <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        상태 확인
                    </button>
                    <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                       class="bg-gray-600 hover:bg-gray-700 text-white font-medium py-1.5 px-3 rounded-md transition duration-200 text-sm">
                        <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        로그아웃
                    </a>
                </div>
            </div>

        @elseif($approval->isResubmitted())
            {{-- 재신청 대기 상태 --}}
            <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg p-6">
                <div class="flex items-center justify-center mb-4">
                    <div class="flex items-center justify-center h-10 w-10 rounded-full bg-blue-100 dark:bg-blue-900 mr-3">
                        <svg class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">재신청 대기 중</h3>
                </div>

                <p class="text-gray-600 dark:text-gray-400 mb-4 text-center">
                    재신청이 제출되었습니다. 관리자 검토 후 승인 여부가 결정됩니다.
                </p>

                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-3 mb-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-4 w-4 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-2">
                            <p class="text-sm text-blue-800 dark:text-blue-200">
                                재신청 횟수: {{ $approval->resubmission_count }}회
                            </p>
                        </div>
                    </div>
                </div>

                {{-- 상태 확인 및 로그아웃 버튼 --}}
                <div class="flex justify-center space-x-3">
                    <button id="checkStatusBtn" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-1.5 px-3 rounded-md transition duration-200 text-sm">
                        <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        상태 확인
                    </button>
                    <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                       class="bg-gray-600 hover:bg-gray-700 text-white font-medium py-1.5 px-3 rounded-md transition duration-200 text-sm">
                        <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        로그아웃
                    </a>
                </div>
            </div>

        @elseif($approval->isRejected())
            {{-- 거부 상태 --}}
            <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg p-6">
                <div class="flex items-center justify-center mb-4">
                    <div class="flex items-center justify-center h-10 w-10 rounded-full bg-red-100 dark:bg-red-900 mr-3">
                        <svg class="h-5 w-5 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">승인 거부됨</h3>
                </div>

                <p class="text-gray-600 dark:text-gray-400 mb-4 text-center">승인이 거부되었습니다. 아래 사유를 확인하시고 필요시 재신청해주세요.</p>

                @if($approval->rejection_reason)
                <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-4 w-4 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-2">
                            <h4 class="text-sm font-medium text-red-800 dark:text-red-200">거부 사유</h4>
                            <p class="text-sm text-red-700 dark:text-red-300 mt-1">{{ $approval->rejection_reason }}</p>
                        </div>
                    </div>
                </div>
                @endif

                {{-- 재신청 및 로그아웃 버튼 --}}
                <div class="flex justify-center space-x-3">
                    @if($approval->canResubmit())
                    <button id="resubmitBtn" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-1.5 px-3 rounded-md transition duration-200 text-sm">
                        <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        재신청하기
                    </button>
                    @endif
                    <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                       class="bg-gray-600 hover:bg-gray-700 text-white font-medium py-1.5 px-3 rounded-md transition duration-200 text-sm">
                        <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        로그아웃
                    </a>
                </div>
            </div>

        @elseif($approval->isReturned())
            {{-- 재거부 상태 --}}
            <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg p-6">
                <div class="flex items-center justify-center mb-4">
                    <div class="flex items-center justify-center h-10 w-10 rounded-full bg-red-100 dark:bg-red-900 mr-3">
                        <svg class="h-5 w-5 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">재신청 거부됨</h3>
                </div>

                <p class="text-gray-600 dark:text-gray-400 mb-4 text-center">재신청이 거부되었습니다. 아래 사유를 확인하시고 필요시 다시 재신청해주세요.</p>

                @if($approval->rejection_reason)
                <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-4 w-4 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-2">
                            <h4 class="text-sm font-medium text-red-800 dark:text-red-200">거부 사유</h4>
                            <p class="text-sm text-red-700 dark:text-red-300 mt-1">{{ $approval->rejection_reason }}</p>
                        </div>
                    </div>
                </div>
                @endif

                {{-- 재신청 및 로그아웃 버튼 --}}
                <div class="flex justify-center space-x-3">
                    @if($approval->canResubmit())
                    <button id="resubmitBtn" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-1.5 px-3 rounded-md transition duration-200 text-sm">
                        <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        다시 재신청하기
                    </button>
                    @endif
                    <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                       class="bg-gray-600 hover:bg-gray-700 text-white font-medium py-1.5 px-3 rounded-md transition duration-200 text-sm">
                        <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        로그아웃
                    </a>
                </div>
            </div>
        @endif
    @else
        {{-- 승인 요청이 없는 경우 --}}
        <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg p-6">
            <div class="flex items-center justify-center mb-4">
                <div class="flex items-center justify-center h-10 w-10 rounded-full bg-gray-100 dark:bg-gray-700 mr-3">
                    <svg class="h-5 w-5 text-gray-600 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 dark:text-white">승인 요청 없음</h3>
            </div>

            <p class="text-gray-600 dark:text-gray-400 mb-4 text-center">
                현재 승인 요청이 없습니다. 관리자에게 문의해주세요.
            </p>

            {{-- 로그아웃 버튼 --}}
            <div class="flex justify-center">
                <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                   class="bg-gray-600 hover:bg-gray-700 text-white font-medium py-1.5 px-3 rounded-md transition duration-200 text-sm">
                    <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    로그아웃
                </a>
            </div>
        </div>
    @endif

    {{-- 로그아웃 폼 --}}
    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
        @csrf
    </form>

    {{-- 승인 이력 표시 --}}
    @if($approvalHistory && $approvalHistory->count() > 0)
    <div class="mt-6 bg-white dark:bg-gray-800 shadow-lg rounded-lg p-6">
        <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">승인 이력</h4>

        <div class="space-y-3">
            @foreach($approvalHistory as $log)
            <div class="border-l-4 border-blue-500 pl-3 py-2">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                            {{ $log->getActionDescription() }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            {{ $log->performed_at ? $log->performed_at->format('Y-m-d H:i:s') : 'N/A' }}
                        </p>
                        @if($log->action_notes)
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                            {{ $log->action_notes }}
                        </p>
                        @endif
                    </div>
                    <div class="text-right">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                            @if($log->action === 'approved') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                            @elseif($log->action === 'rejected' || $log->action === 'returned') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                            @else bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 @endif">
                            {{ ucfirst($log->action) }}
                        </span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- 상태 메시지 컨테이너 --}}
    <div id="status-message-container"></div>

    {{-- 승인 이력 컨테이너 --}}
    <div id="approval-history-container"></div>

    {{-- 워크플로우 단계 컨테이너 --}}
    <div id="workflow-container"></div>
@endsection

