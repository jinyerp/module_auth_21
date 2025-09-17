<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            새 메시지 작성
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    
                    @if ($errors->any())
                        <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
                            <ul class="list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    
                    <form method="POST" action="{{ route('home.message.send') }}">
                        @csrf
                        
                        <div class="mb-4">
                            <label for="recipient_email" class="block text-sm font-medium text-gray-700 mb-2">
                                받는 사람 이메일
                            </label>
                            <input type="email" name="recipient_email" id="recipient_email" 
                                   value="{{ old('recipient_email', $recipient?->email) }}"
                                   class="w-full rounded-md border-gray-300 shadow-sm"
                                   placeholder="example@email.com" required>
                        </div>
                        
                        <div class="mb-4">
                            <label for="subject" class="block text-sm font-medium text-gray-700 mb-2">
                                제목
                            </label>
                            <input type="text" name="subject" id="subject" 
                                   value="{{ old('subject') }}"
                                   class="w-full rounded-md border-gray-300 shadow-sm"
                                   placeholder="메시지 제목을 입력하세요" required>
                        </div>
                        
                        <div class="mb-4">
                            <label for="priority" class="block text-sm font-medium text-gray-700 mb-2">
                                우선순위
                            </label>
                            <select name="priority" id="priority" class="w-full rounded-md border-gray-300 shadow-sm">
                                <option value="normal" {{ old('priority') === 'normal' ? 'selected' : '' }}>보통</option>
                                <option value="low" {{ old('priority') === 'low' ? 'selected' : '' }}>낮음</option>
                                <option value="high" {{ old('priority') === 'high' ? 'selected' : '' }}>높음</option>
                                <option value="urgent" {{ old('priority') === 'urgent' ? 'selected' : '' }}>긴급</option>
                            </select>
                        </div>
                        
                        @if($templates->count() > 0)
                            <div class="mb-4">
                                <label for="template" class="block text-sm font-medium text-gray-700 mb-2">
                                    템플릿 사용 (선택사항)
                                </label>
                                <select id="template" class="w-full rounded-md border-gray-300 shadow-sm" onchange="loadTemplate(this.value)">
                                    <option value="">템플릿을 선택하세요</option>
                                    @foreach($templates->groupBy('category') as $category => $categoryTemplates)
                                        <optgroup label="{{ $category ?: '기본' }}">
                                            @foreach($categoryTemplates as $template)
                                                <option value="{{ $template->id }}" 
                                                        data-subject="{{ $template->subject }}"
                                                        data-content="{{ $template->content }}">
                                                    {{ $template->name }}
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        
                        <div class="mb-4">
                            <label for="content" class="block text-sm font-medium text-gray-700 mb-2">
                                내용
                            </label>
                            <textarea name="content" id="content" rows="10" 
                                      class="w-full rounded-md border-gray-300 shadow-sm"
                                      placeholder="메시지 내용을 입력하세요" required>{{ old('content') }}</textarea>
                        </div>
                        
                        <div class="flex justify-between">
                            <a href="{{ route('home.message') }}" class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">
                                취소
                            </a>
                            <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                                메시지 발송
                            </button>
                        </div>
                    </form>
                    
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function loadTemplate(templateId) {
            if (!templateId) {
                return;
            }
            
            const option = document.querySelector(`#template option[value="${templateId}"]`);
            if (option) {
                const subject = option.getAttribute('data-subject');
                const content = option.getAttribute('data-content');
                
                document.getElementById('subject').value = subject;
                document.getElementById('content').value = content;
            }
        }
    </script>
</x-app-layout>