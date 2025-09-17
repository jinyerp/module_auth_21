@extends('jiny-auth::layouts.centered')

@section('title', 'JWT 로그아웃 - Jiny Auth')

@section('brand-title', 'JWT Auth')
@section('brand-subtitle', '안전하게 로그아웃하기')

@section('content')
    <div class="text-center mb-8">
        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900 mb-4">
            <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
            </svg>
        </div>
        <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">JWT 로그아웃</h3>
    </div>

    <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg p-8">

        <!-- 알림 메시지 (JavaScript용) -->
        <div id="alert-container" class="mb-4 hidden">
            <div id="alert-box" class="p-4 border rounded-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg id="alert-icon" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"></svg>
                    </div>
                    <div class="ml-3">
                        <p id="alert-message" class="text-sm font-medium"></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center mb-6">
            <p class="text-gray-600 dark:text-gray-400 mb-4">
                정말로 로그아웃하시겠습니까?
            </p>
            <p class="text-sm text-gray-500 dark:text-gray-500">
                로그아웃하면 모든 JWT 토큰이 무효화되며,<br>
                다시 로그인해야 서비스를 이용할 수 있습니다.
            </p>
        </div>

        <div class="space-y-3">
            <!-- 현재 기기에서만 로그아웃 -->
            <button id="signout-button"
                class="w-full text-white bg-red-600 hover:bg-red-700 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-800 transition duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                <span id="signout-text" class="flex items-center justify-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    로그아웃
                </span>
                <span id="signout-loading" class="flex items-center justify-center hidden">
                    <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    로그아웃 중...
                </span>
            </button>

            <!-- 모든 기기에서 로그아웃 -->
            <button id="signout-all-button"
                class="w-full text-red-600 bg-white border border-red-600 hover:bg-red-50 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-gray-700 dark:text-red-400 dark:border-red-400 dark:hover:bg-gray-600 dark:focus:ring-red-800 transition duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                <span id="signout-all-text" class="flex items-center justify-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    모든 기기에서 로그아웃
                </span>
                <span id="signout-all-loading" class="flex items-center justify-center hidden">
                    <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    모든 기기 로그아웃 중...
                </span>
            </button>

            <!-- 취소 버튼 -->
            <a href="/dashboard"
                class="block w-full text-gray-700 bg-gray-200 hover:bg-gray-300 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-gray-600 dark:text-white dark:hover:bg-gray-500 dark:focus:ring-gray-700 transition duration-200">
                <span class="flex items-center justify-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    취소
                </span>
            </a>
        </div>

        <!-- 보안 정보 -->
        <div class="mt-8 p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">보안 권장사항</h3>
                    <div class="mt-2 text-xs text-yellow-700 dark:text-yellow-300">
                        <ul class="list-disc pl-5 space-y-1">
                            <li>공용 컴퓨터를 사용하셨다면 반드시 로그아웃해주세요</li>
                            <li>의심스러운 활동이 감지되면 모든 기기에서 로그아웃을 선택해주세요</li>
                            <li>로그아웃 후에도 브라우저 캐시를 삭제하는 것이 안전합니다</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 저작권 정보 -->
    <div class="mt-8 text-xs text-gray-400 text-center">
        <p>JWT 토큰 기반 안전한 로그아웃</p>
        <p class="mt-1">© 2025 Jiny Auth. All rights reserved.</p>
    </div>
@endsection

@section('copyright', '© 2025 Jiny Auth. All rights reserved.')
@section('powered-by', 'Powered by Laravel, JWT & Sharding')

<script>
document.addEventListener('DOMContentLoaded', function() {
    const signoutButton = document.getElementById('signout-button');
    const signoutText = document.getElementById('signout-text');
    const signoutLoading = document.getElementById('signout-loading');
    
    const signoutAllButton = document.getElementById('signout-all-button');
    const signoutAllText = document.getElementById('signout-all-text');
    const signoutAllLoading = document.getElementById('signout-all-loading');
    
    const alertContainer = document.getElementById('alert-container');
    const alertBox = document.getElementById('alert-box');
    const alertIcon = document.getElementById('alert-icon');
    const alertMessage = document.getElementById('alert-message');
    
    // 알림 표시 함수
    function showAlert(type, message) {
        alertContainer.classList.add('hidden');
        
        if (type === 'success') {
            alertBox.className = 'p-4 border rounded-lg bg-green-50 border-green-200 text-green-800';
            alertIcon.className = 'h-5 w-5 text-green-400';
            alertIcon.innerHTML = '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />';
        } else {
            alertBox.className = 'p-4 border rounded-lg bg-red-50 border-red-200 text-red-800';
            alertIcon.className = 'h-5 w-5 text-red-400';
            alertIcon.innerHTML = '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />';
        }
        
        alertMessage.textContent = message;
        alertContainer.classList.remove('hidden');
    }
    
    // 토큰 가져오기
    function getAccessToken() {
        // 먼저 localStorage에서 확인
        const token = localStorage.getItem('access_token');
        if (token) return token;
        
        // 쿠키에서 확인
        const cookies = document.cookie.split(';');
        for (let cookie of cookies) {
            const [name, value] = cookie.trim().split('=');
            if (name === 'jwt_token') {
                return value;
            }
        }
        return null;
    }
    
    // 토큰 삭제
    function clearTokens() {
        localStorage.removeItem('access_token');
        localStorage.removeItem('refresh_token');
        localStorage.removeItem('token_type');
        
        // 쿠키도 삭제
        document.cookie = 'jwt_token=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
        document.cookie = 'jwt_refresh_token=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
    }
    
    // 로그아웃 처리
    async function performSignout(signoutAll = false) {
        const token = getAccessToken();
        
        if (!token) {
            showAlert('error', 'JWT 토큰을 찾을 수 없습니다. 이미 로그아웃되었을 수 있습니다.');
            setTimeout(() => {
                window.location.href = '/signin';
            }, 2000);
            return;
        }
        
        try {
            const endpoint = signoutAll ? '{{ route("jwt.signout.all") }}' : '{{ route("jwt.signout.post") }}';
            const response = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`,
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });
            
            const result = await response.json();
            
            if (result.success) {
                // 토큰 삭제
                clearTokens();
                
                showAlert('success', result.message || '로그아웃되었습니다.');
                
                // 2초 후 로그인 페이지로 리다이렉트
                setTimeout(() => {
                    window.location.href = '/signin';
                }, 2000);
            } else {
                showAlert('error', result.message || '로그아웃에 실패했습니다.');
            }
            
        } catch (error) {
            console.error('Signout error:', error);
            showAlert('error', '네트워크 오류가 발생했습니다.');
        }
    }
    
    // 로그아웃 버튼 클릭
    if (signoutButton) {
        signoutButton.addEventListener('click', async function() {
            signoutButton.disabled = true;
            signoutAllButton.disabled = true;
            signoutText.classList.add('hidden');
            signoutLoading.classList.remove('hidden');
            
            await performSignout(false);
            
            signoutButton.disabled = false;
            signoutAllButton.disabled = false;
            signoutText.classList.remove('hidden');
            signoutLoading.classList.add('hidden');
        });
    }
    
    // 모든 기기에서 로그아웃 버튼 클릭
    if (signoutAllButton) {
        signoutAllButton.addEventListener('click', async function() {
            if (!confirm('정말로 모든 기기에서 로그아웃하시겠습니까?')) {
                return;
            }
            
            signoutButton.disabled = true;
            signoutAllButton.disabled = true;
            signoutAllText.classList.add('hidden');
            signoutAllLoading.classList.remove('hidden');
            
            await performSignout(true);
            
            signoutButton.disabled = false;
            signoutAllButton.disabled = false;
            signoutAllText.classList.remove('hidden');
            signoutAllLoading.classList.add('hidden');
        });
    }
});
</script>