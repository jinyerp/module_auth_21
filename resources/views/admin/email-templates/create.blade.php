<x-admin-layout>
    <x-admin-header>
        <h1 class="text-2xl font-bold">이메일 템플릿 생성</h1>
    </x-admin-header>

    <div class="bg-white rounded-lg shadow">
        <form method="POST" action="{{ route('admin.auth.emails.templates.store') }}" class="p-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- 기본 정보 -->
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">템플릿명 *</label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                               class="w-full px-3 py-2 border rounded-md @error('name') border-red-500 @enderror"
                               placeholder="예: password_reset">
                        @error('name')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">카테고리</label>
                        <input type="text" name="category" value="{{ old('category') }}" 
                               list="categories"
                               class="w-full px-3 py-2 border rounded-md"
                               placeholder="예: 인증">
                        <datalist id="categories">
                            @foreach($categories as $category)
                                <option value="{{ $category }}">
                            @endforeach
                        </datalist>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">언어 *</label>
                        <select name="locale" required
                                class="w-full px-3 py-2 border rounded-md @error('locale') border-red-500 @enderror">
                            <option value="ko" @selected(old('locale') == 'ko')>한국어</option>
                            <option value="en" @selected(old('locale') == 'en')>English</option>
                            <option value="ja" @selected(old('locale') == 'ja')>日本語</option>
                            <option value="zh-CN" @selected(old('locale') == 'zh-CN')>中文(简体)</option>
                        </select>
                        @error('locale')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="flex items-center space-x-2">
                            <input type="checkbox" name="is_active" value="1" checked
                                   class="rounded border-gray-300">
                            <span class="text-sm font-medium text-gray-700">활성화</span>
                        </label>
                    </div>
                </div>

                <!-- 변수 가이드 -->
                <div class="bg-gray-50 rounded-lg p-4">
                    <h3 class="font-medium mb-3">사용 가능한 변수</h3>
                    <div class="space-y-2 text-sm text-gray-600">
                        <p><code class="bg-white px-1 py-0.5 rounded">@{{ user_name }}</code> - 사용자 이름</p>
                        <p><code class="bg-white px-1 py-0.5 rounded">@{{ site_name }}</code> - 사이트 이름</p>
                        <p><code class="bg-white px-1 py-0.5 rounded">@{{ reset_link }}</code> - 비밀번호 재설정 링크</p>
                        <p><code class="bg-white px-1 py-0.5 rounded">@{{ verify_link }}</code> - 이메일 인증 링크</p>
                        <p><code class="bg-white px-1 py-0.5 rounded">@{{ code }}</code> - 인증 코드</p>
                        <p><code class="bg-white px-1 py-0.5 rounded">@{{ expire_minutes }}</code> - 만료 시간(분)</p>
                        <p><code class="bg-white px-1 py-0.5 rounded">@{{ ip_address }}</code> - IP 주소</p>
                        <p><code class="bg-white px-1 py-0.5 rounded">@{{ browser }}</code> - 브라우저 정보</p>
                        <p><code class="bg-white px-1 py-0.5 rounded">@{{ login_time }}</code> - 로그인 시간</p>
                    </div>
                </div>
            </div>

            <!-- 이메일 제목 -->
            <div class="mt-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">이메일 제목 *</label>
                <input type="text" name="subject" value="{{ old('subject') }}" required
                       class="w-full px-3 py-2 border rounded-md @error('subject') border-red-500 @enderror"
                       placeholder="예: [@{{ site_name }}] 비밀번호 재설정 요청">
                @error('subject')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- 이메일 본문 -->
            <div class="mt-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">이메일 본문 *</label>
                <div class="flex space-x-4 mb-2">
                    <button type="button" onclick="toggleEditor('html')" 
                            class="px-3 py-1 text-sm bg-blue-500 text-white rounded editor-btn" data-mode="html">
                        HTML 편집기
                    </button>
                    <button type="button" onclick="toggleEditor('preview')"
                            class="px-3 py-1 text-sm bg-gray-300 text-gray-700 rounded editor-btn" data-mode="preview">
                        미리보기
                    </button>
                </div>
                <textarea name="body" rows="15" required id="editor"
                          class="w-full px-3 py-2 border rounded-md font-mono text-sm @error('body') border-red-500 @enderror"
                          placeholder="HTML 형식으로 작성하세요...">{{ old('body') }}</textarea>
                <div id="preview" class="hidden w-full px-3 py-2 border rounded-md min-h-[400px] bg-white"></div>
                @error('body')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- 버튼 -->
            <div class="mt-6 flex justify-end space-x-3">
                <a href="{{ route('admin.auth.emails.templates') }}"
                   class="px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50">
                    취소
                </a>
                <button type="submit"
                        class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                    템플릿 생성
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        function toggleEditor(mode) {
            const editor = document.getElementById('editor');
            const preview = document.getElementById('preview');
            const buttons = document.querySelectorAll('.editor-btn');
            
            buttons.forEach(btn => {
                if (btn.dataset.mode === mode) {
                    btn.classList.remove('bg-gray-300', 'text-gray-700');
                    btn.classList.add('bg-blue-500', 'text-white');
                } else {
                    btn.classList.remove('bg-blue-500', 'text-white');
                    btn.classList.add('bg-gray-300', 'text-gray-700');
                }
            });
            
            if (mode === 'preview') {
                editor.classList.add('hidden');
                preview.classList.remove('hidden');
                preview.innerHTML = editor.value;
            } else {
                editor.classList.remove('hidden');
                preview.classList.add('hidden');
            }
        }
    </script>
    @endpush
</x-admin-layout>