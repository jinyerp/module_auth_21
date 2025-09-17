<x-admin-layout>
    <x-admin-header>
        <h1 class="text-2xl font-bold">SMS 템플릿 관리</h1>
        <x-slot name="actions">
            <a href="{{ route('admin.auth.sms.templates.create') }}" 
               class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                새 템플릿 생성
            </a>
        </x-slot>
    </x-admin-header>

    <!-- 필터 -->
    <div class="bg-white rounded-lg shadow mb-6">
        <form method="GET" action="{{ route('admin.auth.sms.templates') }}" class="p-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="md:col-span-2">
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="템플릿 검색..." 
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

    <!-- 템플릿 목록 -->
    <div class="bg-white rounded-lg shadow">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">템플릿명</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">제목</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">카테고리</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">발신번호</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">사용 횟수</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">상태</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">작업</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($templates as $template)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-medium">{{ $template->name }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm">{{ $template->title ?: '-' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs bg-gray-100 rounded">
                                    {{ $template->category ?: '미분류' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                {{ $template->sender ?: '기본' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="text-sm">{{ number_format($template->usage_count) }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($template->is_active)
                                    <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded">활성</span>
                                @else
                                    <span class="px-2 py-1 text-xs bg-gray-100 text-gray-800 rounded">비활성</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <div class="flex space-x-2">
                                    <button onclick="previewTemplate('{{ $template->content }}')"
                                            class="text-blue-600 hover:text-blue-800">미리보기</button>
                                    <a href="{{ route('admin.auth.sms.templates.edit', $template->id) }}"
                                       class="text-indigo-600 hover:text-indigo-800">수정</a>
                                    <button onclick="deleteTemplate({{ $template->id }})"
                                            class="text-red-600 hover:text-red-800">삭제</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                등록된 템플릿이 없습니다.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- 페이지네이션 -->
        <div class="px-6 py-4 border-t">
            {{ $templates->links() }}
        </div>
    </div>

    @push('scripts')
    <script>
        function previewTemplate(content) {
            alert(content);
        }

        function deleteTemplate(id) {
            if (confirm('이 템플릿을 삭제하시겠습니까?')) {
                fetch(`/admin/auth/sms/templates/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    }
                });
            }
        }
    </script>
    @endpush
</x-admin-layout>