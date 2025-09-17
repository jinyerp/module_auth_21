<x-admin-layout>
    <x-admin-header>
        <h1 class="text-2xl font-bold">이메일 템플릿 관리</h1>
        <x-slot name="actions">
            <a href="{{ route('admin.auth.emails.templates.create') }}" 
               class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                새 템플릿 생성
            </a>
        </x-slot>
    </x-admin-header>

    <!-- 통계 -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-sm text-gray-500">전체 템플릿</div>
            <div class="text-2xl font-bold">{{ $stats['total'] }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-sm text-gray-500">활성 템플릿</div>
            <div class="text-2xl font-bold text-green-600">{{ $stats['active'] }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-sm text-gray-500">총 사용 횟수</div>
            <div class="text-2xl font-bold text-blue-600">{{ number_format($stats['total_usage']) }}</div>
        </div>
    </div>

    <!-- 필터 -->
    <div class="bg-white rounded-lg shadow mb-6">
        <form method="GET" action="{{ route('admin.auth.emails.templates') }}" class="p-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="템플릿 검색..." 
                           class="w-full px-3 py-2 border rounded-md">
                </div>
                <div>
                    <select name="category" class="w-full px-3 py-2 border rounded-md">
                        <option value="">모든 카테고리</option>
                        @foreach($categories as $category)
                            <option value="{{ $category }}" @selected(request('category') == $category)>
                                {{ $category }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <select name="is_active" class="w-full px-3 py-2 border rounded-md">
                        <option value="">모든 상태</option>
                        <option value="true" @selected(request('is_active') == 'true')>활성</option>
                        <option value="false" @selected(request('is_active') == 'false')>비활성</option>
                    </select>
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
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">언어</th>
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
                                <div class="text-sm">{{ Str::limit($template->subject, 50) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs bg-gray-100 rounded">
                                    {{ $template->category ?: '미분류' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm">{{ strtoupper($template->locale) }}</span>
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
                                    <button onclick="previewTemplate({{ $template->id }})"
                                            class="text-blue-600 hover:text-blue-800">미리보기</button>
                                    <a href="{{ route('admin.auth.emails.templates.edit', $template->id) }}"
                                       class="text-indigo-600 hover:text-indigo-800">수정</a>
                                    <button onclick="duplicateTemplate({{ $template->id }})"
                                            class="text-green-600 hover:text-green-800">복제</button>
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
        function previewTemplate(id) {
            fetch(`/admin/auth/emails/templates/${id}/preview`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // 미리보기 모달 표시
                        alert('제목: ' + data.subject + '\n\n' + data.body);
                    }
                });
        }

        function duplicateTemplate(id) {
            if (confirm('이 템플릿을 복제하시겠습니까?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/admin/auth/emails/templates/${id}/duplicate`;
                form.innerHTML = '@csrf';
                document.body.appendChild(form);
                form.submit();
            }
        }

        function deleteTemplate(id) {
            if (confirm('이 템플릿을 삭제하시겠습니까?')) {
                fetch(`/admin/auth/emails/templates/${id}`, {
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
                    } else if (data.confirm) {
                        if (confirm(data.message)) {
                            // 강제 삭제
                            location.reload();
                        }
                    }
                });
            }
        }
    </script>
    @endpush
</x-admin-layout>