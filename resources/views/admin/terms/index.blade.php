@extends('jiny-auth::layouts.resource.table')
@section('title', '약관 관리')
@section('description', '회원 인증 시스템에서 제공하는 약관을 관리합니다. 약관명, 타입, 버전, 활성화 여부 등을 관리할 수 있습니다.')

{{-- 페이지 상태 스크립트 --}}
@section('page-script', 'table')

@section('heading')
    <section class="w-full">
        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
            <article class="flex-1 min-w-0">
                <h1 class="text-2xl font-semibold text-gray-900">약관 관리</h1>
                <p class="mt-2 text-base text-gray-700 leading-relaxed">회원 인증 시스템에서 제공하는 약관을 관리합니다. 약관명, 타입, 버전, 활성화 여부 등을 관리할 수 있습니다.</p>
            </article>
            <aside class="flex-shrink-0 flex gap-2">
                <x-ui::button-primary href="{{ route($route . 'create') }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    약관등록
                </x-ui::button-primary>
            </aside>
        </div>
    </section>
@endsection

@section('content')
    @csrf {{-- ajax 통신을 위한 토큰 --}}

    {{-- 필터 컴포넌트 --}}
    <x-ui::layout-resource-filters>
        @includeIf('jiny-auth::admin.terms.filter')
    </x-ui::layout-resource-filters>

    <x-ui::table-stripe>
        <x-ui::table-thead>
            <x-ui::table-th sort="title">약관명</x-ui::table-th>
            <x-ui::table-th sort="type">타입</x-ui::table-th>
            <x-ui::table-th sort="version">버전</x-ui::table-th>
            <x-ui::table-th sort="is_active">활성화</x-ui::table-th>
            <x-ui::table-th sort="display_order">표시순서</x-ui::table-th>
            <x-ui::table-th sort="effective_date">시행일</x-ui::table-th>
            <x-ui::table-th sort="expiry_date">만료일</x-ui::table-th>
            <x-ui::table-th sort="created_at">생성일</x-ui::table-th>
            <th class="relative py-3.5 pr-4 pl-3 sm:pr-3 text-center">
                Actions
            </th>
        </x-ui::table-thead>

        <tbody class="bg-white">
            @foreach ($rows as $item)
                <x-ui::table-row :item="$item" data-row-id="{{ $item->id }}"
                    data-even="{{ $loop->even ? '1' : '0' }}">

                    <td class="py-4 pr-3 pl-4 text-sm font-medium whitespace-nowrap text-gray-900 sm:pl-3">
                        <a href="{{ route($route . 'show', $item->id) }}" class="text-gray-500 hover:text-indigo-600">
                            {{ $item->title }}
                        </a>
                    </td>

                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">
                        @if ($item->type === 'required')
                            <x-ui::badge-danger text="필수" />
                        @else
                            <x-ui::badge-info text="선택" />
                        @endif
                    </td>

                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">
                        {{ $item->version }}
                    </td>

                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">
                        @if ($item->is_active)
                            <x-ui::badge-success text="활성" />
                        @else
                            <x-ui::badge-warning text="비활성" />
                        @endif
                    </td>

                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">
                        {{ $item->display_order }}
                    </td>

                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">
                        {{ $item->effective_date ? \Carbon\Carbon::parse($item->effective_date)->format('Y-m-d') : '-' }}
                    </td>

                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">
                        {{ $item->expiry_date ? \Carbon\Carbon::parse($item->expiry_date)->format('Y-m-d') : '-' }}
                    </td>

                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">
                        {{ $item->created_at ? $item->created_at->format('Y-m-d') : '-' }}
                    </td>

                    <td class="relative py-4 pr-4 pl-3 text-right text-sm font-medium whitespace-nowrap sm:pr-3">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route($route . 'show', $item->id) }}"
                                class="text-indigo-600 hover:text-indigo-900 p-1 rounded-md hover:bg-indigo-50 transition-colors"
                                title="상세보기">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <span class="sr-only">View {{ $item->title }}</span>
                            </a>
                            <a href="{{ route($route . 'edit', $item->id) }}"
                                class="text-indigo-600 hover:text-indigo-900 p-1 rounded-md hover:bg-indigo-50 transition-colors"
                                title="수정">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                <span class="sr-only">Edit {{ $item->title }}</span>
                            </a>
                            <button type="button"
                                class="text-red-600 hover:text-red-900 p-1 rounded-md hover:bg-red-50 transition-colors cursor-pointer"
                                data-delete-route="{{ route($route . 'destroy', $item->id) }}"
                                data-item-title="{{ $item->title }}"
                                title="삭제">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                <span class="sr-only">Delete {{ $item->title }}</span>
                            </button>
                        </div>
                    </td>
                </x-ui::table-row>
            @endforeach
        </tbody>
    </x-ui::table-stripe>

    {{-- 페이지네이션 --}}
    @includeIf('jiny-auth::layouts.resource.pagenation')

    {{-- 디버그 모드 --}}
    @if(config('app.debug'))
        @includeIf('jiny-admin::layouts.crud.debug')
    @endif

@endsection
