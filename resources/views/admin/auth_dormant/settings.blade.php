@extends('jiny-admin::layouts.admin')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    {{-- 헤더 --}}
    <div class="mb-6">
        <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
            휴면계정 설정
        </h2>
        <p class="mt-1 text-sm text-gray-500">
            휴면계정 처리 정책 및 자동화 설정을 관리합니다
        </p>
    </div>

    @if(session('success'))
    <div class="mb-6 bg-green-50 border border-green-200 rounded-md p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 0116 0zM11.707 9.707a1 1 0 00-1.414-1.414L7 11.586l-1.293-1.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l5-5z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm text-green-700">{{ session('success') }}</p>
            </div>
        </div>
    </div>
    @endif

    <form action="{{ route('admin.auth.dormant.settings.update') }}" method="POST" class="space-y-6">
        @csrf
        
        {{-- 휴면 처리 설정 --}}
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">
                    휴면 처리 정책
                </h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">
                    계정을 휴면 상태로 전환하는 기준을 설정합니다
                </p>
            </div>
            <div class="border-t border-gray-200 px-4 py-5 sm:px-6 space-y-4">
                <div>
                    <label for="dormant_days" class="block text-sm font-medium text-gray-700">
                        휴면 전환 기준 (일)
                    </label>
                    <input type="number" 
                           name="dormant_days" 
                           id="dormant_days"
                           min="30"
                           value="{{ $settings['dormant_days'] }}"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <p class="mt-2 text-sm text-gray-500">
                        마지막 로그인 후 지정된 기간 동안 접속하지 않은 계정을 휴면 처리합니다
                    </p>
                </div>

                <div>
                    <label for="warning_days" class="block text-sm font-medium text-gray-700">
                        사전 알림 기간 (일)
                    </label>
                    <input type="number" 
                           name="warning_days" 
                           id="warning_days"
                           min="7"
                           value="{{ $settings['warning_days'] }}"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <p class="mt-2 text-sm text-gray-500">
                        휴면 전환 전 사용자에게 알림을 발송하는 기간
                    </p>
                </div>

                <div>
                    <label for="deletion_days" class="block text-sm font-medium text-gray-700">
                        휴면계정 삭제 기간 (일)
                    </label>
                    <input type="number" 
                           name="deletion_days" 
                           id="deletion_days"
                           min="30"
                           value="{{ $settings['deletion_days'] }}"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <p class="mt-2 text-sm text-gray-500">
                        휴면 처리 후 완전 삭제까지의 보관 기간 (개인정보보호법 준수)
                    </p>
                </div>
            </div>
        </div>

        {{-- 자동화 설정 --}}
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">
                    자동화 설정
                </h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">
                    휴면 처리 자동화 옵션을 설정합니다
                </p>
            </div>
            <div class="border-t border-gray-200 px-4 py-5 sm:px-6 space-y-4">
                <div class="flex items-center">
                    <input type="checkbox" 
                           name="auto_process" 
                           id="auto_process"
                           value="1"
                           {{ $settings['auto_process'] ? 'checked' : '' }}
                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="auto_process" class="ml-2 block text-sm text-gray-900">
                        자동 휴면 처리 활성화
                    </label>
                </div>
                <p class="text-sm text-gray-500 ml-6">
                    활성화 시 설정된 기준에 따라 자동으로 휴면 처리가 진행됩니다
                </p>

                <div class="flex items-center">
                    <input type="checkbox" 
                           name="notification_enabled" 
                           id="notification_enabled"
                           value="1"
                           {{ $settings['notification_enabled'] ? 'checked' : '' }}
                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="notification_enabled" class="ml-2 block text-sm text-gray-900">
                        이메일 알림 발송
                    </label>
                </div>
                <p class="text-sm text-gray-500 ml-6">
                    휴면 전환 전과 삭제 전에 사용자에게 이메일 알림을 발송합니다
                </p>
            </div>
        </div>

        {{-- 알림 템플릿 설정 --}}
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">
                    알림 템플릿
                </h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">
                    휴면 알림 이메일 템플릿을 선택합니다
                </p>
            </div>
            <div class="border-t border-gray-200 px-4 py-5 sm:px-6">
                <div>
                    <label for="notification_template" class="block text-sm font-medium text-gray-700">
                        알림 템플릿 선택
                    </label>
                    <select name="notification_template" 
                            id="notification_template"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        <option value="default" {{ $settings['notification_template'] === 'default' ? 'selected' : '' }}>
                            기본 템플릿
                        </option>
                        <option value="simple" {{ $settings['notification_template'] === 'simple' ? 'selected' : '' }}>
                            간단한 템플릿
                        </option>
                        <option value="detailed" {{ $settings['notification_template'] === 'detailed' ? 'selected' : '' }}>
                            상세 템플릿
                        </option>
                    </select>
                </div>
            </div>
        </div>

        {{-- 법적 고지 --}}
        <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800">
                        개인정보보호법 준수 사항
                    </h3>
                    <div class="mt-2 text-sm text-yellow-700">
                        <ul class="list-disc list-inside space-y-1">
                            <li>휴면계정 전환 30일 전 사전 알림 발송 필수</li>
                            <li>휴면계정은 별도 분리 보관 필요</li>
                            <li>휴면계정 파기 시 복구 불가능함을 명시</li>
                            <li>휴면계정 재활성화 절차 마련 필수</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        {{-- 저장 버튼 --}}
        <div class="flex justify-end space-x-3">
            <a href="{{ route('admin.auth.dormant') }}" 
               class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                취소
            </a>
            <button type="submit" 
                    class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                설정 저장
            </button>
        </div>
    </form>
</div>
@endsection