@extends('jiny-auth::layouts.admin')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="py-6">
        {{-- 헤더 --}}
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">국가별 통계</h1>
                <p class="mt-1 text-sm text-gray-600">국가 및 사용자 분포 현황</p>
            </div>
            <a href="{{ route('admin.auth.countries') }}" 
               class="inline-flex items-center px-4 py-2 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M11 17l-5-5m0 0l5-5m-5 5h12"/>
                </svg>
                목록으로
            </a>
        </div>

        {{-- 요약 카드 --}}
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-xs font-medium text-gray-500 truncate">전체 국가</dt>
                                <dd class="mt-1 text-2xl font-semibold text-gray-900">
                                    {{ number_format($stats['total_countries']) }}
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-xs font-medium text-gray-500 truncate">활성 국가</dt>
                                <dd class="mt-1 text-2xl font-semibold text-green-600">
                                    {{ number_format($stats['active_countries']) }}
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-xs font-medium text-gray-500 truncate">비활성 국가</dt>
                                <dd class="mt-1 text-2xl font-semibold text-red-600">
                                    {{ number_format($stats['inactive_countries']) }}
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-xs font-medium text-gray-500 truncate">활성화 비율</dt>
                                <dd class="mt-1 text-2xl font-semibold text-blue-600">
                                    {{ $stats['total_countries'] > 0 ? round(($stats['active_countries'] / $stats['total_countries']) * 100, 1) : 0 }}%
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 대륙별 통계 --}}
        <div class="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-2">
            {{-- 대륙별 국가 수 --}}
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-sm font-medium text-gray-900 mb-4">대륙별 국가 분포</h3>
                    @if(count($stats['by_region']) > 0)
                    <div class="space-y-3">
                        @php
                            $maxCount = $stats['by_region']->max('count');
                        @endphp
                        @foreach($stats['by_region'] as $region)
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-xs font-medium text-gray-700">{{ $region->region }}</span>
                                <span class="text-xs text-gray-500">{{ $region->count }}개국</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="h-2 rounded-full 
                                     @switch($region->region)
                                         @case('Asia') bg-yellow-500 @break
                                         @case('Europe') bg-blue-500 @break
                                         @case('Americas') bg-green-500 @break
                                         @case('Africa') bg-orange-500 @break
                                         @case('Oceania') bg-purple-500 @break
                                         @default bg-gray-500
                                     @endswitch"
                                     style="width: {{ $maxCount > 0 ? ($region->count / $maxCount * 100) : 0 }}%">
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <p class="text-xs text-gray-500">데이터가 없습니다</p>
                    @endif
                </div>
            </div>

            {{-- 통화별 통계 --}}
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-sm font-medium text-gray-900 mb-4">주요 통화별 국가 수</h3>
                    @if(count($stats['by_currency']) > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">통화</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">통화명</th>
                                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-600 uppercase">국가 수</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($stats['by_currency'] as $currency)
                                <tr>
                                    <td class="px-3 py-2 whitespace-nowrap text-xs font-medium text-gray-900">
                                        {{ $currency->currency_code }}
                                    </td>
                                    <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-500">
                                        {{ $currency->currency_name ?: '-' }}
                                    </td>
                                    <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-900 text-right">
                                        {{ $currency->count }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="text-xs text-gray-500">데이터가 없습니다</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- 사용자 수 상위 국가 --}}
        <div class="mt-8">
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-sm font-medium text-gray-900 mb-4">사용자 수 상위 20개국</h3>
                    @if(count($stats['users_by_country']) > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">순위</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">국가</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">코드</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">대륙</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">통화</th>
                                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-600 uppercase">사용자 수</th>
                                    <th class="px-3 py-2 text-center text-xs font-medium text-gray-600 uppercase">비율</th>
                                    <th class="px-3 py-2 text-center text-xs font-medium text-gray-600 uppercase">상태</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @php
                                    $totalUsers = $stats['users_by_country']->sum('user_count');
                                @endphp
                                @foreach($stats['users_by_country'] as $index => $country)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-500">
                                        {{ $index + 1 }}
                                    </td>
                                    <td class="px-3 py-2 whitespace-nowrap">
                                        <div class="flex items-center">
                                            @if($country->flag_emoji)
                                                <span class="text-xl mr-2">{{ $country->flag_emoji }}</span>
                                            @endif
                                            <a href="{{ route('admin.auth.countries.show', $country->id) }}"
                                               class="text-xs text-blue-600 hover:text-blue-900 font-medium">
                                                {{ $country->name }}
                                            </a>
                                        </div>
                                    </td>
                                    <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-900">
                                        {{ $country->code }}
                                    </td>
                                    <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-500">
                                        {{ $country->region ?: '-' }}
                                    </td>
                                    <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-500">
                                        {{ $country->currency_code ?: '-' }}
                                    </td>
                                    <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-900 text-right font-medium">
                                        {{ number_format($country->user_count) }}
                                    </td>
                                    <td class="px-3 py-2 whitespace-nowrap text-center">
                                        <div class="flex items-center">
                                            <div class="flex-1 mr-2">
                                                <div class="w-full bg-gray-200 rounded-full h-2">
                                                    <div class="bg-blue-500 h-2 rounded-full" 
                                                         style="width: {{ $totalUsers > 0 ? ($country->user_count / $totalUsers * 100) : 0 }}%">
                                                    </div>
                                                </div>
                                            </div>
                                            <span class="text-xs text-gray-500">
                                                {{ $totalUsers > 0 ? round(($country->user_count / $totalUsers * 100), 1) : 0 }}%
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-3 py-2 whitespace-nowrap text-center">
                                        @if($country->is_active)
                                            <span class="px-1.5 inline-flex text-xs leading-4 font-medium rounded-full bg-green-100 text-green-800">
                                                활성
                                            </span>
                                        @else
                                            <span class="px-1.5 inline-flex text-xs leading-4 font-medium rounded-full bg-gray-100 text-gray-800">
                                                비활성
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="text-xs text-gray-500">데이터가 없습니다</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection