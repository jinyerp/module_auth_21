@extends('jiny-auth::layouts.resource.show')
@section('title', '약관 상세 정보')
@section('description', '약관의 상세 정보를 확인합니다.')

{{-- 페이지 상태 스크립트 --}}
@section('page-script', 'show')

@section('heading')
    <section class="w-full">
        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
            <article class="flex-1 min-w-0">
                <h1 class="text-2xl font-semibold text-gray-900">약관 상세 정보</h1>
                <p class="mt-2 text-base text-gray-700 leading-relaxed">약관의 상세 정보를 확인합니다. 약관명, 타입, 내용, 버전 등의 정보를 볼 수 있습니다.</p>
            </article>
            <aside class="flex-shrink-0 flex gap-2">
                <x-ui::button-light href="{{ route($route . 'index') }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    약관 목록
                </x-ui::button-light>
                <x-ui::button-primary href="{{ route($route . 'edit', $item->id) }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    수정
                </x-ui::button-primary>
            </aside>
        </div>
    </section>
@endsection

@section('content')
    <div class="pt-2 pb-4">
        <div class="space-y-12">
            <x-ui::form-section
                title="기본 정보"
                description="약관의 기본 정보입니다.">
                <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6 md:col-span-2">
                    <div class="sm:col-span-6">
                        <div class="text-sm font-medium text-gray-700 mb-1">약관명</div>
                        <div class="text-sm text-gray-900">{{ $item->title }}</div>
                    </div>
                    <div class="sm:col-span-3">
                        <div class="text-sm font-medium text-gray-700 mb-1">약관 타입</div>
                        <div class="text-sm text-gray-900">
                            @if ($item->type === 'required')
                                <x-ui::badge-danger text="필수" />
                            @else
                                <x-ui::badge-info text="선택" />
                            @endif
                        </div>
                    </div>
                    <div class="sm:col-span-3">
                        <div class="text-sm font-medium text-gray-700 mb-1">버전</div>
                        <div class="text-sm text-gray-900">{{ $item->version }}</div>
                    </div>
                    <div class="sm:col-span-3">
                        <div class="text-sm font-medium text-gray-700 mb-1">활성화</div>
                        <div class="text-sm text-gray-900">
                            @if ($item->is_active)
                                <x-ui::badge-success text="활성" />
                            @else
                                <x-ui::badge-warning text="비활성" />
                            @endif
                        </div>
                    </div>
                    <div class="sm:col-span-3">
                        <div class="text-sm font-medium text-gray-700 mb-1">표시 순서</div>
                        <div class="text-sm text-gray-900">{{ $item->display_order }}</div>
                    </div>
                    <div class="sm:col-span-3">
                        <div class="text-sm font-medium text-gray-700 mb-1">시행일</div>
                        <div class="text-sm text-gray-900">{{ $item->effective_date ? $item->effective_date->format('Y-m-d') : '-' }}</div>
                    </div>
                    <div class="sm:col-span-3">
                        <div class="text-sm font-medium text-gray-700 mb-1">만료일</div>
                        <div class="text-sm text-gray-900">{{ $item->expiry_date ? $item->expiry_date->format('Y-m-d') : '-' }}</div>
                    </div>
                    <div class="sm:col-span-6">
                        <div class="text-sm font-medium text-gray-700 mb-1">설명</div>
                        <div class="text-sm text-gray-900">{{ $item->description ?: '-' }}</div>
                    </div>
                </div>
            </x-ui::form-section>

            <x-ui::form-section
                title="약관 내용"
                description="약관의 상세 내용입니다.">
                <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6 md:col-span-2">
                    <div class="sm:col-span-6">
                        <div class="text-sm font-medium text-gray-700 mb-1">약관 내용</div>
                        <div class="text-sm text-gray-900 whitespace-pre-wrap">{{ $item->content }}</div>
                    </div>
                </div>
            </x-ui::form-section>

            <x-ui::form-section
                title="시스템 정보"
                description="약관의 시스템 관리 정보입니다.">
                <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6 md:col-span-2">
                    <div class="sm:col-span-3">
                        <div class="text-sm font-medium text-gray-700 mb-1">생성일</div>
                        <div class="text-sm text-gray-900">{{ $item->created_at->format('Y-m-d H:i') }}</div>
                    </div>
                    <div class="sm:col-span-3">
                        <div class="text-sm font-medium text-gray-700 mb-1">수정일</div>
                        <div class="text-sm text-gray-900">{{ $item->updated_at->format('Y-m-d H:i') }}</div>
                    </div>
                </div>
            </x-ui::form-section>
        </div>
    </div>
@endsection
