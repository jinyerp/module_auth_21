@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-2xl font-bold mb-6">휴면계정 상태</h1>

        @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
        @endif

        {{-- 현재 상태 --}}
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-lg font-semibold mb-4">계정 상태</h2>
            
            @if(!$dormantStatus['is_dormant'])
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div>
                            <p class="font-medium text-green-800">활성 계정</p>
                            <p class="text-sm text-green-700">귀하의 계정은 현재 활성 상태입니다.</p>
                        </div>
                    </div>
                </div>

                {{-- 휴면 예방 정보 --}}
                <div class="mt-6 space-y-3">
                    <div class="flex justify-between py-2 border-b">
                        <span class="text-gray-600">마지막 활동:</span>
                        <span class="font-medium">
                            @if($dormantStatus['last_activity_at'])
                                {{ \Carbon\Carbon::parse($dormantStatus['last_activity_at'])->format('Y-m-d H:i') }}
                                ({{ $dormantStatus['last_activity_days'] }}일 전)
                            @else
                                -
                            @endif
                        </span>
                    </div>
                    
                    @if($dormantStatus['will_be_dormant_at'] && $dormantStatus['days_until_dormant'] > 0)
                    <div class="flex justify-between py-2 border-b">
                        <span class="text-gray-600">휴면 예정일:</span>
                        <span class="font-medium">
                            {{ $dormantStatus['will_be_dormant_at']->format('Y-m-d') }}
                            ({{ abs($dormantStatus['days_until_dormant']) }}일 남음)
                        </span>
                    </div>
                    @endif
                </div>

                {{-- 휴면 방지 버튼 --}}
                <div class="mt-6">
                    <form method="POST" action="{{ route('home.dormant.extend') }}">
                        @csrf
                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                            활동 시간 갱신 (휴면 방지)
                        </button>
                    </form>
                    <p class="text-sm text-gray-600 mt-2">
                        클릭하시면 활동 시간이 갱신되어 휴면계정 전환이 연장됩니다.
                    </p>
                </div>
            @else
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-red-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        <div>
                            <p class="font-medium text-red-800">휴면 계정</p>
                            <p class="text-sm text-red-700">귀하의 계정은 현재 휴면 상태입니다.</p>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- 휴면계정 정책 안내 --}}
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-lg font-semibold mb-4">휴면계정 정책</h2>
            <div class="space-y-3">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-gray-400 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div>
                        <p class="font-medium text-gray-700">휴면 전환 기준</p>
                        <p class="text-sm text-gray-600">{{ $policy['inactive_days'] }}일 이상 접속하지 않은 계정</p>
                    </div>
                </div>
                
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-gray-400 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                    </svg>
                    <div>
                        <p class="font-medium text-gray-700">사전 알림</p>
                        <p class="text-sm text-gray-600">휴면 전환 {{ $policy['notification_days'] }}일 전 알림 발송</p>
                    </div>
                </div>
                
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-gray-400 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                    <div>
                        <p class="font-medium text-gray-700">계정 삭제</p>
                        <p class="text-sm text-gray-600">휴면 전환 후 {{ $policy['delete_after_days'] }}일 경과 시 삭제 가능</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection