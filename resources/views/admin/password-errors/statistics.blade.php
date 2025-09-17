@extends('jiny-auth::layouts.admin')

@section('title', '비밀번호 오류 통계')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">비밀번호 오류 통계</h1>
        <p class="text-gray-600 mt-2">비밀번호 오류 시도에 대한 상세한 통계 정보를 확인할 수 있습니다.</p>
    </div>

    <!-- 기간 선택 -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <form method="GET" action="{{ route('admin.auth.password-errors.statistics') }}" class="flex items-center space-x-4">
            <label for="period" class="text-sm font-medium text-gray-700">통계 기간:</label>
            <select name="period" id="period" onchange="this.form.submit()"
                    class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="24h" {{ request('period', '24h') === '24h' ? 'selected' : '' }}>최근 24시간</option>
                <option value="7d" {{ request('period') === '7d' ? 'selected' : '' }}>최근 7일</option>
                <option value="30d" {{ request('period') === '30d' ? 'selected' : '' }}>최근 30일</option>
                <option value="90d" {{ request('period') === '90d' ? 'selected' : '' }}>최근 90일</option>
            </select>
        </form>
    </div>

    <!-- 주요 통계 카드 -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-red-100 text-red-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">총 오류</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ number_format($statistics['total_errors']) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">고유 이메일</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ number_format($statistics['unique_emails']) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">고유 IP</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ number_format($statistics['unique_ips']) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">잠금된 계정</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ number_format($statistics['locked_accounts']) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- 차트 및 상세 통계 -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- 오류 유형별 분포 -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">오류 유형별 분포</h3>
            <div class="space-y-3">
                @foreach($statistics['error_types'] as $type => $count)
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-3 h-3 rounded-full mr-3
                            @switch($type)
                                @case('wrong_password')
                                    bg-red-500
                                    @break
                                @case('account_not_found')
                                    bg-yellow-500
                                    @break
                                @case('account_locked')
                                    bg-orange-500
                                    @break
                                @default
                                    bg-gray-500
                            @endswitch">
                        </div>
                        <span class="text-sm text-gray-700">
                            @switch($type)
                                @case('wrong_password')
                                    잘못된 비밀번호
                                    @break
                                @case('account_not_found')
                                    계정 없음
                                    @break
                                @case('account_locked')
                                    계정 잠금
                                    @break
                                @default
                                    {{ $type }}
                            @endswitch
                        </span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="text-sm font-medium text-gray-900">{{ number_format($count) }}</span>
                        <span class="text-sm text-gray-500">
                            ({{ $statistics['total_errors'] > 0 ? round(($count / $statistics['total_errors']) * 100, 1) : 0 }}%)
                        </span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- 영구 잠금 계정 -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">영구 잠금 계정</h3>
            <div class="text-center">
                <div class="text-3xl font-bold text-red-600 mb-2">
                    {{ number_format($statistics['permanently_locked']) }}
                </div>
                <p class="text-sm text-gray-600">영구 잠금된 계정 수</p>
                <p class="text-xs text-gray-500 mt-1">
                    {{ config('admin.auth.login.permanent_lockout_attempts', 25) }}회 이상 시도
                </p>
            </div>
        </div>
    </div>

    <!-- 상위 이메일 및 IP -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- 상위 오류 이메일 -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">상위 오류 이메일</h3>
            <div class="space-y-3">
                @forelse($statistics['top_emails'] as $email => $count)
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-sm font-medium">
                            {{ $loop->iteration }}
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $email }}</p>
                            <p class="text-xs text-gray-500">{{ $count }}회 시도</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="text-sm font-medium text-gray-900">{{ number_format($count) }}</span>
                    </div>
                </div>
                @empty
                <p class="text-gray-500 text-center py-4">데이터가 없습니다.</p>
                @endforelse
            </div>
        </div>

        <!-- 상위 오류 IP -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">상위 오류 IP</h3>
            <div class="space-y-3">
                @forelse($statistics['top_ips'] as $ip => $count)
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-green-100 text-green-600 rounded-full flex items-center justify-center text-sm font-medium">
                            {{ $loop->iteration }}
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $ip }}</p>
                            <p class="text-xs text-gray-500">{{ $count }}회 시도</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="text-sm font-medium text-gray-900">{{ number_format($count) }}</span>
                    </div>
                </div>
                @empty
                <p class="text-gray-500 text-center py-4">데이터가 없습니다.</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- 시간대별 통계 -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">시간대별 오류 발생 현황</h3>
        <div class="grid grid-cols-6 gap-4">
            @php
                $hours = [];
                for ($i = 0; $i < 24; $i++) {
                    $hours[$i] = 0;
                }

                // 실제 데이터가 있다면 시간대별 통계를 계산
                // 여기서는 예시 데이터를 사용
            @endphp

            @foreach($hours as $hour => $count)
            <div class="text-center p-3 bg-gray-50 rounded-lg">
                <div class="text-lg font-semibold text-gray-900">{{ $hour }}시</div>
                <div class="text-sm text-gray-600">{{ $count }}</div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- 보안 권장사항 -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">보안 권장사항</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-3">
                <div class="flex items-start space-x-3">
                    <div class="w-6 h-6 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-sm font-medium mt-0.5">
                        1
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900">계정 잠금 정책 강화</p>
                        <p class="text-xs text-gray-600">연속 실패 시 계정 잠금 시간을 단계적으로 증가</p>
                    </div>
                </div>

                <div class="flex items-start space-x-3">
                    <div class="w-6 h-6 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-sm font-medium mt-0.5">
                        2
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900">2단계 인증 활성화</p>
                        <p class="text-xs text-gray-600">중요 계정에 SMS 또는 이메일 인증 추가</p>
                    </div>
                </div>

                <div class="flex items-start space-x-3">
                    <div class="w-6 h-6 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-sm font-medium mt-0.5">
                        3
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900">IP 기반 차단</p>
                        <p class="text-xs text-gray-600">의심스러운 IP에서의 접근 차단</p>
                    </div>
                </div>
            </div>

            <div class="space-y-3">
                <div class="flex items-start space-x-3">
                    <div class="w-6 h-6 bg-green-100 text-green-600 rounded-full flex items-center justify-center text-sm font-medium mt-0.5">
                        4
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900">비밀번호 정책 강화</p>
                        <p class="text-xs text-gray-600">복잡한 비밀번호 요구사항 설정</p>
                    </div>
                </div>

                <div class="flex items-start space-x-3">
                    <div class="w-6 h-6 bg-green-100 text-green-600 rounded-full flex items-center justify-center text-sm font-medium mt-0.5">
                        5
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900">정기적인 모니터링</p>
                        <p class="text-xs text-gray-600">일일/주간 보안 보고서 생성</p>
                    </div>
                </div>

                <div class="flex items-start space-x-3">
                    <div class="w-6 h-6 bg-green-100 text-green-600 rounded-full flex items-center justify-center text-sm font-medium mt-0.5">
                        6
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900">사용자 교육</p>
                        <p class="text-xs text-gray-600">보안 인식 향상을 위한 정기 교육</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 액션 버튼 -->
    <div class="flex justify-center space-x-4">
        <a href="{{ route('admin.auth.password-errors.index') }}"
           class="bg-gray-600 hover:bg-gray-700 text-white font-medium py-2 px-6 rounded-md transition duration-150 ease-in-out">
            목록으로 돌아가기
        </a>
        <a href="{{ route('admin.auth.password-errors.locked') }}"
           class="bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-6 rounded-md transition duration-150 ease-in-out">
            잠금된 계정 관리
        </a>
        <a href="{{ route('admin.auth.password-errors.permanently-locked') }}"
           class="bg-orange-600 hover:bg-orange-700 text-white font-medium py-2 px-6 rounded-md transition duration-150 ease-in-out">
            영구 잠금 계정 관리
        </a>
    </div>
</div>

<script>
// 차트 라이브러리가 있다면 여기에 차트를 그리는 코드를 추가할 수 있습니다
document.addEventListener('DOMContentLoaded', function() {
    // 기간 변경 시 자동 새로고침
    document.getElementById('period').addEventListener('change', function() {
        this.form.submit();
    });
});
</script>
@endsection
