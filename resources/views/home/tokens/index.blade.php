@extends('jiny-auth::layouts.app')

@section('title', '내 JWT 토큰')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>JWT 토큰 관리</h2>
                <div>
                    <a href="{{ route('home.account.tokens.active') }}" class="btn btn-outline-info">
                        <i class="fas fa-check-circle me-2"></i>활성 토큰
                    </a>
                    <a href="{{ route('home.account.tokens.history') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-history me-2"></i>사용 이력
                    </a>
                </div>
            </div>

            <!-- 통계 카드 -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title">{{ $statistics['total_tokens'] }}</h5>
                            <p class="card-text text-muted">전체 토큰</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title text-success">{{ $statistics['active_tokens'] }}</h5>
                            <p class="card-text text-muted">활성 토큰</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title text-warning">{{ $statistics['expired_tokens'] }}</h5>
                            <p class="card-text text-muted">만료된 토큰</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title text-danger">{{ $statistics['revoked_tokens'] }}</h5>
                            <p class="card-text text-muted">무효화된 토큰</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 토큰 목록 -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">토큰 목록</h5>
                    @if($statistics['active_tokens'] > 1)
                        <button class="btn btn-sm btn-danger" onclick="revokeAllTokens()">
                            <i class="fas fa-times-circle me-1"></i>모든 토큰 무효화
                        </button>
                    @endif
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>이름</th>
                                    <th>타입</th>
                                    <th>디바이스</th>
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
                                        <td>
                                            <strong>{{ $token->name ?: 'Unnamed Token' }}</strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $token->token_type == 'access' ? 'primary' : 'info' }}">
                                                {{ strtoupper($token->token_type) }}
                                            </span>
                                        </td>
                                        <td>
                                            {{ $token->device ?: '-' }}
                                        </td>
                                        <td>
                                            @if($token->is_active)
                                                <span class="badge bg-success">활성</span>
                                            @elseif($token->is_revoked)
                                                <span class="badge bg-danger">무효화</span>
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
                                            <small>{{ $token->expires_at_formatted }}</small>
                                        </td>
                                        <td>
                                            @if($token->is_active)
                                                <button class="btn btn-sm btn-outline-danger" 
                                                        onclick="revokeToken({{ $token->id }})">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
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
                        {{ $tokens->links() }}
                    </div>
                </div>
            </div>

            <!-- 보안 알림 -->
            <div class="alert alert-info mt-4">
                <h6 class="alert-heading">
                    <i class="fas fa-info-circle me-2"></i>JWT 토큰 관리 안내
                </h6>
                <ul class="mb-0">
                    <li>사용하지 않는 토큰은 보안을 위해 무효화하는 것이 좋습니다.</li>
                    <li>토큰이 유출되었다고 의심되면 즉시 무효화하고 새로 생성하세요.</li>
                    <li>만료된 토큰은 자동으로 사용할 수 없게 됩니다.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
function revokeToken(tokenId) {
    if (!confirm('이 토큰을 무효화하시겠습니까?')) return;
    
    fetch(`/home/account/tokens/${tokenId}`, {
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
        alert('토큰 무효화 중 오류가 발생했습니다.');
    });
}

function revokeAllTokens() {
    if (!confirm('현재 토큰을 제외한 모든 토큰을 무효화하시겠습니까?')) return;
    
    fetch('/home/account/tokens/revoke-all', {
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
        alert('토큰 무효화 중 오류가 발생했습니다.');
    });
}
</script>
@endsection