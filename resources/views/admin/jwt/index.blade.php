@extends('jiny-auth::layouts.admin')

@section('title', 'JWT 토큰 관리')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1 class="h3">JWT 토큰 관리</h1>
                <div>
                    <a href="{{ route('admin.auth.jwt.settings') }}" class="btn btn-outline-primary">
                        <i class="fas fa-cog me-2"></i>설정
                    </a>
                    <a href="{{ route('admin.auth.jwt.statistics') }}" class="btn btn-info">
                        <i class="fas fa-chart-bar me-2"></i>통계
                    </a>
                </div>
            </div>

            <!-- 통계 카드 -->
            <div class="row mb-4">
                <div class="col-md-2">
                    <div class="card">
                        <div class="card-body text-center">
                            <h4 class="mb-0">{{ $statistics['total_tokens'] }}</h4>
                            <small class="text-muted">전체 토큰</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card">
                        <div class="card-body text-center">
                            <h4 class="mb-0 text-success">{{ $statistics['active_tokens'] }}</h4>
                            <small class="text-muted">활성 토큰</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card">
                        <div class="card-body text-center">
                            <h4 class="mb-0 text-warning">{{ $statistics['expired_tokens'] }}</h4>
                            <small class="text-muted">만료됨</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card">
                        <div class="card-body text-center">
                            <h4 class="mb-0 text-danger">{{ $statistics['revoked_tokens'] }}</h4>
                            <small class="text-muted">무효화됨</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card">
                        <div class="card-body text-center">
                            <h4 class="mb-0">{{ $statistics['unique_users'] }}</h4>
                            <small class="text-muted">사용자</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card">
                        <div class="card-body text-center">
                            <h4 class="mb-0">{{ $statistics['recent_24h'] }}</h4>
                            <small class="text-muted">24시간</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 검색 및 필터 -->
            <div class="card mb-3">
                <div class="card-body">
                    <form action="{{ route('admin.auth.jwt.tokens') }}" method="GET" class="row g-3">
                        <div class="col-md-3">
                            <input type="text" class="form-control" name="search" 
                                   placeholder="사용자, 이름, 디바이스 검색" value="{{ request('search') }}">
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" name="status">
                                <option value="">모든 상태</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>활성</option>
                                <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>만료</option>
                                <option value="revoked" {{ request('status') == 'revoked' ? 'selected' : '' }}>무효화</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" name="token_type">
                                <option value="">모든 타입</option>
                                <option value="access" {{ request('token_type') == 'access' ? 'selected' : '' }}>Access</option>
                                <option value="refresh" {{ request('token_type') == 'refresh' ? 'selected' : '' }}>Refresh</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search me-1"></i>검색
                            </button>
                        </div>
                        <div class="col-md-3">
                            <div class="btn-group w-100" role="group">
                                <a href="{{ route('admin.auth.jwt.tokens.active') }}" class="btn btn-outline-success">
                                    활성
                                </a>
                                <a href="{{ route('admin.auth.jwt.tokens.expired') }}" class="btn btn-outline-warning">
                                    만료
                                </a>
                                <button type="button" class="btn btn-danger" onclick="revokeAllTokens()">
                                    전체 무효화
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- 토큰 목록 -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>사용자</th>
                                    <th>토큰 정보</th>
                                    <th>타입</th>
                                    <th>상태</th>
                                    <th>마지막 사용</th>
                                    <th>생성일</th>
                                    <th>만료일</th>
                                    <th>작업</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($tokens as $token)
                                    <tr class="{{ !$token->is_active ? 'table-secondary' : '' }}">
                                        <td>{{ $token->id }}</td>
                                        <td>
                                            <strong>{{ $token->name }}</strong><br>
                                            <small class="text-muted">{{ $token->email }}</small>
                                        </td>
                                        <td>
                                            <strong>{{ $token->name ?: 'Unnamed' }}</strong><br>
                                            <small class="text-muted">{{ $token->device ?: 'Unknown Device' }}</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $token->token_type == 'access' ? 'primary' : 'info' }}">
                                                {{ strtoupper($token->token_type) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($token->is_active)
                                                <span class="badge bg-success">활성</span>
                                            @elseif($token->is_revoked)
                                                <span class="badge bg-danger">무효화</span>
                                                @if($token->revoked_reason)
                                                    <br><small class="text-muted">{{ $token->revoked_reason }}</small>
                                                @endif
                                            @elseif($token->is_expired)
                                                <span class="badge bg-warning">만료</span>
                                            @endif
                                        </td>
                                        <td>
                                            <small>{{ $token->last_used_human }}</small>
                                        </td>
                                        <td>
                                            <small>{{ $token->created_at_formatted }}</small>
                                        </td>
                                        <td>
                                            @if($token->expires_at)
                                                <small>{{ \Carbon\Carbon::parse($token->expires_at)->format('Y-m-d H:i') }}</small>
                                            @else
                                                <span class="text-muted">무제한</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="{{ route('admin.auth.jwt.tokens.show', $token->id) }}" 
                                                   class="btn btn-outline-info" title="상세">
                                                    <i class="fas fa-info-circle"></i>
                                                </a>
                                                @if($token->is_active)
                                                    <button class="btn btn-outline-danger" 
                                                            onclick="revokeToken({{ $token->id }})" title="무효화">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-4">
                                            <i class="fas fa-key fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">토큰이 없습니다.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- 페이지네이션 -->
                    <div class="d-flex justify-content-center mt-4">
                        {{ $tokens->withQueryString()->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function revokeToken(tokenId) {
    if (!confirm('이 토큰을 무효화하시겠습니까?')) return;
    
    fetch(`/admin/auth/jwt/tokens/${tokenId}`, {
        method: 'DELETE',
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

function revokeAllTokens() {
    if (!confirm('모든 활성 토큰을 무효화하시겠습니까? 이 작업은 되돌릴 수 없습니다.')) return;
    
    fetch('/admin/auth/jwt/tokens/revoke-all', {
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

function revokeUserTokens(userId, userName) {
    if (!confirm(`${userName}님의 모든 토큰을 무효화하시겠습니까?`)) return;
    
    fetch(`/admin/auth/jwt/tokens/revoke-user/${userId}`, {
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
</script>
@endsection