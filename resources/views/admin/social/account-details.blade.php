<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            소셜 계정 상세 정보
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold">계정 정보</h3>
                        <a href="{{ route('admin.auth.oauth.users', $account->provider) }}" class="text-blue-600 hover:text-blue-900">
                            ← 사용자 목록으로 돌아가기
                        </a>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h4 class="font-medium text-gray-700 mb-2">사용자 정보</h4>
                            <dl class="space-y-1">
                                <div class="flex">
                                    <dt class="text-gray-600 w-24">이름:</dt>
                                    <dd class="font-medium">{{ $account->user_name }}</dd>
                                </div>
                                <div class="flex">
                                    <dt class="text-gray-600 w-24">이메일:</dt>
                                    <dd>{{ $account->user_email }}</dd>
                                </div>
                                <div class="flex">
                                    <dt class="text-gray-600 w-24">상태:</dt>
                                    <dd>
                                        @if($account->is_active)
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                활성
                                            </span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                비활성
                                            </span>
                                        @endif
                                    </dd>
                                </div>
                            </dl>
                        </div>
                        
                        <div>
                            <h4 class="font-medium text-gray-700 mb-2">소셜 계정 정보</h4>
                            <dl class="space-y-1">
                                <div class="flex">
                                    <dt class="text-gray-600 w-24">공급자:</dt>
                                    <dd class="font-medium">{{ ucfirst($account->provider) }}</dd>
                                </div>
                                <div class="flex">
                                    <dt class="text-gray-600 w-24">소셜 ID:</dt>
                                    <dd class="text-sm">{{ $account->provider_user_id }}</dd>
                                </div>
                                <div class="flex">
                                    <dt class="text-gray-600 w-24">소셜 이름:</dt>
                                    <dd>{{ $account->name ?? '-' }}</dd>
                                </div>
                                <div class="flex">
                                    <dt class="text-gray-600 w-24">소셜 이메일:</dt>
                                    <dd>{{ $account->email ?? '-' }}</dd>
                                </div>
                                <div class="flex">
                                    <dt class="text-gray-600 w-24">연결일:</dt>
                                    <dd>{{ \Carbon\Carbon::parse($account->created_at)->format('Y-m-d H:i:s') }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                    
                    @if($account->avatar)
                        <div class="mt-4">
                            <h4 class="font-medium text-gray-700 mb-2">프로필 이미지</h4>
                            <img src="{{ $account->avatar }}" alt="Profile" class="w-20 h-20 rounded-full">
                        </div>
                    @endif
                    
                    <div class="mt-6">
                        <button onclick="disconnectAccount({{ $account->id }})" 
                                class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600">
                            연결 해제
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">로그인 이력 (최근 20건)</h3>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">작업</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">상태</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">IP 주소</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">브라우저</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">시간</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($loginHistory as $log)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            @switch($log->action)
                                                @case('login')
                                                    <span class="text-blue-600">로그인</span>
                                                    @break
                                                @case('logout')
                                                    <span class="text-gray-600">로그아웃</span>
                                                    @break
                                                @case('register')
                                                    <span class="text-green-600">회원가입</span>
                                                    @break
                                                @case('connect')
                                                    <span class="text-purple-600">연결</span>
                                                    @break
                                                @case('disconnect')
                                                    <span class="text-orange-600">연결 해제</span>
                                                    @break
                                                @default
                                                    {{ $log->action }}
                                            @endswitch
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            @if($log->status == 'success')
                                                <span class="text-green-600">성공</span>
                                            @else
                                                <span class="text-red-600">실패</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $log->ip_address }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ Str::limit($log->user_agent, 30) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ \Carbon\Carbon::parse($log->created_at)->format('Y-m-d H:i:s') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                            로그인 이력이 없습니다.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
    
    <script>
        function disconnectAccount(accountId) {
            if (!confirm('이 소셜 계정 연결을 해제하시겠습니까?')) {
                return;
            }
            
            fetch('/admin/auth/social/accounts/' + accountId, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                if (data.success) {
                    window.location.href = '{{ route('admin.auth.oauth.users', $account->provider) }}';
                }
            })
            .catch(error => {
                alert('오류가 발생했습니다.');
            });
        }
    </script>
</x-admin-layout>