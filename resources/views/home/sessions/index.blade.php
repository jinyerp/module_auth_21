@extends('jiny-auth::layouts.app')

@section('title', '내 세션 관리')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>내 활성 세션</h2>
                @if($sessions->count() > 1)
                    <button class="btn btn-danger" onclick="terminateAllSessions()">
                        <i class="fas fa-sign-out-alt me-2"></i>다른 모든 세션 종료
                    </button>
                @endif
            </div>

            <!-- 세션 통계 -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title">{{ $statistics['total_sessions'] }}</h5>
                            <p class="card-text text-muted">전체 세션</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title">{{ $statistics['desktop_sessions'] }}</h5>
                            <p class="card-text text-muted">데스크탑</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title">{{ $statistics['mobile_sessions'] }}</h5>
                            <p class="card-text text-muted">모바일</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title">{{ $statistics['tablet_sessions'] }}</h5>
                            <p class="card-text text-muted">태블릿</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 세션 목록 -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">활성 세션 목록</h5>
                </div>
                <div class="card-body">
                    @forelse($sessions as $session)
                        <div class="session-item border-bottom pb-3 mb-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="d-flex align-items-center mb-2">
                                        @if($session->device_info)
                                            <i class="{{ $session->device_info['icon'] }} fa-2x me-3 text-primary"></i>
                                        @endif
                                        <div>
                                            <h6 class="mb-1">
                                                {{ $session->device_info['browser'] ?? 'Unknown' }} on 
                                                {{ $session->device_info['platform'] ?? 'Unknown' }}
                                                @if($session->is_current)
                                                    <span class="badge bg-success ms-2">현재 세션</span>
                                                @endif
                                            </h6>
                                            <small class="text-muted">
                                                <i class="fas fa-map-marker-alt me-1"></i>{{ $session->ip_address }}
                                                @if($session->location)
                                                    - {{ $session->location }}
                                                @endif
                                            </small>
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        <small class="text-muted">
                                            <i class="fas fa-clock me-1"></i>로그인: {{ $session->login_at_formatted }}
                                            <span class="mx-2">|</span>
                                            <i class="fas fa-history me-1"></i>마지막 활동: {{ $session->last_activity_human }}
                                        </small>
                                    </div>
                                </div>
                                <div class="text-end">
                                    @if(!$session->is_current)
                                        <button class="btn btn-sm btn-outline-danger" 
                                                onclick="terminateSession({{ $session->id }})">
                                            <i class="fas fa-times me-1"></i>종료
                                        </button>
                                    @endif
                                    <button class="btn btn-sm btn-outline-info" 
                                            onclick="viewSessionDetails({{ $session->id }})">
                                        <i class="fas fa-info-circle me-1"></i>상세
                                    </button>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4">
                            <i class="fas fa-desktop fa-3x text-muted mb-3"></i>
                            <p class="text-muted">활성 세션이 없습니다.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- 보안 팁 -->
            <div class="alert alert-info mt-4">
                <h6 class="alert-heading">
                    <i class="fas fa-shield-alt me-2"></i>보안 팁
                </h6>
                <ul class="mb-0">
                    <li>인식하지 못하는 세션이 있다면 즉시 종료하세요.</li>
                    <li>정기적으로 사용하지 않는 세션을 확인하고 종료하세요.</li>
                    <li>공용 컴퓨터에서 로그인한 경우 사용 후 반드시 로그아웃하세요.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- 세션 상세 정보 모달 -->
<div class="modal fade" id="sessionDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">세션 상세 정보</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="sessionDetailsContent">
                <!-- AJAX로 내용 로드 -->
            </div>
        </div>
    </div>
</div>

<script>
function terminateSession(sessionId) {
    if (!confirm('이 세션을 종료하시겠습니까?')) return;
    
    fetch(`/home/account/sessions/${sessionId}/terminate`, {
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
        alert('세션 종료 중 오류가 발생했습니다.');
    });
}

function terminateAllSessions() {
    if (!confirm('현재 세션을 제외한 모든 세션을 종료하시겠습니까?')) return;
    
    fetch('/home/account/sessions/terminate-all', {
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
        alert('세션 종료 중 오류가 발생했습니다.');
    });
}

function viewSessionDetails(sessionId) {
    fetch(`/home/account/sessions/${sessionId}/details`)
        .then(response => response.text())
        .then(html => {
            document.getElementById('sessionDetailsContent').innerHTML = html;
            new bootstrap.Modal(document.getElementById('sessionDetailsModal')).show();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('세션 정보를 불러올 수 없습니다.');
        });
}
</script>
@endsection