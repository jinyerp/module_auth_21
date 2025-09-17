@extends('jiny-auth::layouts.auth')

@section('title', '이메일 인증')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-100 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div class="text-center">
            <div class="mx-auto h-12 w-12 text-indigo-600">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
            </div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                이메일 인증이 필요합니다
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                계속하기 전에 이메일 주소를 확인해주세요.
            </p>
        </div>

        <div class="bg-white shadow overflow-hidden rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                @if (session('success'))
                    <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('info'))
                    <div class="mb-4 bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative" role="alert">
                        {{ session('info') }}
                    </div>
                @endif

                <div class="text-center">
                    <p class="text-gray-700 mb-4">
                        가입하신 이메일 주소로 인증 링크를 발송했습니다.<br>
                        이메일을 확인하고 인증 링크를 클릭해주세요.
                    </p>
                    
                    <div class="mt-4 p-4 bg-yellow-50 rounded-md">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-yellow-700">
                                    스팸 폴더도 확인해주세요!
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ route('verification.send') }}" class="mt-6">
                    @csrf
                    <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        인증 이메일 재발송
                    </button>
                </form>

                <div class="mt-6 text-center">
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-sm text-gray-600 hover:text-gray-900 underline">
                            로그아웃
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection