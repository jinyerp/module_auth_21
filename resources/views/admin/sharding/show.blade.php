@extends('jiny-auth::layouts.admin.main')

@section('title', '사용자 샤딩 설정 상세')
@section('description', '사용자 샤딩 설정의 상세 정보를 확인합니다.')

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
                        <span class="ml-4 text-sm font-medium text-gray-500">{{ $config->table_name }}</span>
                    </div>
                </li>
            </ol>
        </nav>
    </div>

    <x-resource-heading
        title="사용자 샤딩 설정 상세"
        subtitle="사용자 샤딩 설정의 상세 정보를 확인합니다.">
        <x-slot name="actions">
            <div class="flex items-center space-x-3">
                <a href="{{ route('admin.auth.users.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                    </svg>
                    사용자 관리
                </a>
                <a href="{{ route('admin.auth.sharding.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    목록으로
                </a>
            </div>
        </x-slot>
    </x-resource-heading>

    <!-- 샤딩 설정 정보 -->
    <div class="mt-8">
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">사용자 샤딩 설정 정보</h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">사용자 샤딩 설정의 상세 정보입니다.</p>
            </div>
            <div class="border-t border-gray-200">
                <dl>
                    <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">테이블 이름</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">{{ $config->table_name }}</dd>
                    </div>
                    <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">샤드 개수</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">{{ $config->shard_count }}개</dd>
                    </div>
                    <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">샤딩 키</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">{{ $config->shard_key }}</dd>
                    </div>
                    <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">샤딩 전략</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                            @if($config->shard_strategy === 'hash')
                                해시 기반 (Hash-based)
                            @else
                                범위 기반 (Range-based)
                            @endif
                        </dd>
                    </div>
                    <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">상태</dt>
                        <dd class="mt-1 text-sm sm:col-span-2 sm:mt-0">
                            @if($config->is_active)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    활성
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    비활성
                                </span>
                            @endif
                        </dd>
                    </div>
                    @if($config->description)
                    <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">설명</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">{{ $config->description }}</dd>
                    </div>
                    @endif
                    <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">생성일</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">{{ $config->created_at->format('Y-m-d H:i:s') }}</dd>
                    </div>

                    <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">수정일</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">{{ $config->updated_at->format('Y-m-d H:i:s') }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>

    <!-- 샤드 테이블 목록 -->
    <div class="mt-8">
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">사용자 샤드 테이블 목록</h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">생성된 사용자 샤드 테이블들의 목록입니다. 각 테이블에는 중복 방지를 위한 <code class="bg-gray-100 px-1 rounded">shard_uuid</code> 컬럼이 추가되어 있습니다.</p>
            </div>
            <div class="border-t border-gray-200">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">샤드 ID</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">테이블 이름</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">상태</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($shardTables as $shardTable)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $shardTable['index'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-mono">{{ $shardTable['name'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @if($shardTable['exists'])
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            존재함
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            없음
                                        </span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- 액션 버튼 -->
    <div class="mt-8 flex items-center justify-between">
        <div class="flex items-center space-x-3">
            @if($config->is_active)
                                                <form action="{{ route('admin.auth.sharding.disable', $config) }}" method="POST" class="inline">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-yellow-300 text-sm font-medium rounded-md text-yellow-700 bg-white hover:bg-yellow-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500" onclick="return confirm('사용자 샤딩 설정을 비활성화하시겠습니까?')">
                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                        비활성화
                    </button>
                </form>
            @endif
        </div>
        <div class="flex items-center space-x-3">
                                            <form action="{{ route('admin.auth.sharding.destroy', $config) }}" method="POST" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-red-300 text-sm font-medium rounded-md text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500" onclick="return confirm('사용자 샤딩 설정을 삭제하시겠습니까? 이 작업은 되돌릴 수 없습니다.')">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                    삭제
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
