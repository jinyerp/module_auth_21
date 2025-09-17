@extends('jiny-auth::layouts.centered')

@section('title', '예약된 회원 로그인 안내')
@section('brand-title', 'Jiny Auth')
@section('brand-subtitle', '예약된 회원 안내')

{{-- 페이지 상태 스크립트 --}}
@section('page-script', 'LoginReserved')
@section('content')
<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div>
            <div class="mx-auto h-12 w-12 flex items-center justify-center rounded-full bg-blue-100">
                <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                </svg>
            </div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                예약된 회원 로그인 안내
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                회원가입 신청 상태를 확인해주세요
            </p>
        </div>

        <div class="bg-white shadow rounded-lg p-6">
            {{-- 동적 상태 메시지 영역 --}}
            <div id="status-message-container"></div>

            <div class="space-y-4">
                <div class="bg-gray-50 rounded-lg p-4">
                    <h3 class="text-lg font-medium text-gray-900 mb-2">상태별 안내</h3>
                    <div class="space-y-3">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <div class="w-2 h-2 bg-yellow-400 rounded-full mt-2"></div>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-900">승인 대기 중</p>
                                <p class="text-sm text-gray-600">관리자가 신청을 검토하고 있습니다. 승인 후 로그인할 수 있습니다.</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <div class="w-2 h-2 bg-red-400 rounded-full mt-2"></div>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-900">신청 거절</p>
                                <p class="text-sm text-gray-600">회원가입 신청이 거절되었습니다. 다른 이메일로 다시 신청해주세요.</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <div class="w-2 h-2 bg-green-400 rounded-full mt-2"></div>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-900">승인 완료</p>
                                <p class="text-sm text-gray-600">승인이 완료되었습니다. 일반 로그인을 시도해주세요.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-blue-50 rounded-lg p-4">
                    <h3 class="text-lg font-medium text-blue-900 mb-2">도움말</h3>
                    <ul class="text-sm text-blue-800 space-y-1">
                        <li>• 예약된 도메인으로 회원가입을 신청한 경우 승인이 필요합니다.</li>
                        <li>• 승인 대기 중에는 로그인이 제한됩니다.</li>
                        <li>• 승인이 완료되면 일반 로그인을 시도해주세요.</li>
                        <li>• 문의사항이 있으시면 관리자에게 연락해주세요.</li>
                    </ul>
                </div>
            </div>

            <div class="mt-6 space-y-3">
                <a href="{{ route('login') }}"
                   class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    로그인 페이지로 돌아가기
                </a>
                <a href="{{ route('regist') }}"
                   class="w-full flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    회원가입 페이지로 이동
                </a>
            </div>
        </div>

        <div class="text-center">
            <p class="text-xs text-gray-500">
                예약된 도메인 회원가입 시스템
            </p>
        </div>
    </div>
</div>
@endsection
