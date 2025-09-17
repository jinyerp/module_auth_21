<div class="space-y-6">
    {{-- 헤더 정보 --}}
    <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900">블랙리스트 상세 정보</h2>
                <div class="flex items-center space-x-2">
                    @if($data->is_active)
                        @if($data->expires_at && $data->expires_at <= now())
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800">
                                만료됨
                            </span>
                        @else
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">
                                활성
                            </span>
                        @endif
                    @else
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">
                            비활성
                        </span>
                    @endif
                    
                    @if(!$data->expires_at)
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">
                            영구 차단
                        </span>
                    @endif
                </div>
            </div>
        </div>

        {{-- 기본 정보 --}}
        <div class="px-6 py-4">
            <h3 class="text-sm font-medium text-gray-900 mb-3">기본 정보</h3>
            <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-3">
                <div>
                    <dt class="text-xs font-medium text-gray-500">ID</dt>
                    <dd class="mt-1 text-sm text-gray-900">#{{ $data->id }}</dd>
                </div>
                
                <div>
                    <dt class="text-xs font-medium text-gray-500">차단 유형</dt>
                    <dd class="mt-1">
                        <span class="px-2 py-1 text-xs font-medium rounded-full 
                            @if($data->type === 'email') bg-blue-100 text-blue-800
                            @elseif($data->type === 'ip') bg-purple-100 text-purple-800
                            @elseif($data->type === 'phone') bg-green-100 text-green-800
                            @elseif($data->type === 'domain') bg-yellow-100 text-yellow-800
                            @else bg-gray-100 text-gray-800
                            @endif">
                            {{ $data->type_label ?? $data->type }}
                        </span>
                    </dd>
                </div>
                
                <div>
                    <dt class="text-xs font-medium text-gray-500">차단 대상</dt>
                    <dd class="mt-1">
                        <code class="bg-gray-100 px-2 py-1 rounded text-sm">{{ $data->value }}</code>
                    </dd>
                </div>
                
                <div>
                    <dt class="text-xs font-medium text-gray-500">차단 사유</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $data->reason }}</dd>
                </div>
                
                @if($data->description)
                <div class="md:col-span-2">
                    <dt class="text-xs font-medium text-gray-500">상세 설명</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $data->description }}</dd>
                </div>
                @endif
            </dl>
        </div>
    </div>

    {{-- 상태 정보 --}}
    <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-sm font-medium text-gray-900">상태 정보</h3>
        </div>
        <div class="px-6 py-4">
            <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-3">
                <div>
                    <dt class="text-xs font-medium text-gray-500">활성화 상태</dt>
                    <dd class="mt-1">
                        @if($data->is_active)
                            <span class="text-green-600 text-sm">● 활성</span>
                        @else
                            <span class="text-gray-400 text-sm">● 비활성</span>
                        @endif
                    </dd>
                </div>
                
                <div>
                    <dt class="text-xs font-medium text-gray-500">만료 일시</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        @if($data->expires_at)
                            {{ $data->expires_at->format('Y-m-d H:i:s') }}
                            @if($data->expires_at > now())
                                <span class="text-xs text-gray-500">({{ $data->expires_at->diffForHumans() }})</span>
                            @else
                                <span class="text-xs text-red-600">(만료됨)</span>
                            @endif
                        @else
                            <span class="text-blue-600">영구 차단</span>
                        @endif
                    </dd>
                </div>
            </dl>
        </div>
    </div>

    {{-- 차단 통계 --}}
    <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-sm font-medium text-gray-900">차단 통계</h3>
        </div>
        <div class="px-6 py-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="text-center">
                    <p class="text-2xl font-bold text-gray-900">{{ $data->hit_count }}</p>
                    <p class="text-xs text-gray-500 mt-1">총 차단 횟수</p>
                </div>
                
                <div class="text-center">
                    <p class="text-2xl font-bold text-gray-900">
                        @if(isset($data->statistics))
                            {{ $data->statistics['daily_average'] ?? 0 }}
                        @else
                            0
                        @endif
                    </p>
                    <p class="text-xs text-gray-500 mt-1">일평균 차단</p>
                </div>
                
                <div class="text-center">
                    <p class="text-sm font-medium text-gray-900">
                        @if($data->last_hit_at)
                            {{ $data->last_hit_at->format('m-d H:i') }}
                        @else
                            -
                        @endif
                    </p>
                    <p class="text-xs text-gray-500 mt-1">마지막 차단</p>
                </div>
                
                <div class="text-center">
                    @if(isset($data->statistics) && isset($data->statistics['effectiveness']))
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-{{ $data->statistics['effectiveness_color'] }}-100 text-{{ $data->statistics['effectiveness_color'] }}-800">
                            {{ $data->statistics['effectiveness'] }}
                        </span>
                    @else
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">
                            미평가
                        </span>
                    @endif
                    <p class="text-xs text-gray-500 mt-1">효과성</p>
                </div>
            </div>
        </div>
    </div>

    {{-- 감사 정보 --}}
    <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-sm font-medium text-gray-900">감사 정보</h3>
        </div>
        <div class="px-6 py-4">
            <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-3">
                <div>
                    <dt class="text-xs font-medium text-gray-500">추가자</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        {{ $data->blocked_by_name ?? 'System' }}
                        @if($data->added_by_email)
                            <span class="text-xs text-gray-500">({{ $data->added_by_email }})</span>
                        @endif
                    </dd>
                </div>
                
                <div>
                    <dt class="text-xs font-medium text-gray-500">등록 일시</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        {{ $data->created_at->format('Y-m-d H:i:s') }}
                        <span class="text-xs text-gray-500">({{ $data->created_at->diffForHumans() }})</span>
                    </dd>
                </div>
                
                <div>
                    <dt class="text-xs font-medium text-gray-500">수정 일시</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        {{ $data->updated_at->format('Y-m-d H:i:s') }}
                        <span class="text-xs text-gray-500">({{ $data->updated_at->diffForHumans() }})</span>
                    </dd>
                </div>
                
                <div>
                    <dt class="text-xs font-medium text-gray-500">운영 기간</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        {{ $data->created_at->diffInDays(now()) }}일
                    </dd>
                </div>
            </dl>
        </div>
    </div>

    {{-- 변경 이력 --}}
    @if(isset($data->modification_history) && count($data->modification_history) > 0)
    <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-sm font-medium text-gray-900">변경 이력</h3>
        </div>
        <div class="px-6 py-4">
            <div class="space-y-3">
                @foreach($data->modification_history as $history)
                <div class="border-l-2 border-gray-200 pl-3">
                    <p class="text-xs text-gray-500">
                        {{ $history['date'] ?? '' }} - {{ $history['user'] ?? 'Unknown' }}
                    </p>
                    @if(isset($history['changes']) && is_array($history['changes']))
                    <ul class="mt-1 text-xs text-gray-700">
                        @foreach($history['changes'] as $field => $change)
                        <li>{{ $field }}: {{ is_array($change) ? json_encode($change) : $change }}</li>
                        @endforeach
                    </ul>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- 관련 차단 --}}
    @if(isset($data->related_entries) && !empty($data->related_entries))
    <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-sm font-medium text-gray-900">관련 차단 항목</h3>
        </div>
        <div class="px-6 py-4">
            @if(isset($data->related_entries['same_reason']))
            <div class="mb-4">
                <h4 class="text-xs font-medium text-gray-700 mb-2">같은 사유로 차단된 항목</h4>
                <div class="space-y-1">
                    @foreach($data->related_entries['same_reason'] as $related)
                    <div class="flex items-center space-x-2 text-xs">
                        <span class="text-gray-500">{{ $related->type }}:</span>
                        <code class="bg-gray-100 px-1 rounded">{{ $related->value }}</code>
                        <a href="{{ route('admin.auth.blacklist.show', $related->id) }}" class="text-blue-600 hover:text-blue-800">
                            보기 →
                        </a>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
            
            @if(isset($data->related_entries['same_adder']))
            <div>
                <h4 class="text-xs font-medium text-gray-700 mb-2">같은 관리자가 추가한 최근 항목</h4>
                <div class="space-y-1">
                    @foreach($data->related_entries['same_adder'] as $related)
                    <div class="flex items-center space-x-2 text-xs">
                        <span class="text-gray-500">{{ $related->type }}:</span>
                        <code class="bg-gray-100 px-1 rounded">{{ $related->value }}</code>
                        <span class="text-gray-400">{{ $related->created_at->format('m-d') }}</span>
                        <a href="{{ route('admin.auth.blacklist.show', $related->id) }}" class="text-blue-600 hover:text-blue-800">
                            보기 →
                        </a>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
    @endif

    {{-- 메타데이터 --}}
    @if(isset($data->meta_parsed) && !empty($data->meta_parsed))
    <div class="bg-gray-50 rounded-lg p-4">
        <details>
            <summary class="text-xs font-medium text-gray-700 cursor-pointer">메타데이터 (개발자용)</summary>
            <pre class="mt-2 text-xs text-gray-600 overflow-x-auto">{{ json_encode($data->meta_parsed, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
        </details>
    </div>
    @endif
</div>