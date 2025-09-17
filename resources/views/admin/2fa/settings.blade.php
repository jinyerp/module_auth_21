@extends('jiny-auth::layouts.admin')

@section('title', '2FA 설정 관리')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1 class="h3 mb-3">2단계 인증 (2FA) 설정 관리</h1>
            
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- 통계 카드 -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-users fa-2x text-primary"></i>
                                </div>
                                <div class="ms-3">
                                    <p class="mb-0 text-muted">전체 사용자</p>
                                    <h4 class="mb-0">{{ number_format($statistics['total_users']) }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-shield-alt fa-2x text-success"></i>
                                </div>
                                <div class="ms-3">
                                    <p class="mb-0 text-muted">2FA 활성화</p>
                                    <h4 class="mb-0">{{ number_format($statistics['enabled_users']) }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-percentage fa-2x text-info"></i>
                                </div>
                                <div class="ms-3">
                                    <p class="mb-0 text-muted">활성화율</p>
                                    <h4 class="mb-0">{{ $statistics['enabled_percentage'] }}%</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-key fa-2x text-warning"></i>
                                </div>
                                <div class="ms-3">
                                    <p class="mb-0 text-muted">복구 코드 사용</p>
                                    <h4 class="mb-0">{{ number_format($statistics['recovery_codes_used']) }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 설정 폼 -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">2FA 시스템 설정</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.2fa.settings.update') }}" method="POST">
                        @csrf
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="enabled" name="enabled" value="1" 
                                        {{ $settings['enabled'] ? 'checked' : '' }}>
                                    <label class="form-check-label" for="enabled">
                                        <strong>2FA 시스템 활성화</strong><br>
                                        <small class="text-muted">사용자가 2FA를 설정할 수 있도록 허용합니다.</small>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="enforced" name="enforced" value="1"
                                        {{ $settings['enforced'] ? 'checked' : '' }}>
                                    <label class="form-check-label" for="enforced">
                                        <strong>2FA 강제 적용</strong><br>
                                        <small class="text-muted">모든 사용자에게 2FA 사용을 필수로 지정합니다.</small>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="grace_period" class="form-label">유예 기간 (일)</label>
                                <input type="number" class="form-control" id="grace_period" name="grace_period" 
                                    value="{{ $settings['grace_period'] }}" min="0" max="30" required>
                                <small class="text-muted">2FA 강제 적용 시 사용자에게 주어지는 설정 유예 기간</small>
                            </div>
                            <div class="col-md-4">
                                <label for="recovery_codes_count" class="form-label">복구 코드 개수</label>
                                <input type="number" class="form-control" id="recovery_codes_count" name="recovery_codes_count" 
                                    value="{{ $settings['recovery_codes_count'] }}" min="4" max="20" required>
                                <small class="text-muted">사용자에게 제공할 복구 코드 개수</small>
                            </div>
                            <div class="col-md-4">
                                <label for="remember_days" class="form-label">장치 기억 일수</label>
                                <input type="number" class="form-control" id="remember_days" name="remember_days" 
                                    value="{{ $settings['remember_days'] }}" min="1" max="365" required>
                                <small class="text-muted">신뢰할 수 있는 장치로 기억하는 일수</small>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.2fa.users') }}" class="btn btn-secondary">
                                <i class="fas fa-users me-2"></i>사용자 목록 보기
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>설정 저장
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- 추가 작업 -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">관리 작업</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>일괄 작업</h6>
                            <div class="d-grid gap-2">
                                <button class="btn btn-outline-warning" onclick="if(confirm('모든 사용자에게 2FA 활성화를 요청하시겠습니까?')) { requestAllUsers2FA(); }">
                                    <i class="fas fa-bell me-2"></i>전체 사용자 2FA 활성화 요청
                                </button>
                                <button class="btn btn-outline-info" onclick="exportStatistics()">
                                    <i class="fas fa-download me-2"></i>2FA 통계 다운로드
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6>빠른 통계</h6>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-success me-2"></i>오늘 2FA 활성화: <span id="today-enabled">0</span>명</li>
                                <li><i class="fas fa-times text-danger me-2"></i>오늘 2FA 비활성화: <span id="today-disabled">0</span>명</li>
                                <li><i class="fas fa-sign-in-alt text-info me-2"></i>오늘 2FA 로그인: <span id="today-logins">0</span>회</li>
                                <li><i class="fas fa-exclamation-triangle text-warning me-2"></i>오늘 2FA 실패: <span id="today-failures">0</span>회</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function requestAllUsers2FA() {
    fetch('{{ route('admin.2fa.request-all') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        alert(data.message);
        location.reload();
    })
    .catch(error => {
        console.error('Error:', error);
        alert('요청 처리 중 오류가 발생했습니다.');
    });
}

function exportStatistics() {
    window.location.href = '{{ route('admin.2fa.statistics') }}';
}

// 실시간 통계 업데이트
function updateStatistics() {
    fetch('{{ route('admin.2fa.statistics') }}')
        .then(response => response.json())
        .then(data => {
            // 오늘 통계 업데이트
            const today = new Date().toISOString().split('T')[0];
            const todayStats = data.daily_stats[today] || {};
            
            document.getElementById('today-enabled').textContent = todayStats.enabled || 0;
            document.getElementById('today-logins').textContent = todayStats.logins || 0;
        })
        .catch(error => console.error('Error fetching statistics:', error));
}

// 페이지 로드 시 통계 업데이트
document.addEventListener('DOMContentLoaded', updateStatistics);

// 5분마다 통계 업데이트
setInterval(updateStatistics, 300000);
</script>
@endsection