{{-- 상세 내용만 포함 (액션 버튼은 Livewire 컴포넌트에서 처리) --}}
<div class="bg-white shadow overflow-hidden sm:rounded-lg">
    {{-- 헤더 --}}
    <div class="px-4 py-5 sm:px-6">
        <h3 class="text-lg leading-6 font-medium text-gray-900">
            역할 정보
        </h3>
        <p class="mt-1 max-w-2xl text-sm text-gray-500">
            역할의 상세 정보와 권한 설정을 확인합니다.
        </p>
        
        {{-- 시스템 역할 배지 --}}
        @if(isset($data->slug) && in_array($data->slug, ['super-admin', 'admin', 'user']))
            <span class="mt-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                <svg class="mr-1 h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                </svg>
                시스템 역할
            </span>
        @endif
    </div>
    
    {{-- 기본 정보 --}}
    <div class="border-t border-gray-200">
        <dl>
            <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-xs font-medium text-gray-500">역할 ID</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                    {{ $data->id }}
                </dd>
            </div>
            
            <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-xs font-medium text-gray-500">역할명</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                    <strong>{{ $data->name }}</strong>
                </dd>
            </div>
            
            <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-xs font-medium text-gray-500">슬러그</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                    <code class="px-2 py-1 bg-gray-100 rounded text-xs">{{ $data->slug }}</code>
                </dd>
            </div>
            
            <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-xs font-medium text-gray-500">설명</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                    {{ $data->description ?? '-' }}
                </dd>
            </div>
            
            <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-xs font-medium text-gray-500">상태</dt>
                <dd class="mt-1 text-sm sm:mt-0 sm:col-span-2">
                    @if($data->is_active)
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                            활성
                        </span>
                    @else
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                            비활성
                        </span>
                    @endif
                    
                    {{-- 활성화 토글 버튼 --}}
                    <button wire:click="HookCustom('ToggleActive', { id: {{ $data->id }} })"
                            class="ml-2 text-xs text-blue-600 hover:text-blue-900">
                        ({{ $data->is_active ? '비활성화' : '활성화' }})
                    </button>
                </dd>
            </div>
        </dl>
    </div>
    
    {{-- 권한 설정 --}}
    <div class="border-t border-gray-200">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-sm font-medium text-gray-900 mb-3">권한 설정</h3>
            
            @php
                $permissions = isset($data->permissions) ? json_decode($data->permissions, true) : [];
            @endphp
            
            @if($permissions && count($permissions) > 0)
                {{-- 권한 목록 --}}
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
                        @foreach($permissions as $key => $value)
                            <div class="flex items-center">
                                @if($value)
                                    <svg class="h-4 w-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                    <span class="text-xs text-gray-700">{{ $key }}</span>
                                @else
                                    <svg class="h-4 w-4 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                    </svg>
                                    <span class="text-xs text-gray-500 line-through">{{ $key }}</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
                
                {{-- JSON 표시 (개발자용) --}}
                <details class="mt-4">
                    <summary class="text-xs text-blue-600 cursor-pointer hover:text-blue-800">
                        개발자용: JSON 데이터 보기
                    </summary>
                    <div class="mt-2">
                        <pre class="bg-gray-800 text-gray-100 p-3 rounded-lg overflow-x-auto text-xs">{{ json_encode($permissions, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    </div>
                </details>
            @else
                <p class="text-sm text-gray-500">설정된 권한이 없습니다.</p>
            @endif
        </div>
    </div>
    
    {{-- 통계 정보 --}}
    <div class="border-t border-gray-200">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-sm font-medium text-gray-900 mb-3">통계</h3>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="bg-gray-50 rounded-lg p-3">
                    <p class="text-xs text-gray-500">사용자 수</p>
                    <p class="text-lg font-semibold text-gray-900">
                        {{ $data->user_count ?? 0 }}
                    </p>
                </div>
                <div class="bg-gray-50 rounded-lg p-3">
                    <p class="text-xs text-gray-500">권한 수</p>
                    <p class="text-lg font-semibold text-gray-900">
                        {{ count($permissions) }}
                    </p>
                </div>
                <div class="bg-gray-50 rounded-lg p-3">
                    <p class="text-xs text-gray-500">생성일</p>
                    <p class="text-sm text-gray-900">
                        {{ \Carbon\Carbon::parse($data->created_at)->format('Y-m-d') }}
                    </p>
                </div>
                <div class="bg-gray-50 rounded-lg p-3">
                    <p class="text-xs text-gray-500">수정일</p>
                    <p class="text-sm text-gray-900">
                        {{ \Carbon\Carbon::parse($data->updated_at)->format('Y-m-d') }}
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    {{-- 관련 사용자 (있는 경우) --}}
    @if(isset($data->recent_users) && count($data->recent_users) > 0)
        <div class="border-t border-gray-200">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-sm font-medium text-gray-900 mb-3">이 역할을 가진 사용자 (최근 10명)</h3>
                <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-300">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">이름</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">이메일</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @foreach($data->recent_users as $user)
                                <tr>
                                    <td class="whitespace-nowrap px-3 py-2 text-xs text-gray-500">{{ $user->id }}</td>
                                    <td class="whitespace-nowrap px-3 py-2 text-xs text-gray-900">{{ $user->name }}</td>
                                    <td class="whitespace-nowrap px-3 py-2 text-xs text-gray-500">{{ $user->email }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                @if($data->user_count > 10)
                    <p class="mt-2 text-xs text-gray-500">
                        외 {{ $data->user_count - 10 }}명의 사용자가 더 있습니다.
                    </p>
                @endif
            </div>
        </div>
    @endif
    
    {{-- 추가 작업 --}}
    <div class="border-t border-gray-200 bg-gray-50">
        <div class="px-4 py-4 sm:px-6">
            <div class="flex items-center justify-between">
                <div class="text-xs text-gray-500">
                    추가 작업
                </div>
                <div class="flex space-x-2">
                    {{-- 역할 복사 --}}
                    <button wire:click="HookCustom('CloneRole', { id: {{ $data->id }} })"
                            onclick="return confirm('이 역할을 복사하시겠습니까?')"
                            class="px-3 py-1.5 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="inline-block w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                        </svg>
                        역할 복사
                    </button>
                    
                    {{-- 권한 내보내기 --}}
                    <button wire:click="exportPermissions"
                            class="px-3 py-1.5 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="inline-block w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        권한 내보내기
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>