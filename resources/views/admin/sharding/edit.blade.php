@extends('jiny-auth::layouts.admin.main')

@section('title', '샤딩 설정 편집')

@section('content')
<div class="w-full px-4 py-8">
    <!-- 브레드크럼 -->
    <nav class="flex" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center text-sm font-medium text-gray-500 hover:text-gray-700">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                    </svg>
                    대시보드
                </a>
            </li>
            <li>
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                    </svg>
                    <a href="{{ route('admin.auth.sharding.index') }}" class="ml-4 text-sm font-medium text-gray-500 hover:text-gray-700">사용자 샤딩 관리</a>
                </div>
            </li>
            <li aria-current="page">
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                    </svg>
                    <span class="ml-4 text-sm font-medium text-gray-500">샤딩 설정 편집</span>
                </div>
            </li>
        </ol>
    </nav>

    <!-- 헤더 -->
    <div class="mt-6 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">샤딩 설정 편집</h1>
            <p class="mt-2 text-sm text-gray-600">샤딩 설정을 수정합니다.</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('admin.auth.users.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                </svg>
                사용자 관리
            </a>
            <a href="{{ route('admin.auth.sharding.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                목록으로
            </a>
        </div>
    </div>

    <!-- 폼 -->
    <div class="mt-8">
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">샤딩 설정 정보</h3>
            </div>
            <div class="px-6 py-4">
                <form action="{{ route('admin.auth.sharding.update', $config) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 gap-6">
                        <!-- 테이블명 (읽기 전용) -->
                        <div>
                            <label for="table_name" class="block text-sm font-medium text-gray-700">테이블명</label>
                            <input type="text" id="table_name" name="table_name" value="{{ $config->table_name }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm bg-gray-100" readonly>
                            <p class="mt-1 text-sm text-gray-500">테이블명은 변경할 수 없습니다.</p>
                        </div>

                        <!-- 샤드 수 -->
                        <div>
                            <label for="shard_count" class="block text-sm font-medium text-gray-700">샤드 수</label>
                            <input type="number" id="shard_count" name="shard_count" value="{{ $config->shard_count }}" min="1" max="10000" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                            <p class="mt-1 text-sm text-gray-500">1부터 10,000까지 설정 가능합니다.</p>
                            @error('shard_count')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- 샤드 키 -->
                        <div>
                            <label for="shard_key" class="block text-sm font-medium text-gray-700">샤드 키</label>
                            <input type="text" id="shard_key" name="shard_key" value="{{ $config->shard_key }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                            <p class="mt-1 text-sm text-gray-500">샤드 분배에 사용할 컬럼명을 입력하세요.</p>
                            @error('shard_key')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- 샤드 전략 -->
                        <div>
                            <label for="shard_strategy" class="block text-sm font-medium text-gray-700">샤드 전략</label>
                            <select id="shard_strategy" name="shard_strategy" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                                <option value="hash" {{ $config->shard_strategy === 'hash' ? 'selected' : '' }}>해시 기반 (균등 분배)</option>
                                <option value="range" {{ $config->shard_strategy === 'range' ? 'selected' : '' }}>범위 기반 (순차 분배)</option>
                            </select>
                            <p class="mt-1 text-sm text-gray-500">데이터 분배 방식을 선택하세요.</p>
                            @error('shard_strategy')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- 설명 -->
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700">설명</label>
                            <textarea id="description" name="description" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">{{ $config->description }}</textarea>
                            <p class="mt-1 text-sm text-gray-500">샤딩 설정에 대한 설명을 입력하세요.</p>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- 현재 상태 -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">현재 상태</label>
                            <div class="mt-1">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $config->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                    {{ $config->is_active ? '활성화' : '비활성화' }}
                                </span>
                            </div>
                            <p class="mt-1 text-sm text-gray-500">현재 샤딩 설정의 활성화 상태입니다.</p>
                        </div>
                    </div>

                    <!-- 버튼 -->
                    <div class="mt-6 flex justify-end space-x-3">
                        <a href="{{ route('admin.auth.sharding.index') }}" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            취소
                        </a>
                        <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            설정 업데이트
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
