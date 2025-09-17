@php
    $providers = DB::table('user_oauth_providers')
        ->where('enable', 1)
        ->get();
@endphp

@if (count($providers) > 0)
    <div class="mt-4">
        <div class="text-center mb-3">
            <span class="text-muted">또는</span>
        </div>

        @if (session('error'))
            <div class="alert alert-warning alert-dismissible" role="alert">
                <div class="alert-message">
                    {{ session('error') }}
                </div>
            </div>
        @endif

        <div class="d-flex flex-column gap-2">
            @foreach ($providers as $provider)
                @if($provider->provider == 'google')
                    <a class="btn btn-outline-secondary w-100" href="{{ route('oauth.google') }}">
                        <i class="fab fa-google me-2"></i>
                        {{ $provider->name ?? 'Google' }}로 로그인
                    </a>
                @elseif($provider->provider == 'kakao')
                    <a class="btn btn-outline-warning w-100" href="{{ route('oauth.kakao') }}">
                        <i class="fas fa-comment me-2"></i>
                        {{ $provider->name ?? 'Kakao' }}로 로그인
                    </a>
                @elseif($provider->provider == 'naver')
                    <a class="btn btn-outline-success w-100" href="{{ route('oauth.naver') }}">
                        <i class="fas fa-leaf me-2"></i>
                        {{ $provider->name ?? 'Naver' }}로 로그인
                    </a>
                @elseif($provider->provider == 'facebook')
                    <a class="btn btn-outline-primary w-100" href="{{ route('oauth.facebook') }}">
                        <i class="fab fa-facebook me-2"></i>
                        {{ $provider->name ?? 'Facebook' }}로 로그인
                    </a>
                @elseif($provider->provider == 'github')
                    <a class="btn btn-outline-dark w-100" href="{{ route('oauth.github') }}">
                        <i class="fab fa-github me-2"></i>
                        {{ $provider->name ?? 'Github' }}로 로그인
                    </a>
                @else
                    <a class="btn btn-outline-secondary w-100" href="{{ route('oauth.' . $provider->provider) }}">
                        {{ $provider->name ?? ucfirst($provider->provider) }}로 로그인
                    </a>
                @endif
            @endforeach
        </div>
    </div>
@endif
