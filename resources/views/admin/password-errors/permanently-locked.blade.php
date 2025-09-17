@extends('jiny-auth::layouts.admin')

@section('title', '영구 잠금된 계정 목록')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">영구 잠금된 계정 목록</h1>
        <p class="text-gray-600 mt-2">최대 로그인 시도 횟수를 초과하여 영구 잠금된 계정들을 관리할 수 있습니다.</p>
    </div>

    <!-- 통계 카드 -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-red-100 text-red-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636M5.636 18.364l12.728-12.728"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">총 영구 잠금</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $permanentlyLockedAccounts->total() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-orange-100 text-orange-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">평균 오류 횟수</p>
                    <p class="text-2xl font-semibold text-gray-900">
                        {{ $permanentlyLockedAccounts->avg('consecutive_errors') ? round($permanentlyLockedAccounts->avg('consecutive_errors'), 1) : 0 }}회
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">해제 가능</p>
                    <p class="text-2xl font-semibold text-gray-900">
                        {{ $permanentlyLockedAccounts->where('consecutive_errors', '<', config('admin.auth.login.permanent_lockout_attempts', 25) * 2)->count() }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- 검색 및 필터 -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <form method="GET" action="{{ route('admin.auth.password-errors.permanently-locked') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">이메일</label>
                <input type="email" name="email" id="email" value="{{ request('email') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="이메일 검색">
            </div>

            <div>
                <label for="date_from" class="block text-sm font-medium text-gray-700 mb-2">잠금 시작일</label>
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

    <!-- 일괄 작업 -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">일괄 작업</h3>
            <div class="flex space-x-3">
                <button id="selectAllBtn" onclick="selectAll()"
                        class="bg-gray-600 hover:bg-gray-700 text-white font-medium py-2 px-4 rounded-md transition duration-150 ease-in-out">
                    전체 선택
                </button>
                <button id="bulkUnlockBtn" onclick="bulkUnlock()" disabled
                        class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition duration-150 ease-in-out disabled:opacity-50 disabled:cursor-not-allowed">
                    선택된 계정 잠금 해제
                </button>
            </div>
        </div>
    </div>

    <!-- 영구 잠금된 계정 목록 -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <input type="checkbox" id="masterCheckbox" onchange="toggleAllCheckboxes(this)" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">이메일</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP 주소</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">연속 오류</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">잠금 시간</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">마지막 시도</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">상태</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">작업</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($permanentlyLockedAccounts as $account)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <input type="checkbox" name="selected_accounts[]" value="{{ $account->id }}"
                                   class="account-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                   onchange="updateBulkUnlockButton()">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="text-sm font-medium text-gray-900">{{ $account->email }}</div>
                                @if($account->user)
                                <div class="ml-2 text-sm text-gray-500">({{ $account->user->name }})</div>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $account->ip_address }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                {{ $account->consecutive_errors }}회
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $account->locked_at ? $account->locked_at->format('Y-m-d H:i:s') : '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $account->created_at->format('Y-m-d H:i:s') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                영구 잠금
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2">
                                <a href="{{ route('admin.auth.password-errors.show', $account->id) }}"
                                   class="text-blue-600 hover:text-blue-900">상세보기</a>
                                @if($account->canBeUnlocked())
                                <button onclick="unlockAccount({{ $account->id }})"
                                        class="text-green-600 hover:text-green-900">잠금 해제</button>
                                @else
                                <span class="text-gray-400 cursor-not-allowed">해제 불가</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-4 text-center text-gray-500">
                            영구 잠금된 계정이 없습니다.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- 페이지네이션 -->
    @if($permanentlyLockedAccounts->hasPages())
    <div class="mt-6">
        {{ $permanentlyLockedAccounts->links() }}
    </div>
    @endif
</div>

<!-- 잠금 해제 모달 -->
<div id="unlockModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">계정 잠금 해제</h3>
            <div class="mb-4">
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                이 계정은 영구 잠금 상태입니다. 관리자 권한으로만 해제할 수 있습니다.
                            </p>
                        </div>
                    </div>
                </div>
                <label class="block text-sm font-medium text-gray-700 mb-2">해제 사유</label>
                <textarea id="unlockReason" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="잠금 해제 사유를 입력하세요 (필수)"></textarea>
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

function selectAll() {
    const checkboxes = document.querySelectorAll('.account-checkbox');
    const masterCheckbox = document.getElementById('masterCheckbox');

    checkboxes.forEach(checkbox => {
        checkbox.checked = masterCheckbox.checked;
    });

    updateBulkUnlockButton();
}

function toggleAllCheckboxes(masterCheckbox) {
    const checkboxes = document.querySelectorAll('.account-checkbox');

    checkboxes.forEach(checkbox => {
        checkbox.checked = masterCheckbox.checked;
    });

    updateBulkUnlockButton();
}

function updateBulkUnlockButton() {
    const checkboxes = document.querySelectorAll('.account-checkbox:checked');
    const bulkUnlockBtn = document.getElementById('bulkUnlockBtn');

    bulkUnlockBtn.disabled = checkboxes.length === 0;
}

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

    if (!reason.trim()) {
        alert('해제 사유를 입력해주세요.');
        return;
    }

    fetch(`/admin/auth/password-errors/${currentUnlockAccountId}/unlock`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            unlock_reason: reason
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

function bulkUnlock() {
    const checkboxes = document.querySelectorAll('.account-checkbox:checked');
    const ids = Array.from(checkboxes).map(cb => cb.value);

    if (ids.length === 0) {
        alert('선택된 계정이 없습니다.');
        return;
    }

    if (!confirm(`선택된 ${ids.length}개 영구 잠금 계정의 잠금을 해제하시겠습니까?\n\n이 작업은 관리자 권한이 필요하며, 신중하게 진행해야 합니다.`)) {
        return;
    }

    fetch('/admin/auth/password-errors/bulk-unlock', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ ids: ids })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('오류: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('오류가 발생했습니다.');
    });
}

// 페이지 로드 시 초기화
document.addEventListener('DOMContentLoaded', function() {
    updateBulkUnlockButton();

    // 개별 체크박스 변경 이벤트
    document.querySelectorAll('.account-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', updateBulkUnlockButton);
    });

    // 확인 버튼 이벤트
    document.getElementById('confirmUnlockBtn').addEventListener('click', confirmUnlock);
});
</script>
@endsection
