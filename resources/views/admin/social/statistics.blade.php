<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            소셜 로그인 통계
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-2xl font-bold text-gray-900">{{ $totalStats['total_social_users'] }}</div>
                        <div class="text-sm text-gray-500">전체 소셜 사용자</div>
                    </div>
                </div>
                
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-2xl font-bold text-gray-900">{{ $totalStats['total_connections'] }}</div>
                        <div class="text-sm text-gray-500">전체 연결 수</div>
                    </div>
                </div>
                
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-2xl font-bold text-gray-900">{{ $totalStats['recent_registrations'] }}</div>
                        <div class="text-sm text-gray-500">최근 가입 (30일)</div>
                    </div>
                </div>
                
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-2xl font-bold text-gray-900">{{ $totalStats['recent_logins'] }}</div>
                        <div class="text-sm text-gray-500">최근 로그인 (7일)</div>
                    </div>
                </div>
            </div>
            
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">공급자별 통계</h3>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">공급자</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">상태</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">사용자 수</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">연결 수</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">점유율</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @php
                                    $totalUsers = $providerStats->sum('user_count');
                                @endphp
                                @foreach($providerStats as $stat)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $stat->display_name ?? ucfirst($stat->name) }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($stat->enabled)
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
                                            {{ $stat->user_count }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $stat->connection_count }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="text-sm text-gray-900">
                                                    {{ $totalUsers > 0 ? round(($stat->user_count / $totalUsers) * 100, 1) : 0 }}%
                                                </div>
                                                <div class="ml-2 w-24 bg-gray-200 rounded-full h-2">
                                                    <div class="bg-blue-600 h-2 rounded-full" 
                                                         style="width: {{ $totalUsers > 0 ? ($stat->user_count / $totalUsers) * 100 : 0 }}%"></div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">월별 트렌드</h3>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">월</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">가입</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">로그인</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">연결</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($monthlyTrends as $month => $trend)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $month }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $trend['registrations'] }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $trend['logins'] }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $trend['connections'] }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            @if($recentErrors->count() > 0)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4 text-red-600">최근 오류</h3>
                        
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">시간</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">공급자</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">작업</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">오류 메시지</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">IP</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($recentErrors as $error)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ \Carbon\Carbon::parse($error->created_at)->format('m-d H:i') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $error->provider }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $error->action }}
                                            </td>
                                            <td class="px-6 py-4 text-sm text-red-600">
                                                @php
                                                    $metadata = json_decode($error->metadata, true);
                                                @endphp
                                                {{ $metadata['error'] ?? 'Unknown error' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $error->ip_address }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
            
        </div>
    </div>
</x-admin-layout>