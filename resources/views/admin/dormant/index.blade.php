@extends('jiny-admin::layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="h3 mb-3">휴면계정 관리</h1>
        </div>
    </div>

    {{-- 통계 카드 --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">전체 휴면계정</h5>
                    <h2 class="text-primary">{{ number_format($statistics['total_dormant']) }}</h2>
                    <small class="text-muted">현재 휴면 상태</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">최근 휴면전환</h5>
                    <h2 class="text-warning">{{ number_format($statistics['recent_dormant']) }}</h2>
                    <small class="text-muted">최근 30일</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">삭제 예정</h5>
                    <h2 class="text-danger">{{ number_format($statistics['scheduled_delete']) }}</h2>
                    <small class="text-muted">{{ $statistics['delete_within_7days'] }}건 7일 내</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">이번달 활성화</h5>
                    <h2 class="text-success">{{ number_format($statistics['activated_this_month']) }}</h2>
                    <small class="text-muted">삭제 {{ $statistics['deleted_this_month'] }}건</small>
                </div>
            </div>
        </div>
    </div>

    {{-- 필터 --}}
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.auth.users.dormant') }}" class="row g-3">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="이름 또는 이메일 검색" value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="dormant_period" class="form-select">
                        <option value="">휴면 기간</option>
                        <option value="30days" {{ request('dormant_period') == '30days' ? 'selected' : '' }}>30일 이내</option>
                        <option value="90days" {{ request('dormant_period') == '90days' ? 'selected' : '' }}>90일 이내</option>
                        <option value="1year" {{ request('dormant_period') == '1year' ? 'selected' : '' }}>1년 이내</option>
                        <option value="over1year" {{ request('dormant_period') == 'over1year' ? 'selected' : '' }}>1년 초과</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="scheduled_delete" class="form-select">
                        <option value="">삭제 예정</option>
                        <option value="yes" {{ request('scheduled_delete') == 'yes' ? 'selected' : '' }}>예정됨</option>
                        <option value="no" {{ request('scheduled_delete') == 'no' ? 'selected' : '' }}>예정 없음</option>
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">검색</button>
                    <a href="{{ route('admin.auth.users.dormant') }}" class="btn btn-secondary">초기화</a>
                </div>
            </form>
        </div>
    </div>

    {{-- 작업 버튼 --}}
    <div class="mb-3">
        <button onclick="bulkActivate()" class="btn btn-success">선택 활성화</button>
        <button onclick="bulkDelete()" class="btn btn-danger">선택 삭제</button>
        <a href="{{ route('admin.auth.users.dormant.statistics') }}" class="btn btn-info">통계 보기</a>
        <a href="{{ route('admin.auth.users.dormant.settings') }}" class="btn btn-warning">정책 설정</a>
    </div>

    {{-- 휴면계정 목록 --}}
    <div class="card">
        <div class="card-body">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="selectAll"></th>
                        <th>ID</th>
                        <th>이름</th>
                        <th>이메일</th>
                        <th>휴면일</th>
                        <th>휴면기간</th>
                        <th>삭제예정일</th>
                        <th>상태</th>
                        <th>작업</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($dormantUsers as $user)
                    <tr>
                        <td><input type="checkbox" class="user-checkbox" value="{{ $user->id }}"></td>
                        <td>{{ $user->id }}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->dormant_at_formatted }}</td>
                        <td>
                            <span class="badge bg-secondary">{{ $user->dormant_days }}일</span>
                        </td>
                        <td>
                            @if($user->dormant_scheduled_delete_at)
                                <span class="text-danger">
                                    {{ $user->scheduled_delete_formatted }}
                                    @if($user->days_until_delete <= 7)
                                        <span class="badge bg-danger">{{ $user->days_until_delete }}일 남음</span>
                                    @endif
                                </span>
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-warning">휴면</span>
                        </td>
                        <td>
                            <button onclick="activateUser({{ $user->id }})" class="btn btn-sm btn-success">활성화</button>
                            <button onclick="deleteUser({{ $user->id }})" class="btn btn-sm btn-danger">삭제</button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center">휴면계정이 없습니다.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            {{ $dormantUsers->links() }}
        </div>
    </div>
</div>

@push('scripts')
<script>
// 전체 선택
document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.user-checkbox');
    checkboxes.forEach(checkbox => checkbox.checked = this.checked);
});

// 단일 사용자 활성화
function activateUser(userId) {
    if (!confirm('이 휴면계정을 활성화하시겠습니까?')) return;
    
    fetch(`/admin/auth/users/dormant/${userId}/activate`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        alert(data.message);
        if (data.success) location.reload();
    });
}

// 단일 사용자 삭제
function deleteUser(userId) {
    if (!confirm('이 휴면계정을 삭제하시겠습니까? 이 작업은 취소할 수 없습니다.')) return;
    
    fetch(`/admin/auth/users/dormant/${userId}/delete`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        alert(data.message);
        if (data.success) location.reload();
    });
}

// 일괄 활성화
function bulkActivate() {
    const selectedIds = getSelectedIds();
    if (selectedIds.length === 0) {
        alert('활성화할 계정을 선택해주세요.');
        return;
    }
    
    if (!confirm(`${selectedIds.length}개의 휴면계정을 활성화하시겠습니까?`)) return;
    
    fetch('/admin/auth/users/dormant/bulk-activate', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ user_ids: selectedIds })
    })
    .then(response => response.json())
    .then(data => {
        alert(data.message);
        if (data.success) location.reload();
    });
}

// 일괄 삭제
function bulkDelete() {
    const selectedIds = getSelectedIds();
    if (selectedIds.length === 0) {
        alert('삭제할 계정을 선택해주세요.');
        return;
    }
    
    if (!confirm(`${selectedIds.length}개의 휴면계정을 삭제하시겠습니까? 이 작업은 취소할 수 없습니다.`)) return;
    
    fetch('/admin/auth/users/dormant/bulk-delete', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ user_ids: selectedIds })
    })
    .then(response => response.json())
    .then(data => {
        alert(data.message);
        if (data.success) location.reload();
    });
}

// 선택된 ID 가져오기
function getSelectedIds() {
    const checkboxes = document.querySelectorAll('.user-checkbox:checked');
    return Array.from(checkboxes).map(cb => parseInt(cb.value));
}
</script>
@endpush
@endsection