<x-admin-layout>
    <x-admin-header>
        <h1 class="text-2xl font-bold">이메일 발송</h1>
    </x-admin-header>

    <div class="bg-white rounded-lg shadow">
        <form method="POST" action="{{ route('admin.auth.emails.send') }}" class="p-6">
            @csrf

            <!-- 수신자 선택 -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">수신자 선택 *</label>
                <div class="space-y-2">
                    <label class="flex items-center">
                        <input type="radio" name="recipient_type" value="email" checked
                               class="mr-2" onchange="toggleRecipientInput(this.value)">
                        <span>직접 입력</span>
                    </label>
                    <label class="flex items-center">
                        <input type="radio" name="recipient_type" value="user"
                               class="mr-2" onchange="toggleRecipientInput(this.value)">
                        <span>사용자 선택</span>
                    </label>
                    <label class="flex items-center">
                        <input type="radio" name="recipient_type" value="all"
                               class="mr-2" onchange="toggleRecipientInput(this.value)">
                        <span>전체 사용자</span>
                    </label>
                </div>
            </div>

            <!-- 수신자 입력 필드 -->
            <div id="recipient_email" class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">이메일 주소</label>
                <input type="email" name="email" 
                       class="w-full px-3 py-2 border rounded-md @error('email') border-red-500 @enderror"
                       placeholder="example@domain.com">
                @error('email')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div id="recipient_user" class="mb-6 hidden">
                <label class="block text-sm font-medium text-gray-700 mb-1">사용자 선택</label>
                <select name="user_id" 
                        class="w-full px-3 py-2 border rounded-md @error('user_id') border-red-500 @enderror">
                    <option value="">사용자를 선택하세요</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">
                            {{ $user->name }} ({{ $user->email }})
                        </option>
                    @endforeach
                </select>
                @error('user_id')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div id="recipient_all" class="mb-6 hidden">
                <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4">
                    <p class="text-sm text-yellow-800">
                        <strong>주의:</strong> 이메일 수신 동의한 모든 사용자에게 발송됩니다.
                    </p>
                </div>
            </div>

            <!-- 템플릿 선택 -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">템플릿 선택 (선택사항)</label>
                <select name="template_name" onchange="loadTemplate(this.value)"
                        class="w-full px-3 py-2 border rounded-md">
                    <option value="">템플릿 없음</option>
                    @foreach($templates as $template)
                        <option value="{{ $template->name }}">
                            {{ $template->name }} - {{ $template->subject }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- 이메일 제목 -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">제목 *</label>
                <input type="text" name="subject" value="{{ old('subject') }}" required
                       id="email_subject"
                       class="w-full px-3 py-2 border rounded-md @error('subject') border-red-500 @enderror"
                       placeholder="이메일 제목을 입력하세요">
                @error('subject')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- 이메일 본문 -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">본문 *</label>
                <textarea name="body" rows="12" required id="email_body"
                          class="w-full px-3 py-2 border rounded-md @error('body') border-red-500 @enderror"
                          placeholder="이메일 내용을 입력하세요...">{{ old('body') }}</textarea>
                @error('body')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- 발송 옵션 -->
            <div class="mb-6">
                <label class="flex items-center">
                    <input type="checkbox" name="send_immediately" value="1" checked
                           class="rounded border-gray-300 mr-2">
                    <span class="text-sm text-gray-700">즉시 발송</span>
                </label>
            </div>

            <!-- 버튼 -->
            <div class="flex justify-end space-x-3">
                <a href="{{ route('admin.auth.emails.logs') }}"
                   class="px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50">
                    취소
                </a>
                <button type="submit"
                        class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                    이메일 발송
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        function toggleRecipientInput(type) {
            document.getElementById('recipient_email').classList.add('hidden');
            document.getElementById('recipient_user').classList.add('hidden');
            document.getElementById('recipient_all').classList.add('hidden');
            
            if (type === 'email') {
                document.getElementById('recipient_email').classList.remove('hidden');
            } else if (type === 'user') {
                document.getElementById('recipient_user').classList.remove('hidden');
            } else if (type === 'all') {
                document.getElementById('recipient_all').classList.remove('hidden');
            }
        }

        function loadTemplate(templateName) {
            if (!templateName) {
                document.getElementById('email_subject').value = '';
                document.getElementById('email_body').value = '';
                return;
            }

            // 템플릿 정보를 가져와서 폼에 채우기
            // 실제로는 AJAX로 템플릿 정보를 가져와야 함
            fetch(`/admin/auth/emails/templates/get/${templateName}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('email_subject').value = data.subject;
                        document.getElementById('email_body').value = data.body;
                    }
                })
                .catch(() => {
                    // 에러 처리
                });
        }
    </script>
    @endpush
</x-admin-layout>