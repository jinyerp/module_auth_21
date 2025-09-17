<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            소셜 로그인 관리
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if (session('success'))
                <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif
            
            @if (session('error'))
                <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
                    {{ session('error') }}
                </div>
            @endif
            
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">소셜 로그인 공급자</h3>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">공급자</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">상태</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">전체 사용자</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">활성 사용자</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">최근 로그인 (7일)</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">우선순위</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">관리</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($providers as $provider)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $provider->display_name ?? ucfirst($provider->name) }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($provider->enabled)
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                    활성
                                                </span>
                                            @else
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                    비활성
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $statistics[$provider->name]['total_users'] ?? 0 }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $statistics[$provider->name]['active_users'] ?? 0 }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $statistics[$provider->name]['recent_logins'] ?? 0 }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $provider->priority }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <a href="{{ route('admin.auth.oauth') }}#provider-{{ $provider->id }}" class="text-indigo-600 hover:text-indigo-900 mr-2">설정</a>
                                            <a href="{{ route('admin.auth.oauth.users', $provider->name) }}" class="text-blue-600 hover:text-blue-900">사용자</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">빠른 작업</h3>
                        <div class="space-y-2">
                            <a href="{{ route('admin.auth.oauth') }}" class="block px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 text-center">
                                OAuth 공급자 설정
                            </a>
                            <a href="{{ route('admin.auth.social.statistics') }}" class="block px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600 text-center">
                                통계 보기
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">요약 통계</h3>
                        <dl class="space-y-2">
                            <div class="flex justify-between">
                                <dt class="text-gray-600">전체 소셜 사용자:</dt>
                                <dd class="font-semibold">{{ array_sum(array_column($statistics, 'total_users')) }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-600">활성 소셜 사용자:</dt>
                                <dd class="font-semibold">{{ array_sum(array_column($statistics, 'active_users')) }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-600">최근 로그인 (7일):</dt>
                                <dd class="font-semibold">{{ array_sum(array_column($statistics, 'recent_logins')) }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-600">활성 공급자:</dt>
                                <dd class="font-semibold">{{ $providers->where('enabled', true)->count() }} / {{ $providers->count() }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</x-admin-layout>