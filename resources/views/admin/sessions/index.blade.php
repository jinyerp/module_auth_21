@extends('jiny-auth::layouts.admin')

@section('title', '세션 관리')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1 class="h3">활성 세션 관리</h1>
                <a href="{{ route('admin.auth.sessions.statistics') }}" class="btn btn-info">
                    <i class="fas fa-chart-bar me-2"></i>통계 보기
                </a>
            </div>

            <!-- 통계 카드 -->
            <div class="row mb-4">
                <div class="col-md-2">
                    <div class="card">
                        <div class="card-body text-center">
                            <h4 class="mb-0">{{ $statistics['total_sessions'] }}</h4>
                            <small class="text-muted">전체 세션</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card">
                        <div class="card-body text-center">
                            <h4 class="mb-0">{{ $statistics['unique_users'] }}</h4>
                            <small class="text-muted">활성 사용자</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card">
                        <div class="card-body text-center">
                            <h4 class="mb-0">{{ $statistics['desktop_sessions'] }}</h4>
                            <small class="text-muted">데스크탑</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card">
                        <div class="card-body text-center">
                            <h4 class="mb-0">{{ $statistics['mobile_sessions'] }}</h4>
                            <small class="text-muted">모바일</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card">
                        <div class="card-body text-center">
                            <h4 class="mb-0">{{ $statistics['tablet_sessions'] }}</h4>
                            <small class="text-muted">태블릿</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card">
                        <div class="card-body text-center">
                            <h4 class="mb-0">{{ $statistics['last_hour_sessions'] }}</h4>
                            <small class="text-muted">최근 1시간</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 검색 및 필터 -->
            <div class="card mb-3">
                <div class="card-body">
                    <form action="{{ route('admin.auth.sessions') }}" method="GET" class="row g-3">
                        <div class="col-md-4">
                            <input type="text" class="form-control" name="search" 
                                   placeholder="이름, 이메일, IP 검색" value="{{ request('search') }}">
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" name="device_type">
                                <option value="">모든 디바이스</option>
                                <option value="desktop" {{ request('device_type') == 'desktop' ? 'selected' : '' }}>데스크탑</option>
                                <option value="mobile" {{ request('device_type') == 'mobile' ? 'selected' : '' }}>모바일</option>
                                <option value="tablet" {{ request('device_type') == 'tablet' ? 'selected' : '' }}>태블릿</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" name="sort_by">
                                <option value="last_activity" {{ request('sort_by') == 'last_activity' ? 'selected' : '' }}>마지막 활동</option>
                                <option value="login_at" {{ request('sort_by') == 'login_at' ? 'selected' : '' }}>로그인 시간</option>
                                <option value="ip_address" {{ request('sort_by') == 'ip_address' ? 'selected' : '' }}>IP 주소</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search me-2"></i>검색
                            </button>
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-danger w-100" onclick="bulkTerminate()">
                                <i class="fas fa-times-circle me-2"></i>선택 종료
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- 세션 목록 -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" id="selectAll"></th>
                                    <th>사용자</th>
                                    <th>디바이스</th>
                                    <th>IP 주소</th>
                                    <th>위치</th>
                                    <th>로그인 시간</th>
                                    <th>마지막 활동</th>
                                    <th>지속 시간</th>
                                    <th>작업</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($sessions as $session)
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="session-checkbox" value="{{ $session->id }}">
                                        </td>
                                        <td>
                                            <strong>{{ $session->name }}</strong><br>
                                            <small class="text-muted">{{ $session->email }}</small>
                                        </td>
                                        <td>
                                            @if($session->device_info)
                                                <i class="{{ $session->device_info['icon'] }} text-{{ $session->device_info['color'] }} me-1"></i>
                                                {{ $session->device_info['type'] }}<br>
                                                <small class="text-muted">
                                                    {{ $session->device_info['browser'] }} / {{ $session->device_info['platform'] }}
                                                </small>
                                            @else
                                                <span class="text-muted">Unknown</span>
                                            @endif
                                        </td>
                                        <td>{{ $session->ip_address }}</td>
                                        <td>{{ $session->location ?: '-' }}</td>
                                        <td>
                                            <small>{{ $session->login_at_formatted }}</small>
                                        </td>
                                        <td>
                                            <small>{{ $session->last_activity_human }}</small>
                                        </td>
                                        <td>
                                            <small>{{ $session->duration }}</small>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <button type="button" class="btn btn-outline-info" 
                                                        onclick="viewDetails({{ $session->id }})" title="상세">
                                                    <i class="fas fa-info-circle"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-danger" 
                                                        onclick="terminateSession({{ $session->id }})" title="종료">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-4">
                                            <i class="fas fa-desktop fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">활성 세션이 없습니다.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- 페이지네이션 -->
                    <div class="d-flex justify-content-center mt-4">
                        {{ $sessions->withQueryString()->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 상세 정보 모달 -->
<div class="modal fade" id="detailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">세션 상세 정보</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modalContent">
                <!-- AJAX로 내용 로드 -->
            </div>
        </div>
    </div>
</div>

<script>
// 전체 선택
document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.session-checkbox');
    checkboxes.forEach(checkbox => checkbox.checked = this.checked);
});

function terminateSession(sessionId) {
    if (!confirm('이 세션을 종료하시겠습니까?')) return;
    
    fetch(`/admin/auth/sessions/${sessionId}/terminate`, {
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

function bulkTerminate() {
    const selected = Array.from(document.querySelectorAll('.session-checkbox:checked'))
        .map(cb => cb.value);
    
    if (selected.length === 0) {
        alert('종료할 세션을 선택하세요.');
        return;
    }
    
    if (!confirm(`선택한 ${selected.length}개의 세션을 종료하시겠습니까?`)) return;
    
    fetch('/admin/auth/sessions/bulk-terminate', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ session_ids: selected })
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

function viewDetails(sessionId) {
    fetch(`/admin/auth/sessions/${sessionId}/details`)
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
</script>
@endsection