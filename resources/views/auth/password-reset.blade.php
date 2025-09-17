@extends('jiny-auth::layouts.auth')

@section('title', '새 비밀번호 설정')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-100 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                새 비밀번호 설정
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                새로운 비밀번호를 입력해주세요.
            </p>
        </div>

        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form class="mt-8 space-y-6" action="{{ route('password.update') }}" method="POST">
            @csrf
            
            <input type="hidden" name="token" value="{{ $token }}">
            <input type="hidden" name="email" value="{{ $email }}">

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">
                    새 비밀번호
                </label>
                <input id="password" name="password" type="password" autocomplete="new-password" required
                    class="appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm @error('password') border-red-500 @enderror"
                    placeholder="새 비밀번호를 입력하세요">
                @error('password')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
                
                <div class="mt-2 text-sm text-gray-600">
                    <ul class="list-disc list-inside">
                        <li>최소 8자 이상</li>
                        <li>영문 대소문자, 숫자, 특수문자 중 3가지 이상 포함</li>
                    </ul>
                </div>
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
                    비밀번호 확인
                </label>
                <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required
                    class="appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
                    placeholder="비밀번호를 다시 입력하세요">
            </div>

            <div>
                <button type="submit" class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    비밀번호 재설정
                </button>
            </div>

            <div class="text-center text-sm">
                <a href="{{ route('login') }}" class="font-medium text-indigo-600 hover:text-indigo-500">
                    로그인으로 돌아가기
                </a>
            </div>
        </form>
    </div>
</div>
@endsection