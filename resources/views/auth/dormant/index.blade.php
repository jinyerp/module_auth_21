@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-lg shadow-md p-8">
            {{-- 휴면계정 안내 --}}
            <div class="text-center mb-8">
                <svg class="w-24 h-24 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
                <h2 class="text-2xl font-bold text-gray-800 mb-4">휴면계정 안내</h2>
                <p class="text-gray-600">
                    고객님의 계정은 장기간 접속하지 않아 휴면계정으로 전환되었습니다.
                </p>
            </div>

            {{-- 휴면 정보 --}}
            <div class="bg-gray-50 rounded-lg p-6 mb-6">
                <h3 class="font-semibold text-gray-700 mb-4">휴면계정 정보</h3>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600">계정 이메일:</span>
                        <span class="font-medium">{{ $dormantInfo['email'] }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">휴면 전환일:</span>
                        <span class="font-medium">{{ $dormantInfo['dormant_at']->format('Y년 m월 d일') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">휴면 기간:</span>
                        <span class="font-medium">{{ $dormantInfo['dormant_days'] }}일</span>
                    </div>
                    @if($dormantInfo['scheduled_delete_at'])
                    <div class="flex justify-between">
                        <span class="text-gray-600">삭제 예정일:</span>
                        <span class="font-medium text-red-600">
                            {{ $dormantInfo['scheduled_delete_at']->format('Y년 m월 d일') }}
                            ({{ $dormantInfo['days_until_delete'] }}일 남음)
                        </span>
                    </div>
                    @endif
                </div>
            </div>

            {{-- 활성화 요청 폼 --}}
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
                <h3 class="font-semibold text-blue-900 mb-4">계정 활성화</h3>
                <p class="text-blue-700 mb-4">
                    계정을 다시 사용하시려면 비밀번호를 입력하여 활성화를 요청해주세요.
                </p>
                
                @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
                @endif

                @if($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    @foreach($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
                @endif

                <form method="POST" action="{{ route('dormant.request-activation') }}">
                    @csrf
                    <input type="hidden" name="email" value="{{ $dormantInfo['email'] }}">
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="password">
                            비밀번호
                        </label>
                        <input type="password" name="password" id="password" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500"
                               placeholder="비밀번호를 입력하세요">
                    </div>
                    
                    <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition duration-200">
                        활성화 요청
                    </button>
                </form>
            </div>

            {{-- 안내 메시지 --}}
            <div class="text-sm text-gray-600">
                <p class="mb-2">※ 활성화 요청 후 이메일로 활성화 링크가 발송됩니다.</p>
                <p>※ 활성화 링크는 24시간 동안 유효합니다.</p>
            </div>

            {{-- 로그인 페이지로 돌아가기 --}}
            <div class="mt-6 text-center">
                <a href="{{ route('login') }}" class="text-blue-600 hover:text-blue-800">
                    로그인 페이지로 돌아가기
                </a>
            </div>
        </div>
    </div>
</div>
@endsection