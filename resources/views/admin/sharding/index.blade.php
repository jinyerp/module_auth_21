@extends('jiny-auth::layouts.resource.list')

@section('title', '사용자 샤딩 관리')
@section('description', '사용자 데이터베이스 샤딩 설정을 관리합니다.')

@section('heading')
<div class="w-full">
    <div class="sm:flex sm:items-end justify-between">
        <div class="sm:flex-auto">
            <h1 class="text-2xl font-semibold text-gray-900">사용자 샤딩 관리</h1>
            <p class="mt-2 text-base text-gray-700">
                사용자 데이터베이스 샤딩 설정을 관리합니다.</p>
        </div>
        <div class="mt-4 sm:mt-0 flex gap-2">
            <x-ui::button-secondary href="{{ route('admin.auth.users.index') }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                </svg>
                사용자 관리
            </x-ui::button-secondary>
            <x-ui::button-primary href="{{ route($route . 'create') }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                새 사용자 샤딩 설정
            </x-ui::button-primary>
        </div>
    </div>
</div>

{{-- 통계 정보 --}}
@if(isset($stats))
<div class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-5">
    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"></path>
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">전체 설정</dt>
                        <dd class="text-lg font-medium text-gray-900">{{ $stats['total_configs'] }}</dd>
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
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">활성 설정</dt>
                        <dd class="text-lg font-medium text-gray-900">{{ $stats['active_configs'] }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">비활성 설정</dt>
                        <dd class="text-lg font-medium text-gray-900">{{ $stats['inactive_configs'] }}</dd>
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
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">해시 전략</dt>
                        <dd class="text-lg font-medium text-gray-900">{{ $stats['hash_strategy'] }}</dd>
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
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">범위 전략</dt>
                        <dd class="text-lg font-medium text-gray-900">{{ $stats['range_strategy'] }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
@endsection


@section('table')
<div class="px-4 sm:px-6 lg:px-8">
    {{-- 필터 및 검색 --}}
    <div class="mt-8 mb-6">
        <form method="GET" action="{{ route($route.'index') }}" class="bg-white shadow rounded-lg p-6">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700">검색</label>
                    <input type="text" name="search" id="search" value="{{ $filters['search'] ?? '' }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                           placeholder="테이블명, 설명, 샤딩 키...">
                </div>

                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700">상태</label>
                    <select name="status" id="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="">전체</option>
                        <option value="active" {{ ($filters['status'] ?? '') === 'active' ? 'selected' : '' }}>활성</option>
                        <option value="inactive" {{ ($filters['status'] ?? '') === 'inactive' ? 'selected' : '' }}>비활성</option>
                    </select>
                </div>

                <div>
                    <label for="strategy" class="block text-sm font-medium text-gray-700">전략</label>
                    <select name="strategy" id="strategy" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="">전체</option>
                        <option value="hash" {{ ($filters['strategy'] ?? '') === 'hash' ? 'selected' : '' }}>해시</option>
                        <option value="range" {{ ($filters['strategy'] ?? '') === 'range' ? 'selected' : '' }}>범위</option>
                    </select>
                </div>

                <div>
                    <label for="sort_by" class="block text-sm font-medium text-gray-700">정렬</label>
                    <select name="sort_by" id="sort_by" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="created_at" {{ ($filters['sort_by'] ?? 'created_at') === 'created_at' ? 'selected' : '' }}>생성일</option>
                        <option value="table_name" {{ ($filters['sort_by'] ?? '') === 'table_name' ? 'selected' : '' }}>테이블명</option>
                        <option value="shard_count" {{ ($filters['sort_by'] ?? '') === 'shard_count' ? 'selected' : '' }}>샤드 개수</option>
                        <option value="shard_strategy" {{ ($filters['sort_by'] ?? '') === 'shard_strategy' ? 'selected' : '' }}>전략</option>
                        <option value="is_active" {{ ($filters['sort_by'] ?? '') === 'is_active' ? 'selected' : '' }}>상태</option>
                    </select>
                </div>
            </div>

            <div class="mt-4 flex justify-between items-center">
                <div class="flex items-center space-x-4">
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        검색
                    </button>
                    <a href="{{ route($route.'index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        초기화
                    </a>
                </div>

                <div class="flex items-center space-x-2">
                    <label for="per_page" class="text-sm font-medium text-gray-700">페이지당:</label>
                    <select name="per_page" id="per_page" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="15" {{ ($filters['per_page'] ?? '15') === '15' ? 'selected' : '' }}>15</option>
                        <option value="30" {{ ($filters['per_page'] ?? '') === '30' ? 'selected' : '' }}>30</option>
                        <option value="50" {{ ($filters['per_page'] ?? '') === '50' ? 'selected' : '' }}>50</option>
                    </select>
                </div>
            </div>
        </form>
    </div>

    <!-- 샤딩 설정 목록 -->
    <div class="mt-8">
        @if($rows->count() > 0)
            <div class="bg-white shadow overflow-hidden sm:rounded-md">
                <ul role="list" class="divide-y divide-gray-200">
                    @foreach($rows as $item)
                    <li>
                        <div class="px-4 py-4 sm:px-6">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <div class="h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center">
                                            <svg class="h-5 w-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="flex items-center">
                                            <p class="text-sm font-medium text-gray-900">{{ $item->table_name }}</p>
                                            @if($item->is_active)
                                                <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    활성
                                                </span>
                                            @else
                                                <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                    비활성
                                                </span>
                                            @endif
                                        </div>
                                        <div class="mt-1 flex items-center text-sm text-gray-500">
                                            <span class="mr-4">{{ $item->shard_count }}개 샤드</span>
                                            <span class="mr-4">키: {{ $item->shard_key }}</span>
                                            <span>전략: {{ $item->shard_strategy }}</span>
                                        </div>
                                        @if($item->description)
                                            <p class="mt-1 text-sm text-gray-600">{{ $item->description }}</p>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <a href="{{ route('admin.auth.sharding.show', $item) }}" class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                                        상세보기
                                    </a>
                                    @if($item->is_active)
                                        <form action="{{ route('admin.auth.sharding.disable', $item) }}" method="POST" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="text-yellow-600 hover:text-yellow-900 text-sm font-medium" onclick="return confirm('샤딩 설정을 비활성화하시겠습니까?')">
                                                비활성화
                                            </button>
                                        </form>
                                    @endif
                                    <form action="{{ route('admin.auth.sharding.destroy', $item) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900 text-sm font-medium" onclick="return confirm('샤딩 설정을 삭제하시겠습니까?')">
                                            삭제
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </li>
                    @endforeach
                </ul>
            </div>
        @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">사용자 샤딩 설정이 없습니다</h3>
                <p class="mt-1 text-sm text-gray-500">새로운 사용자 샤딩 설정을 생성해보세요.</p>
                <div class="mt-6">
                    <a href="{{ route($route.'create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        새 사용자 샤딩 설정
                    </a>
                </div>
            </div>
        @endif

        {{-- 페이지네이션 --}}
        @if($rows->hasPages())
            <div class="mt-6">
                {{ $rows->appends($filters)->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
