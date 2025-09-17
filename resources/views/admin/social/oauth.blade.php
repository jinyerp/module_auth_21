<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            OAuth 공급자 관리
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
            
            <div class="space-y-6">
                @foreach($providers as $provider)
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg" id="provider-{{ $provider->id }}">
                        <div class="p-6">
                            <form action="{{ route('admin.auth.oauth.update', $provider->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                
                                <div class="mb-4 flex items-center justify-between">
                                    <h3 class="text-lg font-semibold">
                                        {{ $provider->display_name ?? ucfirst($provider->name) }}
                                    </h3>
                                    <div>
                                        @if($provider->enabled)
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                활성
                                            </span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                비활성
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            상태
                                        </label>
                                        <select name="enabled" class="w-full rounded-md border-gray-300 shadow-sm">
                                            <option value="1" {{ $provider->enabled ? 'selected' : '' }}>활성</option>
                                            <option value="0" {{ !$provider->enabled ? 'selected' : '' }}>비활성</option>
                                        </select>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            우선순위
                                        </label>
                                        <input type="number" name="priority" value="{{ $provider->priority }}" 
                                               class="w-full rounded-md border-gray-300 shadow-sm" min="0">
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            Client ID
                                        </label>
                                        <input type="text" name="client_id" value="{{ $provider->client_id }}" 
                                               class="w-full rounded-md border-gray-300 shadow-sm"
                                               placeholder="OAuth Client ID">
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            Client Secret
                                        </label>
                                        <input type="password" name="client_secret" value="{{ $provider->client_secret }}" 
                                               class="w-full rounded-md border-gray-300 shadow-sm"
                                               placeholder="OAuth Client Secret">
                                    </div>
                                    
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            Redirect URI
                                        </label>
                                        <input type="url" name="redirect_uri" value="{{ $provider->redirect_uri }}" 
                                               class="w-full rounded-md border-gray-300 shadow-sm"
                                               placeholder="https://example.com/login/{{ $provider->name }}/callback">
                                        <p class="mt-1 text-xs text-gray-500">
                                            기본값: {{ url('/login/' . $provider->name . '/callback') }}
                                        </p>
                                    </div>
                                    
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            Scopes
                                        </label>
                                        <input type="text" name="scopes_text" 
                                               value="{{ is_array($scopes = json_decode($provider->scopes, true)) ? implode(', ', $scopes) : '' }}" 
                                               class="w-full rounded-md border-gray-300 shadow-sm"
                                               placeholder="email, profile">
                                        <p class="mt-1 text-xs text-gray-500">
                                            쉼표로 구분하여 입력하세요.
                                        </p>
                                    </div>
                                </div>
                                
                                <div class="mt-4 flex justify-end space-x-2">
                                    <a href="{{ route('admin.auth.oauth.users', $provider->name) }}" 
                                       class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">
                                        사용자 목록
                                    </a>
                                    <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                                        설정 저장
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
            
        </div>
    </div>
    
    <script>
        // Convert scopes text to array before submitting
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                const scopesText = form.querySelector('input[name="scopes_text"]');
                if (scopesText) {
                    const scopes = scopesText.value.split(',').map(s => s.trim()).filter(s => s);
                    const scopesInput = document.createElement('input');
                    scopesInput.type = 'hidden';
                    scopesInput.name = 'scopes[]';
                    scopes.forEach(scope => {
                        const input = scopesInput.cloneNode();
                        input.value = scope;
                        form.appendChild(input);
                    });
                }
            });
        });
    </script>
</x-admin-layout>