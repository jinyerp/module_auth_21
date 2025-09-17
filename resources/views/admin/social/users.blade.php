<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ ucfirst($provider) }} 사용자 목록
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="mb-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold">
                            {{ $providerInfo->display_name ?? ucfirst($provider) }} 사용자
                        </h3>
                        <a href="{{ route('admin.auth.social') }}" class="text-blue-600 hover:text-blue-900">
                            ← 소셜 로그인 관리로 돌아가기
                        </a>
                    </div>
                    
                    <form method="GET" action="{{ route('admin.auth.oauth.users', $provider) }}" class="mb-4">
                        <div class="flex space-x-2">
                            <input type="text" name="search" value="{{ request('search') }}" 
                                   placeholder="이름, 이메일 검색..." 
                                   class="flex-1 rounded-md border-gray-300 shadow-sm">
                            <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                                검색
                            </button>
                            @if(request('search'))
                                <a href="{{ route('admin.auth.oauth.users', $provider) }}" 
                                   class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">
                                    초기화
                                </a>
                            @endif
                        </div>
                    </form>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">사용자</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">소셜 계정</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">상태</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">연결일</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">관리</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($users as $user)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                @if($user->avatar)
                                                    <img src="{{ $user->avatar }}" alt="" class="w-8 h-8 rounded-full mr-3">
                                                @else
                                                    <div class="w-8 h-8 rounded-full bg-gray-200 mr-3"></div>
                                                @endif
                                                <div>
                                                    <div class="text-sm font-medium text-gray-900">{{ $user->user_name }}</div>
                                                    <div class="text-sm text-gray-500">{{ $user->user_email }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ $user->name ?? '-' }}</div>
                                            <div class="text-sm text-gray-500">{{ $user->email ?? '-' }}</div>
                                            <div class="text-xs text-gray-400">ID: {{ $user->provider_user_id }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($user->is_active)
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
                                            {{ \Carbon\Carbon::parse($user->created_at)->format('Y-m-d H:i') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <a href="{{ route('admin.auth.social.accounts.details', $user->id) }}" 
                                               class="text-indigo-600 hover:text-indigo-900">
                                                상세
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                            {{ ucfirst($provider) }} 로그인 사용자가 없습니다.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-4">
                        {{ $users->links() }}
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</x-admin-layout>