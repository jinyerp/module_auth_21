@extends('mypage::layouts.master')

@section('content')
<!-- Notifications -->
<div class="flex flex-col rounded shadow-sm bg-white overflow-hidden">
    <div class="p-5 lg:p-6 grow w-full">
        <div class="md:flex">
        <div class="md:flex-none md:w-1/3 border-b md:border-0 mb-5 md:mb-0">
            <h3 class="flex items-center space-x-2 font-semibold mb-2">
            <svg class="hi-solid hi-speakerphone inline-block w-5 h-5 text-indigo-500" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M18 3a1 1 0 00-1.447-.894L8.763 6H5a3 3 0 000 6h.28l1.771 5.316A1 1 0 008 18h1a1 1 0 001-1v-4.382l6.553 3.276A1 1 0 0018 15V3z" clip-rule="evenodd"></path></svg>
            <span>Notifications</span>
            </h3>
            <p class="text-gray-500 text-sm mb-5">
            You have complete control over the notifications you receive from us.
            </p>
        </div>
        <div class="md:w-2/3 md:pl-24">
            <form class="space-y-6" onsubmit="return false;">
            <div class="space-y-2">
                <div class="font-medium">Email Preferences</div>
                <div class="space-y-1">
                <label class="flex items-center">
                    <input type="checkbox" class="border border-gray-300 rounded h-4 w-4 text-indigo-500 focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50" id="email1" name="email1">
                    <span class="ml-2">Account related</span>
                </label>
                <label class="flex items-center">
                    <input type="checkbox" class="border border-gray-300 rounded h-4 w-4 text-indigo-500 focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50" id="email2" name="email2">
                    <span class="ml-2">Product announcements</span>
                </label>
                <label class="flex items-center">
                    <input type="checkbox" class="border border-gray-300 rounded h-4 w-4 text-indigo-500 focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50" id="email3" name="email3">
                    <span class="ml-2">News and promotions</span>
                </label>
                </div>
            </div>
            <div class="space-y-2">
                <div class="font-medium">Push Notifications</div>
                <div class="space-y-1">
                <label class="inline-flex items-center">
                    <input type="radio" class="border border-gray-300 h-4 w-4 text-indigo-500 focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50" name="push" id="push1"> <span class="ml-2">Enable</span>
                </label>
                <label class="inline-flex items-center ml-6">
                    <input type="radio" class="border border-gray-300 h-4 w-4 text-indigo-500 focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50" name="push" id="push2"> <span class="ml-2">Disable</span>
                </label>
                </div>
            </div>
            <button type="submit" class="inline-flex justify-center items-center space-x-2 border font-semibold focus:outline-none px-3 py-2 leading-5 text-sm rounded border-indigo-700 bg-indigo-700 text-white hover:text-white hover:bg-indigo-800 hover:border-indigo-800 focus:ring focus:ring-indigo-500 focus:ring-opacity-50 active:bg-indigo-700 active:border-indigo-700">
                Update Preferences
            </button>
            </form>
        </div>
        </div>
    </div>
    </div>
    <!-- END Notifications -->
@endsection
