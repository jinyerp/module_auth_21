@extends('jiny-auth::layouts.auth')

@section('title', '비밀번호 찾기')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-100 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                비밀번호 찾기
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                가입하신 이메일 주소를 입력하시면 비밀번호 재설정 링크를 보내드립니다.
            </p>
        </div>
        
        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form class="mt-8 space-y-6" action="{{ route('password.email') }}" method="POST">
            @csrf
            
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">
                    이메일 주소
                </label>
                <input id="email" name="email" type="email" autocomplete="email" required
                    class="appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm @error('email') border-red-500 @enderror"
                    placeholder="이메일을 입력하세요" value="{{ old('email') }}">
                @error('email')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <button type="submit" class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    비밀번호 재설정 링크 보내기
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