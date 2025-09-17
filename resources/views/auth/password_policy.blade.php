@if(isset($password_policy) && $password_policy)
<div class="mt-3 p-3">
    <div class="flex items-start">
        <svg class="w-5 h-5 text-gray-400 mt-0.5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <div class="flex-1">
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">안전한 비밀번호를 만들어주세요</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs text-gray-400 dark:text-gray-500">
                @if(isset($password_policy['min_length']))
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>최소 {{ $password_policy['min_length'] }}자 이상</span>
                </div>
                @endif
                @if(isset($password_policy['require_lowercase']) && $password_policy['require_lowercase'])
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>소문자 (a-z)</span>
                </div>
                @endif
                @if(isset($password_policy['require_uppercase']) && $password_policy['require_uppercase'])
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>대문자 (A-Z)</span>
                </div>
                @endif
                @if(isset($password_policy['require_numbers']) && $password_policy['require_numbers'])
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>숫자 (0-9)</span>
                </div>
                @endif
                @if(isset($password_policy['require_symbols']) && $password_policy['require_symbols'])
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>특수문자 (!@#$%^&*)</span>
                </div>
                @endif
                @if(isset($password_policy['max_length']))
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>최대 {{ $password_policy['max_length'] }}자 이하</span>
                </div>
                @endif
                @if(isset($password_policy['prevent_common_passwords']) && $password_policy['prevent_common_passwords'])
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>일반적인 비밀번호 사용 금지</span>
                </div>
                @endif
                @if(isset($password_policy['prevent_sequential_chars']) && $password_policy['prevent_sequential_chars'])
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>연속된 문자 사용 금지 (123, abc)</span>
                </div>
                @endif
                @if(isset($password_policy['prevent_repeated_chars']) && $password_policy['prevent_repeated_chars'])
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>반복된 문자 사용 금지 (aaa, 111)</span>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endif
