@extends('jiny-auth::layouts.auth')

@section('title', '승인 대기')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-100 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div class="text-center">
            <div class="mx-auto h-12 w-12 text-yellow-600">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                회원가입 승인 대기 중
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                관리자의 승인을 기다리고 있습니다.
            </p>
        </div>

        <div class="bg-white shadow overflow-hidden rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <div class="text-center mb-6">
                    @if($status === 'pending')
                        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-yellow-700">
                                        회원가입 신청이 접수되었습니다.<br>
                                        관리자 승인 후 서비스를 이용하실 수 있습니다.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @elseif($status === 'rejected')
                        <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-red-700">
                                        회원가입 승인이 거부되었습니다.<br>
                                        @if($user->rejection_reason)
                                            거부 사유: {{ $user->rejection_reason }}
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="border-t border-gray-200 px-4 py-5 sm:p-0">
                    <dl class="sm:divide-y sm:divide-gray-200">
                        <div class="py-3 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">이름</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $user->name }}</dd>
                        </div>
                        <div class="py-3 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">이메일</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $user->email }}</dd>
                        </div>
                        <div class="py-3 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">신청일시</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $user->created_at->format('Y-m-d H:i:s') }}</dd>
                        </div>
                        <div class="py-3 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">상태</dt>
                            <dd class="mt-1 text-sm sm:mt-0 sm:col-span-2">
                                @if($status === 'pending')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                        승인 대기
                                    </span>
                                @elseif($status === 'rejected')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                        승인 거부
                                    </span>
                                @endif
                            </dd>
                        </div>
                    </dl>
                </div>

                <div class="mt-6 flex flex-col gap-3">
                    <button type="button" id="checkStatusBtn" 
                            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        승인 상태 확인
                    </button>
                    
                    @if($status === 'rejected')
                        <button type="button" id="resendRequestBtn"
                                class="w-full flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            승인 요청 재전송
                        </button>
                    @endif
                    
                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <button type="submit" 
                                class="w-full flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            로그아웃
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkStatusBtn = document.getElementById('checkStatusBtn');
    const resendRequestBtn = document.getElementById('resendRequestBtn');
    
    // 승인 상태 확인
    checkStatusBtn?.addEventListener('click', function() {
        fetch('{{ route('register.approval.check') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (data.status === 'approved') {
                    alert(data.message);
                    window.location.href = data.redirect;
                } else if (data.status === 'rejected') {
                    alert(data.message + '\n' + (data.reason || ''));
                    location.reload();
                } else {
                    alert(data.message);
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('상태 확인 중 오류가 발생했습니다.');
        });
    });
    
    // 승인 요청 재전송
    resendRequestBtn?.addEventListener('click', function() {
        if (!confirm('승인 요청을 재전송하시겠습니까?')) return;
        
        fetch('{{ route('register.approval.resend') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message);
            if (data.success) {
                location.reload();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('요청 재전송 중 오류가 발생했습니다.');
        });
    });
    
    // 5초마다 자동으로 상태 확인
    setInterval(function() {
        fetch('{{ route('register.approval.check') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.status === 'approved') {
                window.location.href = data.redirect;
            }
        })
        .catch(error => {
            console.error('Auto-check error:', error);
        });
    }, 5000);
});
</script>
@endsection