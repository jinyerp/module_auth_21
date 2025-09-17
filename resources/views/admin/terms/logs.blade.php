@extends('jiny-auth::layouts.resource.table')
@section('title', '약관 로그 관리')
@section('description', '약관 동의 및 변경 이력을 관리합니다. 사용자별 약관 동의 현황과 변경 이력을 확인할 수 있습니다.')

{{-- 페이지 상태 스크립트 --}}
@section('page-script', 'table')

@section('heading')
    <section class="w-full">
        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
            <article class="flex-1 min-w-0">
                <h1 class="text-2xl font-semibold text-gray-900">약관 로그 관리</h1>
                <p class="mt-2 text-base text-gray-700 leading-relaxed">약관 동의 및 변경 이력을 관리합니다. 사용자별 약관 동의 현황과 변경 이력을 확인할 수 있습니다.</p>
            </article>
            <aside class="flex-shrink-0 flex gap-2">
                <x-ui::button-light href="{{ route($route . 'index') }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    약관 목록
                </x-ui::button-light>
            </aside>
        </div>
    </section>
@endsection

@section('content')
    @csrf {{-- ajax 통신을 위한 토큰 --}}

    {{-- 필터 컴포넌트 --}}
    <x-ui::layout-resource-filters>
        @includeIf('jiny-auth::admin.terms.log-filter')
    </x-ui::layout-resource-filters>

    <x-ui::table-stripe>
        <x-ui::table-thead>
            <x-ui::table-th sort="id">ID</x-ui::table-th>
            <x-ui::table-th sort="user_id">사용자ID</x-ui::table-th>
            <x-ui::table-th sort="term_id">약관ID</x-ui::table-th>
            <x-ui::table-th sort="agreed">동의여부</x-ui::table-th>
            <x-ui::table-th sort="agreed_at">동의시간</x-ui::table-th>
            <x-ui::table-th sort="ip_address">IP주소</x-ui::table-th>
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
                        {{ $item->id }}
                    </td>

                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">
                        <a href="{{ route($route . 'log-show', $item->id) }}" class="text-gray-500 hover:text-indigo-600">
                            {{ $item->user_id }}
                        </a>
                    </td>

                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">
                        {{ $item->term_id }}
                    </td>

                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">
                        @if ($item->agreed)
                            <x-ui::badge-success text="동의" />
                        @else
                            <x-ui::badge-warning text="미동의" />
                        @endif
                    </td>

                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">
                        {{ $item->agreed_at ? $item->agreed_at->format('Y-m-d H:i') : '-' }}
                    </td>

                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">
                        {{ $item->ip_address ?: '-' }}
                    </td>

                    <td class="px-3 py-4 text-sm whitespace-nowrap text-gray-500">
                        {{ $item->created_at ? $item->created_at->format('Y-m-d H:i') : '-' }}
                    </td>

                    <td class="relative py-4 pr-4 pl-3 text-right text-sm font-medium whitespace-nowrap sm:pr-3">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route($route . 'log-show', $item->id) }}"
                                class="text-indigo-600 hover:text-indigo-900 p-1 rounded-md hover:bg-indigo-50 transition-colors"
                                title="상세보기">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <span class="sr-only">View log {{ $item->id }}</span>
                            </a>
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
