@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto">
        {{-- 프로필 헤더 --}}
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex items-center space-x-6">
                <div class="relative">
                    @if($user->avatar)
                        <img src="{{ asset('storage/' . $user->avatar) }}" alt="Avatar" class="w-24 h-24 rounded-full object-cover">
                    @else
                        <div class="w-24 h-24 rounded-full bg-gray-300 flex items-center justify-center">
                            <span class="text-3xl text-gray-600">{{ substr($user->name, 0, 1) }}</span>
                        </div>
                    @endif
                    <a href="{{ route('home.profile.avatar') }}" class="absolute bottom-0 right-0 bg-blue-600 text-white p-2 rounded-full hover:bg-blue-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </a>
                </div>
                
                <div class="flex-1">
                    <h1 class="text-2xl font-bold text-gray-800">{{ $user->name }}</h1>
                    <p class="text-gray-600">{{ $user->email }}</p>
                    <div class="mt-2">
                        <div class="flex items-center space-x-4">
                            <span class="text-sm text-gray-500">가입일: {{ $user->created_at->format('Y년 m월 d일') }}</span>
                            <span class="text-sm text-gray-500">•</span>
                            <span class="text-sm text-gray-500">프로필 완성도: {{ $completeness }}%</span>
                        </div>
                    </div>
                </div>
                
                <div>
                    <a href="{{ route('home.profile.edit') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                        프로필 편집
                    </a>
                </div>
            </div>
            
            {{-- 프로필 완성도 바 --}}
            <div class="mt-6">
                <div class="bg-gray-200 rounded-full h-2">
                    <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $completeness }}%"></div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            {{-- 기본 정보 --}}
            <div class="md:col-span-2">
                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <h2 class="text-lg font-semibold mb-4">기본 정보</h2>
                    <dl class="space-y-3">
                        <div class="flex">
                            <dt class="w-32 text-gray-600">이름:</dt>
                            <dd class="font-medium">{{ $user->name }}</dd>
                        </div>
                        <div class="flex">
                            <dt class="w-32 text-gray-600">이메일:</dt>
                            <dd class="font-medium">{{ $user->email }}</dd>
                        </div>
                        <div class="flex">
                            <dt class="w-32 text-gray-600">전화번호:</dt>
                            <dd class="font-medium">{{ $user->phone ?? '-' }}</dd>
                        </div>
                        <div class="flex">
                            <dt class="w-32 text-gray-600">생년월일:</dt>
                            <dd class="font-medium">{{ $user->birthdate ? \Carbon\Carbon::parse($user->birthdate)->format('Y년 m월 d일') : '-' }}</dd>
                        </div>
                        <div class="flex">
                            <dt class="w-32 text-gray-600">성별:</dt>
                            <dd class="font-medium">
                                @if($user->gender == 'male') 남성
                                @elseif($user->gender == 'female') 여성
                                @elseif($user->gender == 'other') 기타
                                @else -
                                @endif
                            </dd>
                        </div>
                    </dl>
                </div>

                {{-- 주소록 --}}
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-lg font-semibold">주소록</h2>
                        <a href="{{ route('home.profile.addresses') }}" class="text-blue-600 hover:text-blue-800">
                            관리
                        </a>
                    </div>
                    @if($addresses->count() > 0)
                        <div class="space-y-3">
                            @foreach($addresses->take(2) as $address)
                            <div class="border rounded-lg p-3">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <span class="inline-block px-2 py-1 text-xs bg-gray-100 rounded mb-1">
                                            {{ ucfirst($address->type) }}
                                        </span>
                                        @if($address->is_default)
                                            <span class="inline-block px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded mb-1">기본</span>
                                        @endif
                                        <p class="text-sm">{{ $address->address_line_1 }}</p>
                                        @if($address->address_line_2)
                                            <p class="text-sm">{{ $address->address_line_2 }}</p>
                                        @endif
                                        <p class="text-sm">{{ $address->city }}, {{ $address->state }} {{ $address->postal_code }}</p>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500">등록된 주소가 없습니다.</p>
                    @endif
                </div>
            </div>

            {{-- 사이드바 --}}
            <div class="space-y-6">
                {{-- 보안 설정 --}}
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-lg font-semibold mb-4">보안 설정</h2>
                    <div class="space-y-3">
                        <a href="{{ route('home.account.password') }}" class="flex items-center justify-between p-2 hover:bg-gray-50 rounded">
                            <span>비밀번호 변경</span>
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>
                        <a href="{{ route('home.profile.security') }}" class="flex items-center justify-between p-2 hover:bg-gray-50 rounded">
                            <span>2단계 인증</span>
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>
                        <a href="{{ route('home.account.sessions') }}" class="flex items-center justify-between p-2 hover:bg-gray-50 rounded">
                            <span>세션 관리</span>
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>
                    </div>
                </div>

                {{-- 소셜 계정 --}}
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-lg font-semibold">소셜 계정</h2>
                        <a href="{{ route('home.profile.social') }}" class="text-blue-600 hover:text-blue-800">
                            관리
                        </a>
                    </div>
                    @if($socialAccounts->count() > 0)
                        <div class="space-y-2">
                            @foreach($socialAccounts as $account)
                            <div class="flex items-center space-x-2">
                                <img src="/images/social/{{ $account->provider }}.png" alt="{{ $account->provider }}" class="w-6 h-6">
                                <span class="text-sm">{{ ucfirst($account->provider) }}</span>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-500">연결된 소셜 계정이 없습니다.</p>
                    @endif
                </div>

                {{-- 최근 활동 --}}
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-lg font-semibold mb-4">최근 활동</h2>
                    @if($recentActivities->count() > 0)
                        <div class="space-y-2">
                            @foreach($recentActivities as $activity)
                            <div class="text-sm">
                                <p class="text-gray-800">{{ $activity->description }}</p>
                                <p class="text-gray-500">{{ \Carbon\Carbon::parse($activity->created_at)->diffForHumans() }}</p>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-500">최근 활동이 없습니다.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection