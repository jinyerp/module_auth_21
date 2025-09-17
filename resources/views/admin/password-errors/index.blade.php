@extends('jiny-auth::layouts.resource.table')

@section('title', '비밀번호 오류 관리')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- 페이지 헤더 -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">비밀번호 오류 관리</h1>
        <p class="text-gray-600 mt-2">로그인 시도 시 발생하는 비밀번호 오류를 관리하고 모니터링할 수 있습니다.</p>
    </div>

    <!-- 액션 버튼 -->
    <div class="flex flex-wrap gap-3 mb-6">
        <a href="{{ route($route . 'locked') }}"
           class="bg-yellow-600 hover:bg-yellow-700 text-white font-medium py-2 px-4 rounded-md transition duration-150 ease-in-out">
            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
            </svg>
            잠금된 계정 관리
        </a>
        <a href="{{ route($route . 'permanently-locked') }}"
           class="bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-md transition duration-150 ease-in-out">
            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636M5.636 18.364l12.728-12.728"></path>
            </svg>
            영구 잠금 계정 관리
        </a>
        <a href="{{ route($route . 'statistics') }}"
           class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition duration-150 ease-in-out">
            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
            </svg>
            통계 보기
        </a>
    </div>

    <!-- 통계 카드 -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-red-100 text-red-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">총 오류 횟수</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ number_format($stats['total_errors'] ?? 0) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">잠금된 계정</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ number_format($stats['locked_accounts'] ?? 0) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-red-100 text-red-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636M5.636 18.364l12.728-12.728"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">영구 잠금</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ number_format($stats['permanently_locked'] ?? 0) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">오늘 오류</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ number_format($stats['today_errors'] ?? 0) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- 검색 및 필터 -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <form method="GET" action="{{ route($route . 'index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">이메일</label>
                <input type="email" name="email" id="email" value="{{ request('email') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="이메일 검색">
            </div>

            <div>
                <label for="ip_address" class="block text-sm font-medium text-gray-700 mb-2">IP 주소</label>
                <input type="text" name="ip_address" id="ip_address" value="{{ request('ip_address') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="IP 주소 검색">
            </div>

            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">상태</label>
                <select name="status" id="status"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">전체</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>활성</option>
                    <option value="locked" {{ request('status') === 'locked' ? 'selected' : '' }}>잠금</option>
                    <option value="permanently_locked" {{ request('status') === 'permanently_locked' ? 'selected' : '' }}>영구 잠금</option>
                </select>
            </div>

            <div>
                <label for="date_from" class="block text-sm font-medium text-gray-700 mb-2">시작일</label>
                <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="flex items-end">
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition duration-150 ease-in-out">
                    검색
                </button>
            </div>
        </form>
    </div>

    <!-- 비밀번호 오류 목록 -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">비밀번호 오류 기록</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">이메일</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP 주소</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">연속 오류</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">상태</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">마지막 시도</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">작업</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($passwordErrors as $error)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="text-sm font-medium text-gray-900">{{ $error->email }}</div>
                                @if($error->user)
                                <div class="ml-2 text-sm text-gray-500">({{ $error->user->name }})</div>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $error->ip_address }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($error->consecutive_errors >= config('admin.auth.login.permanent_lockout_attempts', 25))
                                    bg-red-100 text-red-800
                                @elseif($error->consecutive_errors >= config('admin.auth.login.lockout_attempts', 5))
                                    bg-yellow-100 text-yellow-800
                                @else
                                    bg-gray-100 text-gray-800
                                @endif">
                                {{ $error->consecutive_errors }}회
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($error->consecutive_errors >= config('admin.auth.login.permanent_lockout_attempts', 25))
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    영구 잠금
                                </span>
                            @elseif($error->consecutive_errors >= config('admin.auth.login.lockout_attempts', 5))
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    잠금
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    활성
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $error->created_at->format('Y-m-d H:i:s') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2">
                                <a href="{{ route('admin.auth.password-errors.show', $error->id) }}"
                                   class="text-blue-600 hover:text-blue-900">상세보기</a>
                                @if($error->consecutive_errors >= config('admin.auth.login.lockout_attempts', 5))
                                <button onclick="unlockAccount({{ $error->id }})"
                                        class="text-green-600 hover:text-green-900">잠금 해제</button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                            비밀번호 오류 기록이 없습니다.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- 페이지네이션 -->
    @if($passwordErrors->hasPages())
    <div class="mt-6">
        {{ $passwordErrors->links() }}
    </div>
    @endif
</div>

<!-- 잠금 해제 모달 -->
<div id="unlockModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">계정 잠금 해제</h3>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">해제 사유</label>
                <textarea id="unlockReason" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="잠금 해제 사유를 입력하세요"></textarea>
            </div>
            <div class="flex justify-end space-x-3">
                <button onclick="closeUnlockModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">취소</button>
                <button id="confirmUnlockBtn" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">해제</button>
            </div>
        </div>
    </div>
</div>

<script>
let currentUnlockAccountId = null;

function unlockAccount(id) {
    currentUnlockAccountId = id;
    document.getElementById('unlockModal').classList.remove('hidden');
}

function closeUnlockModal() {
    document.getElementById('unlockModal').classList.add('hidden');
    currentUnlockAccountId = null;
}

function confirmUnlock() {
    if (!currentUnlockAccountId) return;

    const reason = document.getElementById('unlockReason').value;

    fetch(`/admin/auth/password-errors/${currentUnlockAccountId}/unlock`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            unlock_reason: reason || '관리자에 의한 잠금 해제'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('계정이 성공적으로 해제되었습니다.');
            location.reload();
        } else {
            alert('오류: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('오류가 발생했습니다.');
    });

    closeUnlockModal();
}

// 페이지 로드 시 초기화
document.addEventListener('DOMContentLoaded', function() {
    // 확인 버튼 이벤트
    document.getElementById('confirmUnlockBtn').addEventListener('click', confirmUnlock);
});
</script>
@endsection
