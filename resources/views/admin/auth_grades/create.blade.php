{{-- 회원 등급 생성 폼 --}}
<form wire:submit.prevent="store" class="space-y-6">
    <div class="bg-white shadow-sm rounded-lg">
        {{-- 기본 정보 섹션 --}}
        <div class="px-4 py-5 sm:p-6 border-b border-gray-200">
            <h3 class="text-sm font-medium text-gray-900">기본 정보</h3>
            <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                {{-- 등급명 --}}
                <div>
                    <label for="name" class="block text-xs font-medium text-gray-700 mb-1">
                        등급명 <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="name"
                           wire:model.defer="form.name"
                           class="w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="예: 골드 회원"
                           required>
                    @error('form.name')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                {{-- 등급 코드 --}}
                <div>
                    <label for="code" class="block text-xs font-medium text-gray-700 mb-1">
                        등급 코드 <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="code"
                           wire:model.defer="form.code"
                           class="w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="예: GOLD"
                           required>
                    <p class="mt-1 text-xs text-gray-500">시스템에서 사용할 고유 코드 (영문 대문자 권장)</p>
                    @error('form.code')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                {{-- 레벨 --}}
                <div>
                    <label for="level" class="block text-xs font-medium text-gray-700 mb-1">
                        레벨 <span class="text-red-500">*</span>
                    </label>
                    <input type="number" 
                           id="level"
                           wire:model.defer="form.level"
                           class="w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                           min="1"
                           max="99"
                           required>
                    <p class="mt-1 text-xs text-gray-500">등급의 레벨 (높을수록 상위 등급)</p>
                    @error('form.level')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                {{-- 상태 --}}
                <div>
                    <label for="is_active" class="block text-xs font-medium text-gray-700 mb-1">
                        상태
                    </label>
                    <select id="is_active"
                            wire:model.defer="form.is_active"
                            class="w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                        <option value="1">활성</option>
                        <option value="0">비활성</option>
                    </select>
                    @error('form.is_active')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                {{-- 설명 --}}
                <div class="sm:col-span-2">
                    <label for="description" class="block text-xs font-medium text-gray-700 mb-1">
                        설명
                    </label>
                    <textarea id="description"
                              wire:model.defer="form.description"
                              rows="3"
                              class="w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                              placeholder="등급에 대한 설명을 입력하세요"></textarea>
                    @error('form.description')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>
        
        {{-- 혜택 설정 섹션 --}}
        <div class="px-4 py-5 sm:p-6 border-b border-gray-200">
            <h3 class="text-sm font-medium text-gray-900">혜택 설정</h3>
            <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                {{-- 포인트 적립률 --}}
                <div>
                    <label for="point_rate" class="block text-xs font-medium text-gray-700 mb-1">
                        포인트 적립률 (%) <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="number" 
                               id="point_rate"
                               wire:model.defer="form.point_rate"
                               class="w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                               min="0"
                               max="100"
                               step="0.1"
                               required>
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 text-xs">%</span>
                        </div>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">구매 금액 대비 포인트 적립 비율</p>
                    @error('form.point_rate')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                {{-- 할인율 --}}
                <div>
                    <label for="discount_rate" class="block text-xs font-medium text-gray-700 mb-1">
                        할인율 (%) <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="number" 
                               id="discount_rate"
                               wire:model.defer="form.discount_rate"
                               class="w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                               min="0"
                               max="100"
                               step="1"
                               required>
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 text-xs">%</span>
                        </div>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">기본 할인율</p>
                    @error('form.discount_rate')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                {{-- 최소 구매금액 --}}
                <div>
                    <label for="min_purchase" class="block text-xs font-medium text-gray-700 mb-1">
                        최소 구매금액 <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 text-xs">₩</span>
                        </div>
                        <input type="number" 
                               id="min_purchase"
                               wire:model.defer="form.min_purchase"
                               class="w-full pl-8 pr-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                               min="0"
                               required>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">이 등급 승급을 위한 최소 누적 구매금액</p>
                    @error('form.min_purchase')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                {{-- 추가 혜택 --}}
                <div class="sm:col-span-2">
                    <label class="block text-xs font-medium text-gray-700 mb-2">
                        추가 혜택
                    </label>
                    <div class="space-y-2">
                        <label class="inline-flex items-center">
                            <input type="checkbox" 
                                   wire:model.defer="form.benefits.free_shipping"
                                   class="h-3.5 w-3.5 text-blue-600 focus:ring-1 focus:ring-blue-500 border-gray-200 rounded">
                            <span class="ml-2 text-xs text-gray-700">무료 배송</span>
                        </label>
                        <label class="inline-flex items-center ml-4">
                            <input type="checkbox" 
                                   wire:model.defer="form.benefits.birthday_coupon"
                                   class="h-3.5 w-3.5 text-blue-600 focus:ring-1 focus:ring-blue-500 border-gray-200 rounded">
                            <span class="ml-2 text-xs text-gray-700">생일 쿠폰</span>
                        </label>
                        <label class="inline-flex items-center ml-4">
                            <input type="checkbox" 
                                   wire:model.defer="form.benefits.exclusive_sale"
                                   class="h-3.5 w-3.5 text-blue-600 focus:ring-1 focus:ring-blue-500 border-gray-200 rounded">
                            <span class="ml-2 text-xs text-gray-700">전용 세일</span>
                        </label>
                        <label class="inline-flex items-center ml-4">
                            <input type="checkbox" 
                                   wire:model.defer="form.benefits.priority_support"
                                   class="h-3.5 w-3.5 text-blue-600 focus:ring-1 focus:ring-blue-500 border-gray-200 rounded">
                            <span class="ml-2 text-xs text-gray-700">우선 고객지원</span>
                        </label>
                        <label class="inline-flex items-center ml-4">
                            <input type="checkbox" 
                                   wire:model.defer="form.benefits.early_access"
                                   class="h-3.5 w-3.5 text-blue-600 focus:ring-1 focus:ring-blue-500 border-gray-200 rounded">
                            <span class="ml-2 text-xs text-gray-700">신제품 우선 구매</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>
        
        {{-- 시각적 설정 섹션 --}}
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-sm font-medium text-gray-900">시각적 설정</h3>
            <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                {{-- 등급 색상 --}}
                <div>
                    <label for="color" class="block text-xs font-medium text-gray-700 mb-1">
                        등급 색상
                    </label>
                    <div class="flex items-center space-x-2">
                        <input type="color" 
                               id="color"
                               wire:model.defer="form.color"
                               class="h-8 w-16 border border-gray-300 rounded cursor-pointer">
                        <input type="text" 
                               wire:model.defer="form.color"
                               class="flex-1 px-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="#6B7280"
                               pattern="^#[0-9A-Fa-f]{6}$">
                    </div>
                    <p class="mt-1 text-xs text-gray-500">등급을 나타낼 색상 선택</p>
                    @error('form.color')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                {{-- 아이콘 --}}
                <div>
                    <label for="icon" class="block text-xs font-medium text-gray-700 mb-1">
                        아이콘
                    </label>
                    <input type="text" 
                           id="icon"
                           wire:model.defer="form.icon"
                           class="w-full px-3 py-2 text-xs border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="예: star, crown, diamond">
                    <p class="mt-1 text-xs text-gray-500">등급을 나타낼 아이콘 이름 (Heroicons 사용)</p>
                    @error('form.icon')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>
    </div>
</form>