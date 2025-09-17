<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            언어 관리
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
            
            <!-- 통계 카드 -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-2xl font-bold text-gray-900">{{ $stats['total'] }}</div>
                        <div class="text-sm text-gray-500">전체 언어</div>
                    </div>
                </div>
                
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-2xl font-bold text-green-600">{{ $stats['active'] }}</div>
                        <div class="text-sm text-gray-500">활성 언어</div>
                    </div>
                </div>
                
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-2xl font-bold text-gray-400">{{ $stats['inactive'] }}</div>
                        <div class="text-sm text-gray-500">비활성 언어</div>
                    </div>
                </div>
                
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-2xl font-bold text-blue-600">{{ $stats['users_with_settings'] }}</div>
                        <div class="text-sm text-gray-500">설정된 사용자</div>
                    </div>
                </div>
            </div>
            
            <!-- 언어 목록 -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">언어 목록</h3>
                        <a href="{{ route('admin.auth.languages.create') }}" 
                           class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                            언어 추가
                        </a>
                    </div>
                    
                    <!-- 검색 및 필터 -->
                    <form method="GET" action="{{ route('admin.auth.languages') }}" class="mb-4">
                        <div class="flex space-x-2">
                            <input type="text" name="search" value="{{ request('search') }}" 
                                   placeholder="언어 검색..." 
                                   class="flex-1 rounded-md border-gray-300 shadow-sm">
                            <select name="is_active" class="rounded-md border-gray-300 shadow-sm">
                                <option value="">모든 상태</option>
                                <option value="true" {{ request('is_active') === 'true' ? 'selected' : '' }}>활성</option>
                                <option value="false" {{ request('is_active') === 'false' ? 'selected' : '' }}>비활성</option>
                            </select>
                            <select name="direction" class="rounded-md border-gray-300 shadow-sm">
                                <option value="">모든 방향</option>
                                <option value="ltr" {{ request('direction') === 'ltr' ? 'selected' : '' }}>왼쪽→오른쪽</option>
                                <option value="rtl" {{ request('direction') === 'rtl' ? 'selected' : '' }}>오른쪽→왼쪽</option>
                            </select>
                            <button type="submit" class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">
                                검색
                            </button>
                        </div>
                    </form>
                    
                    <!-- 언어 테이블 -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">순서</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">코드</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">언어명</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">원어명</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">방향</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">상태</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">기본</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">관리</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200" id="language-list">
                                @foreach($languages as $language)
                                    <tr data-id="{{ $language->id }}" class="sortable-row">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <span class="cursor-move">☰</span>
                                            {{ $language->sort_order }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $language->code }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $language->name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $language->native_name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            @if($language->direction === 'rtl')
                                                <span class="text-orange-600">RTL →</span>
                                            @else
                                                <span class="text-blue-600">LTR ←</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($language->is_active)
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                    활성
                                                </span>
                                            @else
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                    비활성
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($language->is_default)
                                                <span class="text-yellow-500">★</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <a href="{{ route('admin.auth.languages.edit', $language->id) }}" 
                                               class="text-indigo-600 hover:text-indigo-900 mr-2">수정</a>
                                            <a href="{{ route('admin.auth.languages.users', $language->id) }}" 
                                               class="text-blue-600 hover:text-blue-900 mr-2">사용자</a>
                                            @if(!$language->is_default)
                                                <button onclick="deleteLanguage({{ $language->id }})" 
                                                        class="text-red-600 hover:text-red-900">삭제</button>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- 페이지네이션 -->
                    <div class="mt-4">
                        {{ $languages->links() }}
                    </div>
                    
                </div>
            </div>
            
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
    <script>
        // 정렬 기능
        new Sortable(document.getElementById('language-list'), {
            animation: 150,
            handle: '.cursor-move',
            onEnd: function (evt) {
                const languages = [];
                document.querySelectorAll('.sortable-row').forEach((row, index) => {
                    languages.push({
                        id: row.dataset.id,
                        sort_order: index + 1
                    });
                });
                
                fetch('/admin/auth/languages/reorder', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ languages })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // 순서 번호 업데이트
                        document.querySelectorAll('.sortable-row').forEach((row, index) => {
                            row.querySelector('td:first-child').textContent = '☰ ' + (index + 1);
                        });
                    }
                });
            }
        });
        
        function deleteLanguage(id) {
            if (!confirm('이 언어를 삭제하시겠습니까?')) {
                return;
            }
            
            fetch('/admin/auth/languages/' + id, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.message);
                }
            });
        }
    </script>
</x-admin-layout>