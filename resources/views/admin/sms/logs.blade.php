<x-admin-layout>
    <x-admin-header>
        <h1 class="text-2xl font-bold">SMS 발송 로그</h1>
        <x-slot name="actions">
            <a href="{{ route('admin.auth.sms.send') }}" 
               class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                SMS 발송
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
            <div class="text-sm text-gray-500">전송 완료</div>
            <div class="text-2xl font-bold text-blue-600">{{ number_format($stats['delivered']) }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-sm text-gray-500">발송 실패</div>
            <div class="text-2xl font-bold text-red-600">{{ number_format($stats['failed']) }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-sm text-gray-500">총 비용</div>
            <div class="text-2xl font-bold">₩{{ number_format($stats['total_cost']) }}</div>
        </div>
    </div>

    <!-- 필터 -->
    <div class="bg-white rounded-lg shadow mb-6">
        <form method="GET" action="{{ route('admin.auth.sms.logs') }}" class="p-4">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="전화번호/내용 검색..." 
                           class="w-full px-3 py-2 border rounded-md">
                </div>
                <div>
                    <select name="status" class="w-full px-3 py-2 border rounded-md">
                        <option value="">모든 상태</option>
                        <option value="pending" @selected(request('status') == 'pending')>대기중</option>
                        <option value="sent" @selected(request('status') == 'sent')>발송완료</option>
                        <option value="delivered" @selected(request('status') == 'delivered')>전송완료</option>
                        <option value="failed" @selected(request('status') == 'failed')>실패</option>
                    </select>
                </div>
                <div>
                    <select name="provider" class="w-full px-3 py-2 border rounded-md">
                        <option value="">모든 프로바이더</option>
                        <option value="twilio" @selected(request('provider') == 'twilio')>Twilio</option>
                        <option value="aligo" @selected(request('provider') == 'aligo')>알리고</option>
                        <option value="toast" @selected(request('provider') == 'toast')>Toast SMS</option>
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
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">발신번호</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">내용</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">프로바이더</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">상태</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">비용</th>
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
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                {{ $log->from }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm max-w-xs truncate" title="{{ $log->content }}">
                                    {{ Str::limit($log->content, 50) }}
                                </div>
                                @if($log->template_name)
                                    <span class="text-xs text-gray-500">템플릿: {{ $log->template_name }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs bg-gray-100 rounded">
                                    {{ strtoupper($log->provider) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($log->status == 'sent')
                                    <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded">발송완료</span>
                                @elseif($log->status == 'delivered')
                                    <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded">전송완료</span>
                                @elseif($log->status == 'failed')
                                    <span class="px-2 py-1 text-xs bg-red-100 text-red-800 rounded">실패</span>
                                @else
                                    <span class="px-2 py-1 text-xs bg-yellow-100 text-yellow-800 rounded">대기중</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @if($log->cost)
                                    ₩{{ number_format($log->cost) }}
                                @else
                                    -
                                @endif
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
</x-admin-layout>