<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            메시지함
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                
                <!-- 사이드바 -->
                <div class="md:col-span-1">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 mb-4">
                        <a href="{{ route('home.message.compose') }}" class="block w-full px-4 py-2 bg-blue-500 text-white text-center rounded hover:bg-blue-600">
                            새 메시지 작성
                        </a>
                    </div>
                    
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                        <nav class="space-y-1">
                            <a href="{{ route('home.message', ['type' => 'inbox']) }}" 
                               class="block px-3 py-2 rounded {{ $type === 'inbox' ? 'bg-gray-100' : '' }} hover:bg-gray-50">
                                받은 메시지
                                @if($unreadCount > 0)
                                    <span class="ml-2 px-2 py-1 text-xs bg-red-500 text-white rounded-full">{{ $unreadCount }}</span>
                                @endif
                            </a>
                            <a href="{{ route('home.message', ['type' => 'sent']) }}" 
                               class="block px-3 py-2 rounded {{ $type === 'sent' ? 'bg-gray-100' : '' }} hover:bg-gray-50">
                                보낸 메시지
                            </a>
                            <a href="{{ route('home.message', ['type' => 'starred']) }}" 
                               class="block px-3 py-2 rounded {{ $type === 'starred' ? 'bg-gray-100' : '' }} hover:bg-gray-50">
                                중요 메시지
                            </a>
                            <a href="{{ route('home.message', ['type' => 'archived']) }}" 
                               class="block px-3 py-2 rounded {{ $type === 'archived' ? 'bg-gray-100' : '' }} hover:bg-gray-50">
                                보관함
                            </a>
                            <hr class="my-2">
                            <a href="{{ route('home.message.blocked') }}" class="block px-3 py-2 rounded hover:bg-gray-50">
                                차단 목록
                            </a>
                            <a href="{{ route('home.message.settings') }}" class="block px-3 py-2 rounded hover:bg-gray-50">
                                알림 설정
                            </a>
                        </nav>
                    </div>
                </div>
                
                <!-- 메시지 목록 -->
                <div class="md:col-span-3">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            
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
                            
                            <!-- 검색 바 -->
                            <form method="GET" action="{{ route('home.message') }}" class="mb-4">
                                <input type="hidden" name="type" value="{{ $type }}">
                                <div class="flex space-x-2">
                                    <input type="text" name="search" value="{{ request('search') }}" 
                                           placeholder="메시지 검색..." 
                                           class="flex-1 rounded-md border-gray-300 shadow-sm">
                                    <select name="priority" class="rounded-md border-gray-300 shadow-sm">
                                        <option value="">모든 우선순위</option>
                                        <option value="urgent" {{ request('priority') === 'urgent' ? 'selected' : '' }}>긴급</option>
                                        <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>높음</option>
                                        <option value="normal" {{ request('priority') === 'normal' ? 'selected' : '' }}>보통</option>
                                        <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>낮음</option>
                                    </select>
                                    <button type="submit" class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">
                                        검색
                                    </button>
                                </div>
                            </form>
                            
                            <!-- 메시지 리스트 -->
                            <div class="space-y-2">
                                @forelse($messages as $message)
                                    <div class="border rounded-lg p-4 hover:bg-gray-50 {{ !$message->is_read && $type === 'inbox' ? 'bg-blue-50 border-blue-200' : '' }}">
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1">
                                                <div class="flex items-center space-x-2 mb-1">
                                                    @if($message->is_starred)
                                                        <span class="text-yellow-500">★</span>
                                                    @endif
                                                    @if($message->priority === 'urgent')
                                                        <span class="px-2 py-1 text-xs bg-red-100 text-red-800 rounded">긴급</span>
                                                    @elseif($message->priority === 'high')
                                                        <span class="px-2 py-1 text-xs bg-orange-100 text-orange-800 rounded">높음</span>
                                                    @endif
                                                    @if($message->type === 'system')
                                                        <span class="px-2 py-1 text-xs bg-gray-100 text-gray-800 rounded">시스템</span>
                                                    @elseif($message->type === 'announcement')
                                                        <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded">공지</span>
                                                    @endif
                                                    <span class="font-medium">
                                                        @if($type === 'sent')
                                                            받는 사람: {{ $message->recipient_name }}
                                                        @else
                                                            {{ $message->sender_name ?? '시스템' }}
                                                        @endif
                                                    </span>
                                                    <span class="text-sm text-gray-500">
                                                        {{ \Carbon\Carbon::parse($message->created_at)->diffForHumans() }}
                                                    </span>
                                                </div>
                                                <a href="{{ route('home.message.show', $message->id) }}" class="block">
                                                    <div class="font-medium text-gray-900">{{ $message->subject }}</div>
                                                    <div class="text-sm text-gray-600 truncate">{{ Str::limit($message->content, 100) }}</div>
                                                </a>
                                            </div>
                                            <div class="flex space-x-1 ml-4">
                                                <button onclick="toggleStar({{ $message->id }})" class="p-1 hover:bg-gray-100 rounded">
                                                    <svg class="w-5 h-5 {{ $message->is_starred ? 'text-yellow-500' : 'text-gray-400' }}" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                    </svg>
                                                </button>
                                                @if($type !== 'sent')
                                                    <button onclick="archiveMessage({{ $message->id }})" class="p-1 hover:bg-gray-100 rounded">
                                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                                                        </svg>
                                                    </button>
                                                @endif
                                                <button onclick="deleteMessage({{ $message->id }})" class="p-1 hover:bg-gray-100 rounded">
                                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center py-8 text-gray-500">
                                        메시지가 없습니다.
                                    </div>
                                @endforelse
                            </div>
                            
                            <!-- 페이지네이션 -->
                            <div class="mt-4">
                                {{ $messages->links() }}
                            </div>
                            
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
    
    <script>
        function toggleStar(messageId) {
            fetch('/home/message/' + messageId + '/star', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                }
            });
        }
        
        function archiveMessage(messageId) {
            if (!confirm('이 메시지를 보관하시겠습니까?')) {
                return;
            }
            
            fetch('/home/message/' + messageId + '/archive', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                }
            });
        }
        
        function deleteMessage(messageId) {
            if (!confirm('이 메시지를 삭제하시겠습니까?')) {
                return;
            }
            
            fetch('/home/message/' + messageId, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                }
            });
        }
    </script>
</x-app-layout>