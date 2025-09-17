@extends('jiny-auth::layouts.admin')

@section('title', '비밀번호 오류 상세 정보')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">비밀번호 오류 상세 정보</h1>
        <p class="text-gray-600 mt-2">사용자의 비밀번호 오류 시도 기록을 확인하고 관리할 수 있습니다.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- 메인 정보 -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">기본 정보</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">이메일</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $passwordError->email }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">IP 주소</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $passwordError->ip_address }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">오류 유형</label>
                        <p class="mt-1 text-sm text-gray-900">
                            @switch($passwordError->error_type)
                                @case('wrong_password')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        잘못된 비밀번호
                                    </span>
                                    @break
                                @case('account_not_found')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        계정 없음
                                    </span>
                                    @break
                                @case('account_locked')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        계정 잠금
                                    </span>
                                    @break
                                @default
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        {{ $passwordError->error_type }}
                                    </span>
                            @endswitch
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">상태</label>
                        <p class="mt-1">
                            @switch($passwordError->status)
                                @case('active')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        활성
                                    </span>
                                    @break
                                @case('locked')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        잠금
                                    </span>
                                    @break
                                @case('unlocked')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        해제됨
                                    </span>
                                    @break
                                @default
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        {{ $passwordError->status }}
                                    </span>
                            @endswitch
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">연속 오류 횟수</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $passwordError->consecutive_errors }}회</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">총 오류 횟수</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $passwordError->total_errors }}회</p>
                    </div>
                </div>

                @if($passwordError->error_message)
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700">오류 메시지</label>
                    <p class="mt-1 text-sm text-gray-900">{{ $passwordError->error_message }}</p>
                </div>
                @endif

                @if($passwordError->user_agent)
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700">사용자 에이전트</label>
                    <p class="mt-1 text-sm text-gray-900 break-all">{{ $passwordError->user_agent }}</p>
                </div>
                @endif

                @if($passwordError->location)
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700">위치</label>
                    <p class="mt-1 text-sm text-gray-900">{{ $passwordError->location }}</p>
                </div>
                @endif
            </div>

            <!-- 시간 정보 -->
            <div class="bg-white rounded-lg shadow-md p-6 mt-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">시간 정보</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">생성 시간</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $passwordError->created_at->format('Y-m-d H:i:s') }}</p>
                    </div>

                    @if($passwordError->locked_at)
                    <div>
                        <label class="block text-sm font-medium text-gray-700">잠금 시간</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $passwordError->locked_at->format('Y-m-d H:i:s') }}</p>
                    </div>
                    @endif

                    @if($passwordError->unlocked_at)
                    <div>
                        <label class="block text-sm font-medium text-gray-700">해제 시간</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $passwordError->unlocked_at->format('Y-m-d H:i:s') }}</p>
                    </div>
                    @endif

                    @if($passwordError->unlocked_by)
                    <div>
                        <label class="block text-sm font-medium text-gray-700">해제한 관리자</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $passwordError->unlockedByUser->name ?? '알 수 없음' }}</p>
                    </div>
                    @endif
                </div>

                @if($passwordError->unlock_reason)
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700">해제 사유</label>
                    <p class="mt-1 text-sm text-gray-900">{{ $passwordError->unlock_reason }}</p>
                </div>
                @endif
            </div>

            <!-- 관련 오류 기록 -->
            @if($relatedErrors->count() > 0)
            <div class="bg-white rounded-lg shadow-md p-6 mt-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">관련 오류 기록</h2>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">시간</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">상태</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">연속 오류</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($relatedErrors as $error)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $error->created_at->format('Y-m-d H:i:s') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $error->ip_address }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @switch($error->status)
                                        @case('active')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                활성
                                            </span>
                                            @break
                                        @case('locked')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                잠금
                                            </span>
                                            @break
                                        @case('unlocked')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                해제됨
                                            </span>
                                            @break
                                        @default
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                {{ $error->status }}
                                            </span>
                                    @endswitch
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $error->consecutive_errors }}회
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>

        <!-- 사이드바 -->
        <div class="lg:col-span-1">
            <!-- 사용자 정보 -->
            @if($passwordError->user)
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">사용자 정보</h3>

                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">이름</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $passwordError->user->name }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">이메일</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $passwordError->user->email }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">계정 상태</label>
                        <p class="mt-1">
                            @if($passwordError->user->is_active)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    활성
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    비활성
                                </span>
                            @endif
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">승인 상태</label>
                        <p class="mt-1">
                            @if($passwordError->user->is_approved)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    승인됨
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    승인 대기
                                </span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>
            @endif

            <!-- 작업 버튼 -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">작업</h3>

                <div class="space-y-3">
                    @if($passwordError->status === 'locked')
                    <button
                        onclick="unlockAccount({{ $passwordError->id }})"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition duration-150 ease-in-out">
                        계정 잠금 해제
                    </button>
                    @endif

                    <button
                        onclick="deleteError({{ $passwordError->id }})"
                        class="w-full bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-md transition duration-150 ease-in-out">
                        오류 기록 삭제
                    </button>

                    <a href="{{ route('admin.auth.password-errors.index') }}"
                       class="block w-full bg-gray-600 hover:bg-gray-700 text-white font-medium py-2 px-4 rounded-md text-center transition duration-150 ease-in-out">
                        목록으로 돌아가기
                    </a>
                </div>
            </div>
        </div>
    </div>
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
                <button onclick="confirmUnlock({{ $passwordError->id }})" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">해제</button>
            </div>
        </div>
    </div>
</div>

<script>
function unlockAccount(id) {
    document.getElementById('unlockModal').classList.remove('hidden');
}

function closeUnlockModal() {
    document.getElementById('unlockModal').classList.add('hidden');
}

function confirmUnlock(id) {
    const reason = document.getElementById('unlockReason').value;

    fetch(`/admin/auth/password-errors/${id}/unlock`, {
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

function deleteError(id) {
    if (confirm('이 오류 기록을 삭제하시겠습니까?')) {
        fetch(`/admin/auth/password-errors/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('오류 기록이 삭제되었습니다.');
                window.location.href = '{{ route("admin.auth.password-errors.index") }}';
            } else {
                alert('오류: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('오류가 발생했습니다.');
        });
    }
}
</script>
@endsection
