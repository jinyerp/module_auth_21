@extends('layouts.admin.main')

@section('title', '사용자 샤딩 통계')
@section('description', '사용자 샤딩 설정 통계를 확인합니다.')

@section('content')
<div class="px-4 sm:px-6 lg:px-8">
    <!-- 브레드크럼 네비게이션 -->
    <div>
        <nav class="sm:hidden" aria-label="Back">
                            <a href="{{ route('admin.auth.sharding.index') }}" class="flex items-center text-sm font-medium text-gray-500 hover:text-gray-700">
                <svg class="mr-1 -ml-1 size-5 shrink-0 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
                    <path fill-rule="evenodd" d="M11.78 5.22a.75.75 0 0 1 0 1.06L8.06 10l3.72 3.72a.75.75 0 1 1-1.06 1.06l-4.25-4.25a.75.75 0 0 1 0-1.06l4.25-4.25a.75.75 0 0 1 1.06 0Z" clip-rule="evenodd" />
                </svg>
                뒤로
            </a>
        </nav>
        <nav class="hidden sm:flex" aria-label="Breadcrumb">
            <ol role="list" class="flex items-center space-x-4">
                <li>
                    <div class="flex">
                        <a href="{{ route('admin.dashboard') }}" class="text-sm font-medium text-gray-500 hover:text-gray-700">대시보드</a>
                    </div>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="size-5 flex-shrink-0 text-gray-300" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path d="M5.555 17.776l8-16 .894.448-8 16-.894-.448z" />
                        </svg>
                        <a href="{{ route('admin.auth.sharding.index') }}" class="ml-4 text-sm font-medium text-gray-500 hover:text-gray-700">사용자 샤딩 관리</a>
                    </div>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="size-5 flex-shrink-0 text-gray-300" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path d="M5.555 17.776l8-16 .894.448-8 16-.894-.448z" />
                        </svg>
                        <span class="ml-4 text-sm font-medium text-gray-500">통계</span>
                    </div>
                </li>
            </ol>
        </nav>
    </div>

    <x-resource-heading
        title="사용자 샤딩 통계"
        subtitle="사용자 샤딩 설정 통계를 확인합니다.">
        <x-slot name="actions">
            <a href="{{ route('admin.auth.sharding.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                목록으로
            </a>
        </x-slot>
    </x-resource-heading>

    <!-- 통계 카드 -->
    <div class="mt-8">
        <dl class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
            <!-- 총 샤딩 설정 -->
            <div class="relative overflow-hidden rounded-lg bg-white px-4 pb-12 pt-5 shadow sm:px-6 sm:pt-6">
                <dt>
                    <div class="absolute rounded-md bg-indigo-500 p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"></path>
                        </svg>
                    </div>
                    <p class="ml-16 truncate text-sm font-medium text-gray-500">총 사용자 샤딩 설정</p>
                </dt>
                <dd class="ml-16 flex items-baseline pb-6 sm:pb-7">
                    <p class="text-2xl font-semibold text-gray-900">{{ $stats['total_configs'] }}</p>
                </dd>
            </div>

            <!-- 활성 샤딩 설정 -->
            <div class="relative overflow-hidden rounded-lg bg-white px-4 pb-12 pt-5 shadow sm:px-6 sm:pt-6">
                <dt>
                    <div class="absolute rounded-md bg-green-500 p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <p class="ml-16 truncate text-sm font-medium text-gray-500">활성 사용자 샤딩 설정</p>
                </dt>
                <dd class="ml-16 flex items-baseline pb-6 sm:pb-7">
                    <p class="text-2xl font-semibold text-gray-900">{{ $stats['active_configs'] }}</p>
                </dd>
            </div>

            <!-- 총 샤드 수 -->
            <div class="relative overflow-hidden rounded-lg bg-white px-4 pb-12 pt-5 shadow sm:px-6 sm:pt-6">
                <dt>
                    <div class="absolute rounded-md bg-blue-500 p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                    </div>
                    <p class="ml-16 truncate text-sm font-medium text-gray-500">총 샤드 수</p>
                </dt>
                <dd class="ml-16 flex items-baseline pb-6 sm:pb-7">
                    <p class="text-2xl font-semibold text-gray-900">{{ $stats['total_shards'] }}</p>
                </dd>
            </div>

            <!-- 평균 샤드 수 -->
            <div class="relative overflow-hidden rounded-lg bg-white px-4 pb-12 pt-5 shadow sm:px-6 sm:pt-6">
                <dt>
                    <div class="absolute rounded-md bg-purple-500 p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <p class="ml-16 truncate text-sm font-medium text-gray-500">평균 샤드 수</p>
                </dt>
                <dd class="ml-16 flex items-baseline pb-6 sm:pb-7">
                    <p class="text-2xl font-semibold text-gray-900">
                        {{ $stats['active_configs'] > 0 ? round($stats['total_shards'] / $stats['active_configs'], 1) : 0 }}
                    </p>
                </dd>
            </div>
        </dl>
    </div>

    <!-- 샤딩 설정 목록 -->
    <div class="mt-8">
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">활성 사용자 샤딩 설정</h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">현재 활성화된 사용자 샤딩 설정들의 목록입니다.</p>
            </div>
            <div class="border-t border-gray-200">
                @if($stats['tables']->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">테이블 이름</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">샤드 개수</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">샤딩 전략</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">상태</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($stats['tables'] as $table)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $table->table_name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $table->shard_count }}개</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        @if($table->shard_strategy === 'hash')
                                            해시 기반
                                        @else
                                            범위 기반
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            활성
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">활성 사용자 샤딩 설정이 없습니다</h3>
                        <p class="mt-1 text-sm text-gray-500">새로운 사용자 샤딩 설정을 생성해보세요.</p>
                        <div class="mt-6">
                            <a href="{{ route('admin.auth.sharding.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                새 사용자 샤딩 설정
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
