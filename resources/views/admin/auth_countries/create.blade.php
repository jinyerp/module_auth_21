{{-- 폼 필드만 포함, 버튼은 Livewire 컴포넌트에서 처리 --}}
<form wire:submit.prevent="save" class="space-y-6">
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        
        {{-- 기본 정보 --}}
        <div class="space-y-4">
            <h3 class="text-sm font-medium text-gray-900">기본 정보</h3>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="code" class="block text-xs font-medium text-gray-700 mb-1">
                        ISO2 코드 <span class="text-red-500">*</span>
                    </label>
                    <input type="text" wire:model="form.code" id="code" maxlength="2"
                           class="w-full px-3 py-2 text-xs uppercase border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="KR" required>
                    @error('form.code')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="code3" class="block text-xs font-medium text-gray-700 mb-1">
                        ISO3 코드
                    </label>
                    <input type="text" wire:model="form.code3" id="code3" maxlength="3"
                           class="w-full px-3 py-2 text-xs uppercase border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="KOR">
                    @error('form.code3')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            
            <div>
                <label for="name" class="block text-xs font-medium text-gray-700 mb-1">
                    국가명 <span class="text-red-500">*</span>
                </label>
                <input type="text" wire:model="form.name" id="name"
                       class="w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="대한민국" required>
                @error('form.name')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
            
            <div>
                <label for="native_name" class="block text-xs font-medium text-gray-700 mb-1">
                    현지 국가명
                </label>
                <input type="text" wire:model="form.native_name" id="native_name"
                       class="w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="대한민국">
                <p class="mt-1 text-xs text-gray-500">현지 언어로 표기된 국가명</p>
            </div>
            
            <div>
                <label for="flag_emoji" class="block text-xs font-medium text-gray-700 mb-1">
                    국기 이모지
                </label>
                <input type="text" wire:model="form.flag_emoji" id="flag_emoji" maxlength="10"
                       class="w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="🇰🇷">
            </div>
        </div>
        
        {{-- 지리 정보 --}}
        <div class="space-y-4">
            <h3 class="text-sm font-medium text-gray-900">지리 정보</h3>
            
            <div>
                <label for="capital" class="block text-xs font-medium text-gray-700 mb-1">
                    수도
                </label>
                <input type="text" wire:model="form.capital" id="capital"
                       class="w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="서울">
            </div>
            
            <div>
                <label for="region" class="block text-xs font-medium text-gray-700 mb-1">
                    대륙
                </label>
                <select wire:model="form.region" id="region"
                        class="w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">선택하세요</option>
                    <option value="Africa">Africa</option>
                    <option value="Americas">Americas</option>
                    <option value="Asia">Asia</option>
                    <option value="Europe">Europe</option>
                    <option value="Oceania">Oceania</option>
                    <option value="Antarctic">Antarctic</option>
                </select>
            </div>
            
            <div>
                <label for="subregion" class="block text-xs font-medium text-gray-700 mb-1">
                    하위 지역
                </label>
                <input type="text" wire:model="form.subregion" id="subregion"
                       class="w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="Eastern Asia">
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="latitude" class="block text-xs font-medium text-gray-700 mb-1">
                        위도
                    </label>
                    <input type="number" wire:model="form.latitude" id="latitude" step="0.000001"
                           class="w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="37.566535">
                </div>
                
                <div>
                    <label for="longitude" class="block text-xs font-medium text-gray-700 mb-1">
                        경도
                    </label>
                    <input type="number" wire:model="form.longitude" id="longitude" step="0.000001"
                           class="w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="126.977969">
                </div>
            </div>
        </div>
        
        {{-- 경제 정보 --}}
        <div class="space-y-4">
            <h3 class="text-sm font-medium text-gray-900">경제 정보</h3>
            
            <div>
                <label for="currency_code" class="block text-xs font-medium text-gray-700 mb-1">
                    통화 코드
                </label>
                <input type="text" wire:model="form.currency_code" id="currency_code" maxlength="3"
                       class="w-full px-3 py-2 text-xs uppercase border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="KRW">
                <p class="mt-1 text-xs text-gray-500">ISO 4217 통화 코드</p>
            </div>
            
            <div>
                <label for="currency_name" class="block text-xs font-medium text-gray-700 mb-1">
                    통화명
                </label>
                <input type="text" wire:model="form.currency_name" id="currency_name"
                       class="w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="원">
            </div>
            
            <div>
                <label for="currency_symbol" class="block text-xs font-medium text-gray-700 mb-1">
                    통화 기호
                </label>
                <input type="text" wire:model="form.currency_symbol" id="currency_symbol" maxlength="10"
                       class="w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="₩">
            </div>
        </div>
        
        {{-- 통신 정보 --}}
        <div class="space-y-4">
            <h3 class="text-sm font-medium text-gray-900">통신 정보</h3>
            
            <div>
                <label for="phone_code" class="block text-xs font-medium text-gray-700 mb-1">
                    국가번호
                </label>
                <input type="text" wire:model="form.phone_code" id="phone_code" maxlength="10"
                       class="w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="82">
                <p class="mt-1 text-xs text-gray-500">+ 없이 숫자만 입력</p>
            </div>
            
            <div>
                <label for="languages" class="block text-xs font-medium text-gray-700 mb-1">
                    언어
                </label>
                <input type="text" wire:model="form.languages" id="languages"
                       class="w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="ko, en">
                <p class="mt-1 text-xs text-gray-500">ISO 639-1 언어 코드, 쉼표로 구분</p>
            </div>
            
            <div>
                <label for="timezone" class="block text-xs font-medium text-gray-700 mb-1">
                    주요 시간대
                </label>
                <input type="text" wire:model="form.timezone" id="timezone"
                       class="w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="Asia/Seoul">
            </div>
            
            <div>
                <label for="timezones" class="block text-xs font-medium text-gray-700 mb-1">
                    모든 시간대
                </label>
                <input type="text" wire:model="form.timezones" id="timezones"
                       class="w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="Asia/Seoul">
                <p class="mt-1 text-xs text-gray-500">여러 시간대가 있는 경우 쉼표로 구분</p>
            </div>
        </div>
        
        {{-- 표시 설정 --}}
        <div class="space-y-4">
            <h3 class="text-sm font-medium text-gray-900">표시 설정</h3>
            
            <div>
                <label for="display_order" class="block text-xs font-medium text-gray-700 mb-1">
                    표시 순서
                </label>
                <input type="number" wire:model="form.display_order" id="display_order" min="0"
                       class="w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="999">
                <p class="mt-1 text-xs text-gray-500">낮은 숫자가 먼저 표시됩니다 (주요 국가: 1-10)</p>
            </div>
            
            <div class="flex items-center">
                <input type="checkbox" wire:model="form.is_active" id="is_active"
                       class="h-3.5 w-3.5 text-blue-600 focus:ring-1 focus:ring-blue-500 border-gray-200 rounded">
                <label for="is_active" class="ml-2 text-xs font-medium text-gray-700">
                    활성화
                </label>
            </div>
            <p class="text-xs text-gray-500">비활성화된 국가는 사용자가 선택할 수 없습니다</p>
        </div>
        
        {{-- 추가 정보 --}}
        <div class="space-y-4">
            <h3 class="text-sm font-medium text-gray-900">추가 정보</h3>
            
            <div>
                <label for="numeric_code" class="block text-xs font-medium text-gray-700 mb-1">
                    숫자 코드
                </label>
                <input type="text" wire:model="form.numeric_code" id="numeric_code" maxlength="3"
                       class="w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="410">
                <p class="mt-1 text-xs text-gray-500">ISO 3166-1 numeric 코드</p>
            </div>
            
            <div>
                <label for="flag_svg" class="block text-xs font-medium text-gray-700 mb-1">
                    국기 SVG URL
                </label>
                <input type="url" wire:model="form.flag_svg" id="flag_svg"
                       class="w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="https://example.com/flags/kr.svg">
            </div>
            
            <div>
                <label for="meta" class="block text-xs font-medium text-gray-700 mb-1">
                    메타데이터 (JSON)
                </label>
                <textarea wire:model="form.meta" id="meta" rows="3"
                          class="w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                          placeholder='{"key": "value"}'></textarea>
                <p class="mt-1 text-xs text-gray-500">JSON 형식의 추가 데이터</p>
            </div>
        </div>
    </div>
    
    @if($errors->any())
    <div class="rounded-md bg-red-50 p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-red-800">입력 오류가 있습니다</h3>
                <div class="mt-2 text-xs text-red-700">
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
    @endif
</form>