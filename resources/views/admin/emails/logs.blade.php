<x-admin-layout>
    <x-admin-header>
        <h1 class="text-2xl font-bold">이메일 발송 로그</h1>
        <x-slot name="actions">
            <a href="{{ route('admin.auth.emails.send') }}" 
               class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                이메일 발송
            </a>
        </x-slot>
    </x-admin-header>

    <!-- 통계 -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-sm text-gray-500">전체 발송</div>
            <div class="text-2xl font-bold">{{ number_format($stats['total']) }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-sm text-gray-500">발송 완료</div>
            <div class="text-2xl font-bold text-green-600">{{ number_format($stats['sent']) }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-sm text-gray-500">발송 실패</div>
            <div class="text-2xl font-bold text-red-600">{{ number_format($stats['failed']) }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-sm text-gray-500">열람</div>
            <div class="text-2xl font-bold text-blue-600">{{ number_format($stats['opened']) }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-sm text-gray-500">클릭</div>
            <div class="text-2xl font-bold text-purple-600">{{ number_format($stats['clicked']) }}</div>
        </div>
    </div>

    <!-- 필터 -->
    <div class="bg-white rounded-lg shadow mb-6">
        <form method="GET" action="{{ route('admin.auth.emails.logs') }}" class="p-4">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="검색..." 
                           class="w-full px-3 py-2 border rounded-md">
                </div>
                <div>
                    <select name="status" class="w-full px-3 py-2 border rounded-md">
                        <option value="">모든 상태</option>
                        <option value="pending" @selected(request('status') == 'pending')>대기중</option>
                        <option value="sent" @selected(request('status') == 'sent')>발송완료</option>
                        <option value="failed" @selected(request('status') == 'failed')>실패</option>
                    </select>
                </div>
                <div>
                    <select name="template_name" class="w-full px-3 py-2 border rounded-md">
                        <option value="">모든 템플릿</option>
                        @foreach($templates as $template)
                            <option value="{{ $template }}" @selected(request('template_name') == $template)>
                                {{ $template }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <input type="date" name="date_from" value="{{ request('date_from') }}"
                           class="w-full px-3 py-2 border rounded-md">
                </div>
                <div>
                    <button type="submit" class="w-full px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700">
                        검색
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- 로그 목록 -->
    <div class="bg-white rounded-lg shadow">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">발송일시</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">수신자</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">제목</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">템플릿</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">상태</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">열람/클릭</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">작업</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($logs as $log)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                {{ $log->created_at->format('Y-m-d H:i') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm">{{ $log->to }}</div>
                                @if($log->user_name)
                                    <div class="text-xs text-gray-500">{{ $log->user_name }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm">{{ Str::limit($log->subject, 40) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($log->template_name)
                                    <span class="px-2 py-1 text-xs bg-gray-100 rounded">
                                        {{ $log->template_name }}
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($log->status == 'sent')
                                    <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded">발송완료</span>
                                @elseif($log->status == 'failed')
                                    <span class="px-2 py-1 text-xs bg-red-100 text-red-800 rounded">실패</span>
                                @else
                                    <span class="px-2 py-1 text-xs bg-yellow-100 text-yellow-800 rounded">대기중</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <div class="flex space-x-2">
                                    @if($log->opened_at)
                                        <span class="text-blue-600" title="{{ $log->opened_at }}">열람</span>
                                    @endif
                                    @if($log->clicked_at)
                                        <span class="text-purple-600" title="{{ $log->clicked_at }}">클릭</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <div class="flex space-x-2">
                                    <a href="{{ route('admin.auth.emails.logs.show', $log->id) }}"
                                       class="text-blue-600 hover:text-blue-800">상세</a>
                                    @if($log->status == 'failed')
                                        <button onclick="resendEmail({{ $log->id }})"
                                                class="text-green-600 hover:text-green-800">재발송</button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                발송 로그가 없습니다.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- 페이지네이션 -->
        <div class="px-6 py-4 border-t">
            {{ $logs->links() }}
        </div>
    </div>

    @push('scripts')
    <script>
        function resendEmail(id) {
            if (confirm('이메일을 재발송하시겠습니까?')) {
                fetch(`/admin/auth/emails/logs/${id}/resend`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        location.reload();
                    } else {
                        alert(data.message || '재발송에 실패했습니다.');
                    }
                });
            }
        }
    </script>
    @endpush
</x-admin-layout>