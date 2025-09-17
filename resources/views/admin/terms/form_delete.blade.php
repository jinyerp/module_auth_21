<div class="bg-white rounded-xl shadow-2xl p-8 w-full max-w-md border border-gray-200">
    <div class="flex items-start gap-4 mb-4">
        <div class="flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
            <svg class="h-7 w-7 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
            </svg>
        </div>
        <div class="flex-1">
            <h3 class="text-lg font-bold text-gray-900 mb-1">{{ $title ?? '약관 삭제' }}</h3>
            <div class="text-gray-700 mb-1">
                <strong>{{ $item->title ?? '선택된 약관' }}</strong>을 삭제하시겠습니까?
            </div>
            <div class="text-red-600 text-xs">이 작업은 되돌릴 수 없습니다.</div>
        </div>
        <button type="button" id="close-popup-btn" class="text-gray-400 hover:text-gray-600 focus:outline-none ml-2 mt-1" aria-label="닫기">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <div class="flex items-center mb-3">
        <span id="random-key" class="font-mono text-base bg-gray-100 px-3 py-1 rounded select-all mr-2 border border-gray-200">{{ $randomKey ?? 'ABCD1234EF' }}</span>
        <button id="copy-key-btn" type="button" class="p-1 rounded hover:bg-gray-100 border border-gray-200" title="복사">
            <svg class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16h8M8 12h8m-7 4h.01M4 4h16v16H4V4z" />
            </svg>
        </button>
    </div>

    <input type="text" id="confirm-input"
           class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-red-400 mb-4 transition"
           placeholder="위의 난수키를 입력하세요" autocomplete="off">

    <div class="flex justify-end gap-2 mt-2">
        <button id="cancel-btn" type="button"
                class="bg-white border border-gray-300 text-gray-900 px-4 py-2 rounded hover:bg-gray-50">
            취소
        </button>
        <button id="confirm-delete-btn" type="button" disabled
                class="bg-gray-400 text-white px-4 py-2 rounded disabled:bg-gray-400 disabled:cursor-not-allowed">
            삭제
        </button>
    </div>
</div>
