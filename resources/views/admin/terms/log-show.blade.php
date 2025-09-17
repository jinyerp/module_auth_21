@extends('jiny-auth::layouts.resource.show')
@section('title', '약관 로그 상세 정보')
@section('description', '약관 동의 로그의 상세 정보를 확인합니다.')

{{-- 페이지 상태 스크립트 --}}
@section('page-script', 'show')

@section('heading')
    <section class="w-full">
        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
            <article class="flex-1 min-w-0">
                <h1 class="text-2xl font-semibold text-gray-900">약관 로그 상세 정보</h1>
                <p class="mt-2 text-base text-gray-700 leading-relaxed">약관 동의 로그의 상세 정보를 확인합니다. 사용자별 약관 동의 현황과 상세 정보를 볼 수 있습니다.</p>
            </article>
            <aside class="flex-shrink-0 flex gap-2">
                <x-ui::button-light href="{{ route($route . 'logs') }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    로그 목록
                </x-ui::button-light>
            </aside>
        </div>
    </section>
@endsection

@section('content')
    <div class="pt-2 pb-4">
        <div class="space-y-12">
            <x-ui::form-section
                title="기본 정보"
                description="약관 로그의 기본 정보입니다.">
                <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6 md:col-span-2">
                    <div class="sm:col-span-3">
                        <div class="text-sm font-medium text-gray-700 mb-1">로그 ID</div>
                        <div class="text-sm text-gray-900">{{ $item->id }}</div>
                    </div>
                    <div class="sm:col-span-3">
                        <div class="text-sm font-medium text-gray-700 mb-1">사용자 ID</div>
                        <div class="text-sm text-gray-900">{{ $item->user_id }}</div>
                    </div>
                    <div class="sm:col-span-3">
                        <div class="text-sm font-medium text-gray-700 mb-1">약관 ID</div>
                        <div class="text-sm text-gray-900">{{ $item->term_id }}</div>
                    </div>
                    <div class="sm:col-span-3">
                        <div class="text-sm font-medium text-gray-700 mb-1">동의 여부</div>
                        <div class="text-sm text-gray-900">
                            @if ($item->agreed)
                                <x-ui::badge-success text="동의" />
                            @else
                                <x-ui::badge-warning text="미동의" />
                            @endif
                        </div>
                    </div>
                </div>
            </x-ui::form-section>

            <x-ui::form-section
                title="동의 정보"
                description="약관 동의 관련 상세 정보입니다.">
                <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6 md:col-span-2">
                    <div class="sm:col-span-3">
                        <div class="text-sm font-medium text-gray-700 mb-1">동의 시간</div>
                        <div class="text-sm text-gray-900">{{ $item->agreed_at ? $item->agreed_at->format('Y-m-d H:i:s') : '-' }}</div>
                    </div>
                    <div class="sm:col-span-3">
                        <div class="text-sm font-medium text-gray-700 mb-1">IP 주소</div>
                        <div class="text-sm text-gray-900">{{ $item->ip_address ?: '-' }}</div>
                    </div>
                    <div class="sm:col-span-6">
                        <div class="text-sm font-medium text-gray-700 mb-1">User Agent</div>
                        <div class="text-sm text-gray-900">{{ $item->user_agent ?: '-' }}</div>
                    </div>
                    <div class="sm:col-span-6">
                        <div class="text-sm font-medium text-gray-700 mb-1">메타데이터</div>
                        <div class="text-sm text-gray-900">
                            @if($item->metadata)
                                <pre class="bg-gray-100 p-2 rounded text-xs">{{ json_encode($item->metadata, JSON_PRETTY_PRINT) }}</pre>
                            @else
                                -
                            @endif
                        </div>
                    </div>
                </div>
            </x-ui::form-section>

            <x-ui::form-section
                title="시스템 정보"
                description="로그의 시스템 관리 정보입니다.">
                <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6 md:col-span-2">
                    <div class="sm:col-span-3">
                        <div class="text-sm font-medium text-gray-700 mb-1">생성일</div>
                        <div class="text-sm text-gray-900">{{ $item->created_at->format('Y-m-d H:i:s') }}</div>
                    </div>
                    <div class="sm:col-span-3">
                        <div class="text-sm font-medium text-gray-700 mb-1">수정일</div>
                        <div class="text-sm text-gray-900">{{ $item->updated_at->format('Y-m-d H:i:s') }}</div>
                    </div>
                </div>
            </x-ui::form-section>
        </div>
    </div>
@endsection
