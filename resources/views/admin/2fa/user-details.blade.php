<div class="user-details">
    <div class="mb-4">
        <h6 class="text-muted">사용자 정보</h6>
        <dl class="row">
            <dt class="col-sm-4">이름</dt>
            <dd class="col-sm-8">{{ $user->name }}</dd>
            
            <dt class="col-sm-4">이메일</dt>
            <dd class="col-sm-8">{{ $user->email }}</dd>
            
            <dt class="col-sm-4">2FA 상태</dt>
            <dd class="col-sm-8">
                @if($user->two_factor_secret && $user->two_factor_enabled)
                    <span class="badge bg-success">활성화</span>
                @else
                    <span class="badge bg-secondary">비활성화</span>
                @endif
            </dd>
            
            <dt class="col-sm-4">가입일</dt>
            <dd class="col-sm-8">{{ $user->created_at->format('Y-m-d H:i') }}</dd>
        </dl>
    </div>
    
    <div class="mb-4">
        <h6 class="text-muted">복구 코드 상태</h6>
        @if($recoveryCodes->count() > 0)
            <div class="row">
                <div class="col-6">
                    <div class="text-center">
                        <div class="display-6 text-primary">{{ $recoveryCodes->whereNull('used_at')->count() }}</div>
                        <small class="text-muted">사용 가능</small>
                    </div>
                </div>
                <div class="col-6">
                    <div class="text-center">
                        <div class="display-6 text-secondary">{{ $recoveryCodes->whereNotNull('used_at')->count() }}</div>
                        <small class="text-muted">사용됨</small>
                    </div>
                </div>
            </div>
        @else
            <p class="text-muted">복구 코드가 생성되지 않았습니다.</p>
        @endif
    </div>
    
    <div>
        <h6 class="text-muted">최근 2FA 활동</h6>
        @if($recentLogins->count() > 0)
            <ul class="list-group list-group-flush">
                @foreach($recentLogins as $log)
                    <li class="list-group-item px-0">
                        <div class="d-flex justify-content-between">
                            <span>
                                @if($log->action === 'login_2fa_success')
                                    <i class="fas fa-check-circle text-success me-2"></i>로그인 성공
                                @else
                                    <i class="fas fa-times-circle text-danger me-2"></i>로그인 실패
                                @endif
                            </span>
                            <small class="text-muted">{{ \Carbon\Carbon::parse($log->created_at)->diffForHumans() }}</small>
                        </div>
                        <small class="text-muted">IP: {{ $log->ip_address }}</small>
                    </li>
                @endforeach
            </ul>
        @else
            <p class="text-muted">2FA 활동 기록이 없습니다.</p>
        @endif
    </div>
</div>