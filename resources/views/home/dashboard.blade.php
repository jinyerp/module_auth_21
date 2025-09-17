@extends('jiny-auth::layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- 사이드바 -->
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-center mb-4">
                        @if($user->avatar)
                            <img src="{{ asset('storage/' . $user->avatar) }}" class="rounded-circle" width="100" height="100" alt="Avatar">
                        @else
                            <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center" style="width: 100px; height: 100px; font-size: 40px;">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                        @endif
                        <h5 class="mt-3">{{ $user->name }}</h5>
                        <p class="text-muted">{{ $user->email }}</p>
                    </div>
                    
                    <div class="list-group">
                        <a href="{{ route('home') }}" class="list-group-item list-group-item-action active">
                            <i class="fas fa-tachometer-alt"></i> 대시보드
                        </a>
                        <a href="{{ route('home.profile') }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-user"></i> 프로필
                        </a>
                        <a href="{{ route('home.settings') }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-cog"></i> 설정
                        </a>
                        <a href="{{ route('home.account.password') }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-key"></i> 비밀번호 변경
                        </a>
                        <a href="{{ route('home.account.2fa') }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-shield-alt"></i> 2단계 인증
                        </a>
                        <a href="{{ route('home.account.sessions') }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-desktop"></i> 세션 관리
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- 메인 콘텐츠 -->
        <div class="col-md-9">
            <h1 class="mb-4">대시보드</h1>
            
            <!-- 보안 알림 -->
            @if(count($securityAlerts) > 0)
                <div class="mb-4">
                    @foreach($securityAlerts as $alert)
                        <div class="alert alert-{{ $alert['type'] }} alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle"></i> {{ $alert['message'] }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endforeach
                </div>
            @endif

            <!-- 계정 요약 -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="card-title text-muted">오늘 로그인</h6>
                            <h2>{{ $loginStats['today'] }}회</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="card-title text-muted">이번 주 로그인</h6>
                            <h2>{{ $loginStats['this_week'] }}회</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="card-title text-muted">이번 달 로그인</h6>
                            <h2>{{ $loginStats['this_month'] }}회</h2>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 계정 상태 정보 -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">계정 정보</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>이메일 인증:</strong> 
                                @if($accountInfo['email_verified'])
                                    <span class="badge bg-success">인증됨</span>
                                @else
                                    <span class="badge bg-warning">미인증</span>
                                @endif
                            </p>
                            <p><strong>2단계 인증:</strong> 
                                @if($accountInfo['two_factor_enabled'])
                                    <span class="badge bg-success">활성화</span>
                                @else
                                    <span class="badge bg-secondary">비활성화</span>
                                @endif
                            </p>
                            <p><strong>활성 세션:</strong> {{ $accountInfo['active_sessions'] }}개</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>계정 생성일:</strong> {{ $accountInfo['account_age'] }}</p>
                            <p><strong>마지막 비밀번호 변경:</strong> {{ \Carbon\Carbon::parse($accountInfo['last_password_change'])->format('Y-m-d H:i') }}</p>
                            <p><strong>마지막 로그인:</strong> {{ $user->last_login_at ? \Carbon\Carbon::parse($user->last_login_at)->diffForHumans() : '정보 없음' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 최근 활동 내역 -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">최근 활동 내역</h5>
                </div>
                <div class="card-body">
                    @if($recentActivities->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>활동</th>
                                        <th>설명</th>
                                        <th>IP 주소</th>
                                        <th>시간</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentActivities as $activity)
                                        <tr>
                                            <td>
                                                @switch($activity->action)
                                                    @case('login')
                                                        <span class="badge bg-success">로그인</span>
                                                        @break
                                                    @case('logout')
                                                        <span class="badge bg-info">로그아웃</span>
                                                        @break
                                                    @case('profile_update')
                                                        <span class="badge bg-warning">프로필 수정</span>
                                                        @break
                                                    @case('password_change')
                                                        <span class="badge bg-danger">비밀번호 변경</span>
                                                        @break
                                                    @default
                                                        <span class="badge bg-secondary">{{ $activity->action }}</span>
                                                @endswitch
                                            </td>
                                            <td>{{ $activity->description }}</td>
                                            <td>{{ $activity->ip_address }}</td>
                                            <td>{{ \Carbon\Carbon::parse($activity->created_at)->diffForHumans() }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted">최근 활동 내역이 없습니다.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection