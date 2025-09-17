<x-admin-layout>
    <x-admin-header>
        <h1 class="text-2xl font-bold">SMS 발송</h1>
    </x-admin-header>

    <div class="bg-white rounded-lg shadow">
        <form method="POST" action="{{ route('admin.auth.sms.send') }}" class="p-6">
            @csrf

            <!-- 수신자 선택 -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">수신자 선택 *</label>
                <div class="space-y-2">
                    <label class="flex items-center">
                        <input type="radio" name="recipient_type" value="phone" checked
                               class="mr-2" onchange="toggleRecipientInput(this.value)">
                        <span>전화번호 직접 입력</span>
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
            <div id="recipient_phone" class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">전화번호</label>
                <input type="text" name="phone" 
                       class="w-full px-3 py-2 border rounded-md @error('phone') border-red-500 @enderror"
                       placeholder="010-1234-5678">
                @error('phone')
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
                            {{ $user->name }} ({{ $user->phone }})
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
                        <strong>주의:</strong> SMS 수신 동의한 모든 사용자에게 발송됩니다.
                    </p>
                </div>
            </div>

            <!-- 발신번호 선택 -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">발신번호</label>
                <select name="sender" 
                        class="w-full px-3 py-2 border rounded-md">
                    <option value="">기본 발신번호 사용</option>
                    @foreach($senders as $sender)
                        <option value="{{ $sender->number }}" @selected($sender->is_default)>
                            {{ $sender->number }} {{ $sender->is_default ? '(기본)' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- 템플릿 선택 -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">템플릿 선택 (선택사항)</label>
                <select name="template_name" onchange="loadTemplate(this.value)"
                        class="w-full px-3 py-2 border rounded-md">
                    <option value="">템플릿 없음</option>
                    @foreach($templates->groupBy('category') as $category => $categoryTemplates)
                        <optgroup label="{{ $category ?: '미분류' }}">
                            @foreach($categoryTemplates as $template)
                                <option value="{{ $template->name }}">
                                    {{ $template->title ?: $template->name }}
                                </option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
            </div>

            <!-- 메시지 내용 -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">메시지 내용 *</label>
                <textarea name="content" rows="8" required id="sms_content"
                          class="w-full px-3 py-2 border rounded-md @error('content') border-red-500 @enderror"
                          placeholder="메시지 내용을 입력하세요..."
                          onkeyup="updateCharCount(this)">{{ old('content') }}</textarea>
                @error('content')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
                
                <!-- 문자 수 카운터 -->
                <div class="mt-2 flex justify-between text-sm text-gray-600">
                    <div>
                        <span id="char_count">0</span> / 2000 자
                    </div>
                    <div>
                        <span id="sms_type">SMS</span> 
                        <span id="sms_pages">(1건)</span>
                    </div>
                </div>
            </div>

            <!-- 버튼 -->
            <div class="flex justify-end space-x-3">
                <a href="{{ route('admin.auth.sms.logs') }}"
                   class="px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50">
                    취소
                </a>
                <button type="submit"
                        class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                    SMS 발송
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        function toggleRecipientInput(type) {
            document.getElementById('recipient_phone').classList.add('hidden');
            document.getElementById('recipient_user').classList.add('hidden');
            document.getElementById('recipient_all').classList.add('hidden');
            
            if (type === 'phone') {
                document.getElementById('recipient_phone').classList.remove('hidden');
            } else if (type === 'user') {
                document.getElementById('recipient_user').classList.remove('hidden');
            } else if (type === 'all') {
                document.getElementById('recipient_all').classList.remove('hidden');
            }
        }

        function loadTemplate(templateName) {
            if (!templateName) {
                document.getElementById('sms_content').value = '';
                updateCharCount(document.getElementById('sms_content'));
                return;
            }

            // 템플릿 정보를 가져와서 폼에 채우기
            // 실제로는 AJAX로 템플릿 정보를 가져와야 함
        }

        function updateCharCount(textarea) {
            const length = textarea.value.length;
            document.getElementById('char_count').textContent = length;
            
            let type, pages;
            if (length <= 90) {
                type = 'SMS';
                pages = '1건';
            } else if (length <= 2000) {
                type = 'LMS';
                pages = '1건';
            } else {
                type = 'MMS';
                pages = Math.ceil(length / 2000) + '건';
            }
            
            document.getElementById('sms_type').textContent = type;
            document.getElementById('sms_pages').textContent = '(' + pages + ')';
        }

        // 초기 문자 수 표시
        updateCharCount(document.getElementById('sms_content'));
    </script>
    @endpush
</x-admin-layout>