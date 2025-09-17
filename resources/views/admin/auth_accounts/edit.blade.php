{{--
    AuthAccounts 수정 폼 뷰
    Tailwind CSS 스타일 적용 및 Livewire 기능 통합
--}}
<div class="space-y-6">
    {{-- 기본 정보 섹션 --}}
    <div class="bg-white shadow sm:rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">기본 정보</h3>
            
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                {{-- ID (읽기 전용) --}}
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">
                        ID
                    </label>
                    <input type="text"
                           value="{{ $form['id'] ?? '' }}"
                           disabled
                           class="w-full px-3 py-2 text-xs border border-gray-200 rounded-md bg-gray-50 text-gray-500">
                </div>

                {{-- 가입일 (읽기 전용) --}}
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">
                        가입일
                    </label>
                    <input type="text"
                           value="{{ isset($form['created_at']) ? \Carbon\Carbon::parse($form['created_at'])->format('Y-m-d H:i:s') : '' }}"
                           disabled
                           class="w-full px-3 py-2 text-xs border border-gray-200 rounded-md bg-gray-50 text-gray-500">
                </div>

                {{-- 이름 --}}
                <div>
                    <label for="name" class="block text-xs font-medium text-gray-700 mb-1">
                        이름 <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           wire:model.live="form.name"
                           id="name"
                           class="w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500 @error('form.name') border-red-300 @enderror"
                           placeholder="회원 이름을 입력하세요">
                    @error('form.name')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- 이메일 --}}
                <div>
                    <label for="email" class="block text-xs font-medium text-gray-700 mb-1">
                        이메일 <span class="text-red-500">*</span>
                    </label>
                    <input type="email"
                           wire:model.live="form.email"
                           id="email"
                           class="w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500 @error('form.email') border-red-300 @enderror"
                           placeholder="email@example.com">
                    @error('form.email')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- 전화번호 --}}
                <div>
                    <label for="phone" class="block text-xs font-medium text-gray-700 mb-1">
                        전화번호
                    </label>
                    <input type="text"
                           wire:model.live="form.phone"
                           id="phone"
                           class="w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500 @error('form.phone') border-red-300 @enderror"
                           placeholder="010-0000-0000">
                    @error('form.phone')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- 상태 --}}
                <div>
                    <label for="status" class="block text-xs font-medium text-gray-700 mb-1">
                        상태 <span class="text-red-500">*</span>
                    </label>
                    <select wire:model.live="form.status"
                            id="status"
                            class="w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500 @error('form.status') border-red-300 @enderror">
                        <option value="active">활성</option>
                        <option value="inactive">비활성</option>
                        <option value="suspended">정지</option>
                    </select>
                    @error('form.status')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    {{-- 보안 정보 섹션 --}}
    <div class="bg-white shadow sm:rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">비밀번호 변경</h3>
            
            <div class="mb-3 p-3 bg-yellow-50 border border-yellow-200 rounded-md">
                <p class="text-xs text-yellow-800">
                    <strong>참고:</strong> 비밀번호를 변경하지 않으려면 아래 필드를 비워두세요.
                </p>
            </div>
            
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                {{-- 새 비밀번호 --}}
                <div>
                    <label for="password" class="block text-xs font-medium text-gray-700 mb-1">
                        새 비밀번호
                    </label>
                    <input type="password"
                           wire:model.live="form.password"
                           id="password"
                           class="w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500 @error('form.password') border-red-300 @enderror"
                           placeholder="변경하지 않으려면 비워두세요">
                    @error('form.password')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- 비밀번호 확인 --}}
                <div>
                    <label for="password_confirmation" class="block text-xs font-medium text-gray-700 mb-1">
                        새 비밀번호 확인
                    </label>
                    <input type="password"
                           wire:model.live="form.password_confirmation"
                           id="password_confirmation"
                           class="w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500 @error('form.password_confirmation') border-red-300 @enderror"
                           placeholder="새 비밀번호 재입력">
                    @error('form.password_confirmation')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- 비밀번호 요구사항 --}}
            @if(isset($form['password']) && $form['password'])
                <div class="mt-4 p-3 bg-gray-50 rounded-md">
                    <p class="text-xs text-gray-600">비밀번호 요구사항:</p>
                    <ul class="mt-1 text-xs text-gray-500 list-disc list-inside">
                        <li>최소 8자 이상</li>
                        <li>대문자, 소문자, 숫자, 특수문자 중 3가지 이상 포함</li>
                        <li>사용자 정보(이름, 이메일)와 유사하지 않아야 함</li>
                    </ul>
                </div>
            @endif
        </div>
    </div>

    {{-- 추가 정보 섹션 --}}
    <div class="bg-white shadow sm:rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">추가 정보</h3>
            
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                {{-- 회원 등급 --}}
                <div>
                    <label for="grade_id" class="block text-xs font-medium text-gray-700 mb-1">
                        회원 등급
                    </label>
                    <select wire:model.live="form.grade_id"
                            id="grade_id"
                            class="w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500 @error('form.grade_id') border-red-300 @enderror">
                        <option value="">선택하세요</option>
                        @if(isset($grades))
                            @foreach($grades as $grade)
                                <option value="{{ $grade->id }}">{{ $grade->name }}</option>
                            @endforeach
                        @endif
                    </select>
                    @error('form.grade_id')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- 국가 --}}
                <div>
                    <label for="country_id" class="block text-xs font-medium text-gray-700 mb-1">
                        국가
                    </label>
                    <select wire:model.live="form.country_id"
                            id="country_id"
                            class="w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500 @error('form.country_id') border-red-300 @enderror">
                        <option value="">선택하세요</option>
                        @if(isset($countries))
                            @foreach($countries as $country)
                                <option value="{{ $country->id }}">{{ $country->name }}</option>
                            @endforeach
                        @endif
                    </select>
                    @error('form.country_id')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- 이메일 인증 상태 --}}
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">
                        이메일 인증
                    </label>
                    <div class="flex items-center">
                        @if(isset($form['email_verified_at']) && $form['email_verified_at'])
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">
                                인증완료
                            </span>
                            <span class="ml-2 text-xs text-gray-500">
                                {{ \Carbon\Carbon::parse($form['email_verified_at'])->format('Y-m-d H:i') }}
                            </span>
                        @else
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">
                                미인증
                            </span>
                        @endif
                    </div>
                </div>

                {{-- 마지막 로그인 --}}
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">
                        마지막 로그인
                    </label>
                    <input type="text"
                           value="{{ isset($form['last_login_at']) && $form['last_login_at'] ? \Carbon\Carbon::parse($form['last_login_at'])->format('Y-m-d H:i:s') : '없음' }}"
                           disabled
                           class="w-full px-3 py-2 text-xs border border-gray-200 rounded-md bg-gray-50 text-gray-500">
                </div>
            </div>
        </div>
    </div>

    {{-- 수정 정보 --}}
    <div class="bg-gray-50 px-4 py-3 sm:px-6 rounded-lg">
        <div class="text-xs text-gray-500">
            <p>마지막 수정: {{ isset($form['updated_at']) ? \Carbon\Carbon::parse($form['updated_at'])->format('Y-m-d H:i:s') : '-' }}</p>
        </div>
    </div>
</div>