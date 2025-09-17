@extends('mypage::layouts.master')

@section('content')
<!-- Change Password -->
<div class="flex flex-col rounded shadow-sm bg-white overflow-hidden">
    <div class="p-5 lg:p-6 grow w-full">
        <div class="md:flex">
        <div class="md:flex-none md:w-1/3 border-b md:border-0 mb-5 md:mb-0">
            <h3 class="flex items-center space-x-2 font-semibold mb-2">
            <svg class="hi-solid hi-lock-open inline-block w-5 h-5 text-indigo-500" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M10 2a5 5 0 00-5 5v2a2 2 0 00-2 2v5a2 2 0 002 2h10a2 2 0 002-2v-5a2 2 0 00-2-2H7V7a3 3 0 015.905-.75 1 1 0 001.937-.5A5.002 5.002 0 0010 2z"></path></svg>
            <span>Change Password</span>
            </h3>
            <p class="text-gray-500 text-sm mb-5">
            Changing your sign in password is an easy way to keep your account secure.
            </p>
        </div>
        <div class="md:w-2/3 md:pl-24">
            @livewire('mypage-password')
        </div>
        </div>
    </div>
    </div>
    <!-- END Change Password -->
@endsection
