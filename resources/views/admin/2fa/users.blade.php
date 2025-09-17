@extends('jiny-auth::layouts.admin')

@section('title', '2FA 사용자 관리')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1 class="h3">2FA 사용자 관리</h1>
                <a href="{{ route('admin.2fa.settings') }}" class="btn btn-secondary">
                    <i class="fas fa-cog me-2"></i>설정 관리
                </a>
            </div>

            <!-- 검색 및 필터 -->
            <div class="card mb-3">
                <div class="card-body">
                    <form action="{{ route('admin.2fa.users') }}" method="GET" class="row g-3">
                        <div class="col-md-4">
                            <label for="search" class="form-label">검색</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                placeholder="이름 또는 이메일로 검색" value="{{ request('search') }}">
                        </div>
                        <div class="col-md-3">
                            <label for="status" class="form-label">2FA 상태</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">전체</option>
                                <option value="enabled" {{ request('status') == 'enabled' ? 'selected' : '' }}>활성화</option>
                                <option value="disabled" {{ request('status') == 'disabled' ? 'selected' : '' }}>비활성화</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search me-2"></i>검색
                            </button>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <a href="{{ route('admin.2fa.users') }}" class="btn btn-outline-secondary w-100">
                                <i class="fas fa-redo me-2"></i>초기화
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- 사용자 목록 -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">사용자 목록 ({{ $users->total() }}명)</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>이름</th>
                                    <th>이메일</th>
                                    <th>2FA 상태</th>
                                    <th>복구 코드</th>
                                    <th>마지막 2FA 로그인</th>
                                    <th>가입일</th>
                                    <th>작업</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $user)
                                    <tr>
                                        <td>{{ $user->id }}</td>
                                        <td>{{ $user->name }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>
                                            @if($user->two_factor_secret && $user->two_factor_enabled)
                                                <span class="badge bg-success">
                                                    <i class="fas fa-shield-alt me-1"></i>활성화
                                                </span>
                                            @elseif($user->two_factor_required)
                                                <span class="badge bg-warning">
                                                    <i class="fas fa-clock me-1"></i>활성화 필요
                                                </span>
                                            @else
                                                <span class="badge bg-secondary">
                                                    <i class="fas fa-times me-1"></i>비활성화
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($user->two_factor_secret)
                                                <span class="badge bg-info">
                                                    {{ $user->recovery_codes_remaining ?? 0 }}개 남음
                                                </span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($user->last_2fa_login)
                                                <small>{{ \Carbon\Carbon::parse($user->last_2fa_login->created_at)->diffForHumans() }}</small>
                                            @else
                                                <span class="text-muted">없음</span>
                                            @endif
                                        </td>
                                        <td>
                                            <small>{{ $user->created_at->format('Y-m-d') }}</small>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                @if($user->two_factor_secret && $user->two_factor_enabled)
                                                    <button type="button" class="btn btn-outline-danger" 
                                                        onclick="disable2FA({{ $user->id }}, '{{ $user->name }}')"
                                                        title="2FA 비활성화">
                                                        <i class="fas fa-ban"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-info"
                                                        onclick="viewDetails({{ $user->id }})"
                                                        title="상세 정보">
                                                        <i class="fas fa-info-circle"></i>
                                                    </button>
                                                @else
                                                    <button type="button" class="btn btn-outline-success"
                                                        onclick="forceEnable2FA({{ $user->id }}, '{{ $user->name }}')"
                                                        title="2FA 활성화 요청">
                                                        <i class="fas fa-shield-alt"></i>
                                                    </button>
                                                @endif
                                                <button type="button" class="btn btn-outline-secondary"
                                                    onclick="viewLogs({{ $user->id }})"
                                                    title="활동 로그">
                                                    <i class="fas fa-history"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">조건에 맞는 사용자가 없습니다.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- 페이지네이션 -->
                    <div class="d-flex justify-content-center mt-4">
                        {{ $users->withQueryString()->links() }}
                    </div>
                </div>
            </div>

            <!-- 일괄 작업 -->
            <div class="card mt-3">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2 text-muted">일괄 작업</h6>
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-outline-success" onclick="bulkEnable()">
                            <i class="fas fa-check-circle me-2"></i>선택한 사용자 2FA 활성화 요청
                        </button>
                        <button type="button" class="btn btn-outline-warning" onclick="bulkDisable()">
                            <i class="fas fa-times-circle me-2"></i>선택한 사용자 2FA 비활성화
                        </button>
                        <button type="button" class="btn btn-outline-info" onclick="exportUsers()">
                            <i class="fas fa-download me-2"></i>목록 다운로드
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 상세 정보 모달 -->
<div class="modal fade" id="detailsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">2FA 상세 정보</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modalContent">
                <!-- AJAX로 내용 로드 -->
            </div>
        </div>
    </div>
</div>

<script>
function disable2FA(userId, userName) {
    if (!confirm(`${userName}님의 2FA를 비활성화하시겠습니까?`)) return;
    
    fetch(`{{ url('/admin/auth/2fa/users') }}/${userId}/disable`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('처리 중 오류가 발생했습니다.');
    });
}

function forceEnable2FA(userId, userName) {
    if (!confirm(`${userName}님에게 2FA 활성화를 요청하시겠습니까?`)) return;
    
    fetch(`{{ url('/admin/auth/2fa/users') }}/${userId}/force-enable`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('처리 중 오류가 발생했습니다.');
    });
}

function viewDetails(userId) {
    // 상세 정보를 모달로 표시
    fetch(`{{ url('/admin/auth/2fa/users') }}/${userId}/details`)
        .then(response => response.text())
        .then(html => {
            document.getElementById('modalContent').innerHTML = html;
            new bootstrap.Modal(document.getElementById('detailsModal')).show();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('상세 정보를 불러올 수 없습니다.');
        });
}

function viewLogs(userId) {
    // 활동 로그 페이지로 이동
    window.location.href = `{{ url('/admin/auth/logs/user') }}/${userId}?filter=2fa`;
}

function bulkEnable() {
    // 체크박스 추가 구현 필요
    alert('선택한 사용자에게 2FA 활성화를 요청합니다.');
}

function bulkDisable() {
    // 체크박스 추가 구현 필요
    alert('선택한 사용자의 2FA를 비활성화합니다.');
}

function exportUsers() {
    const params = new URLSearchParams(window.location.search);
    params.append('export', 'csv');
    window.location.href = `{{ route('admin.2fa.users') }}?${params.toString()}`;
}
</script>
@endsection