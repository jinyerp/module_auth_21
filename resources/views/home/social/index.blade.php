<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            소셜 계정 관리
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
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
                    
                    @if (session('info'))
                        <div class="mb-4 bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded">
                            {{ session('info') }}
                        </div>
                    @endif
                    
                    <h3 class="text-lg font-semibold mb-6">연결된 소셜 계정</h3>
                    
                    @if($socialAccounts->count() > 0)
                        <div class="space-y-4 mb-8">
                            @foreach($socialAccounts as $account)
                                <div class="flex items-center justify-between p-4 border rounded-lg">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center">
                                            @if($account->avatar)
                                                <img src="{{ $account->avatar }}" alt="" class="w-10 h-10 rounded-full">
                                            @else
                                                <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                </svg>
                                            @endif
                                        </div>
                                        <div>
                                            <div class="font-medium">{{ ucfirst($account->provider) }}</div>
                                            <div class="text-sm text-gray-500">{{ $account->name ?? $account->email }}</div>
                                            <div class="text-xs text-gray-400">연결일: {{ \Carbon\Carbon::parse($account->created_at)->format('Y-m-d H:i') }}</div>
                                        </div>
                                    </div>
                                    <button 
                                        onclick="disconnectSocial('{{ $account->provider }}')"
                                        class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600"
                                    >
                                        연결 해제
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-gray-500 mb-8">연결된 소셜 계정이 없습니다.</div>
                    @endif
                    
                    <h3 class="text-lg font-semibold mb-4">소셜 계정 연결</h3>
                    
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        @foreach($availableProviders as $provider)
                            @if(!in_array($provider->name, $connectedProviders))
                                <form action="{{ route('home.account.social.connect', $provider->name) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="w-full px-4 py-2 border rounded-lg hover:bg-gray-50 flex items-center justify-center space-x-2">
                                        <span>{{ $provider->display_name ?? ucfirst($provider->name) }}</span>
                                    </button>
                                </form>
                            @else
                                <div class="w-full px-4 py-2 bg-gray-100 border rounded-lg flex items-center justify-center space-x-2 text-gray-500">
                                    <span>{{ $provider->display_name ?? ucfirst($provider->name) }}</span>
                                    <span class="text-xs">(연결됨)</span>
                                </div>
                            @endif
                        @endforeach
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function disconnectSocial(provider) {
            if (!confirm(provider + ' 계정 연결을 해제하시겠습니까?')) {
                return;
            }
            
            fetch('/home/account/social/' + provider + '/disconnect', {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    window.location.reload();
                } else {
                    alert(data.message || '오류가 발생했습니다.');
                }
            })
            .catch(error => {
                alert('오류가 발생했습니다.');
            });
        }
    </script>
</x-app-layout>