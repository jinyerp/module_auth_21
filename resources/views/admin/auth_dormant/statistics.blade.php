@extends('jiny-admin::layouts.admin')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    {{-- 헤더 --}}
    <div class="md:flex md:items-center md:justify-between mb-6">
        <div class="flex-1 min-w-0">
            <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                휴면계정 통계
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                휴면계정 현황 및 처리 통계를 확인합니다
            </p>
        </div>
        <div class="mt-4 flex md:mt-0 md:ml-4 space-x-3">
            <a href="{{ route('admin.auth.dormant') }}" 
               class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                목록으로
            </a>
            <button onclick="window.print()" 
                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                </svg>
                인쇄
            </button>
        </div>
    </div>

    {{-- 현재 통계 카드 --}}
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-5 mb-8">
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <dt class="text-sm font-medium text-gray-500 truncate">
                    총 휴면계정
                </dt>
                <dd class="mt-1 text-3xl font-semibold text-gray-900">
                    {{ number_format($currentStats['total_dormant']) }}
                </dd>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <dt class="text-sm font-medium text-gray-500 truncate">
                    알림 발송됨
                </dt>
                <dd class="mt-1 text-3xl font-semibold text-yellow-600">
                    {{ number_format($currentStats['total_notified']) }}
                </dd>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <dt class="text-sm font-medium text-gray-500 truncate">
                    삭제 예정
                </dt>
                <dd class="mt-1 text-3xl font-semibold text-red-600">
                    {{ number_format($currentStats['pending_deletion']) }}
                </dd>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <dt class="text-sm font-medium text-gray-500 truncate">
                    이번달 재활성화
                </dt>
                <dd class="mt-1 text-3xl font-semibold text-green-600">
                    {{ number_format($currentStats['reactivated_this_month']) }}
                </dd>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <dt class="text-sm font-medium text-gray-500 truncate">
                    평균 휴면 기간
                </dt>
                <dd class="mt-1 text-3xl font-semibold text-blue-600">
                    {{ number_format($currentStats['avg_dormant_days']) }}일
                </dd>
            </div>
        </div>
    </div>

    {{-- 월별 통계 테이블 --}}
    <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-8">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">
                월별 휴면계정 처리 현황
            </h3>
            <p class="mt-1 max-w-2xl text-sm text-gray-500">
                최근 12개월간 휴면계정 처리 통계
            </p>
        </div>
        <div class="border-t border-gray-200">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            년월
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            전체
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            휴면
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            알림발송
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            재활성화
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            삭제됨
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($monthlyStats as $stat)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ $stat->month }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ number_format($stat->total) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <span class="text-gray-600">{{ number_format($stat->dormant) }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <span class="text-yellow-600">{{ number_format($stat->notified) }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <span class="text-green-600">{{ number_format($stat->reactivated) }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <span class="text-red-600">{{ number_format($stat->deleted) }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                            통계 데이터가 없습니다
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- 휴면 사유 분포 --}}
    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">
                휴면 사유 분포
            </h3>
            <p class="mt-1 max-w-2xl text-sm text-gray-500">
                현재 휴면/알림발송 상태 계정의 휴면 사유 분포
            </p>
        </div>
        <div class="border-t border-gray-200">
            <div class="px-4 py-5 sm:px-6">
                @if($reasonsStats->count() > 0)
                <div class="space-y-3">
                    @php
                        $totalReasons = $reasonsStats->sum('count');
                        $reasonLabels = [
                            'inactivity' => '장기 미접속',
                            'request' => '사용자 요청',
                            'policy' => '정책 위반',
                            'security' => '보안 문제',
                            'other' => '기타'
                        ];
                    @endphp
                    @foreach($reasonsStats as $reason)
                    @php
                        $percentage = $totalReasons > 0 ? round(($reason->count / $totalReasons) * 100, 1) : 0;
                    @endphp
                    <div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">{{ $reasonLabels[$reason->reason] ?? $reason->reason }}</span>
                            <span class="text-gray-900 font-medium">{{ number_format($reason->count) }} ({{ $percentage }}%)</span>
                        </div>
                        <div class="mt-1 w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-sm text-gray-500 text-center py-4">
                    휴면 사유 데이터가 없습니다
                </p>
                @endif
            </div>
        </div>
    </div>

    {{-- 추가 정보 --}}
    <div class="mt-8 bg-blue-50 border border-blue-200 rounded-md p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="ml-3 flex-1 md:flex md:justify-between">
                <p class="text-sm text-blue-700">
                    개인정보보호법에 따라 휴면계정은 정해진 기간 후 자동으로 삭제되며, 삭제 전 사용자에게 알림을 발송해야 합니다.
                </p>
                <p class="mt-3 text-sm md:mt-0 md:ml-6">
                    <a href="{{ route('admin.auth.dormant.settings') }}" class="whitespace-nowrap font-medium text-blue-700 hover:text-blue-600">
                        설정 관리 <span aria-hidden="true">→</span>
                    </a>
                </p>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    @media print {
        .no-print {
            display: none;
        }
    }
</style>
@endpush
@endsection