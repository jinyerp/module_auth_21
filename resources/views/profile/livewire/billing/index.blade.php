@extends('mypage::layouts.master')

@section('content')
<!-- Billing Information -->
<div class="flex flex-col rounded shadow-sm bg-white overflow-hidden">
        <div class="p-5 lg:p-6 grow w-full">
            <div class="md:flex">
            <div class="md:flex-none md:w-1/3 border-b md:border-0 mb-5 md:mb-0">
                <h3 class="flex items-center space-x-2 font-semibold mb-2">
                <svg class="hi-solid hi-credit-card inline-block w-5 h-5 text-indigo-500" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"></path><path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd"></path></svg>
                <span>Billing Information</span>
                </h3>
                <p class="text-gray-500 text-sm mb-5">
                Your billing information is never shown to other users and only used for creating your invoices.
                </p>
            </div>
            <div class="md:w-2/3 md:pl-24">
                <form class="space-y-6" onsubmit="return false;">
                <div class="space-y-1">
                    <label class="font-medium" for="billing_company">Company Name (Optional)</label>
                    <input class="block border placeholder-gray-400 px-3 py-2 leading-6 w-full rounded border-gray-200 focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50" type="text" id="billing_company" name="billing_company">
                </div>
                <div class="space-y-6 sm:space-y-0 sm:flex sm:space-x-3">
                    <div class="space-y-1 grow">
                    <label class="font-medium" for="billing_firstname">Firstname</label>
                    <input class="block border placeholder-gray-400 px-3 py-2 leading-6 w-full rounded border-gray-200 focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50" type="text" id="billing_firstname" name="billing_firstname">
                    </div>
                    <div class="space-y-1 grow">
                    <label class="font-medium" for="billing_lastname">Lastname</label>
                    <input class="block border placeholder-gray-400 px-3 py-2 leading-6 w-full rounded border-gray-200 focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50" type="text" id="billing_lastname" name="billing_lastname">
                    </div>
                </div>
                <div class="space-y-1">
                    <label class="font-medium" for="billing_address1">Street Address 1</label>
                    <input class="block border placeholder-gray-400 px-3 py-2 leading-6 w-full rounded border-gray-200 focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50" type="text" id="billing_address1" name="billing_address1">
                </div>
                <div class="space-y-1">
                    <label class="font-medium" for="billing_address2">Street Address 2</label>
                    <input class="block border placeholder-gray-400 px-3 py-2 leading-6 w-full rounded border-gray-200 focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50" type="text" id="billing_address2" name="billing_address2">
                </div>
                <div class="space-y-6 sm:space-y-0 sm:flex sm:space-x-3">
                    <div class="space-y-1 sm:w-2/3">
                    <label class="font-medium" for="billing_city">City</label>
                    <input class="block border placeholder-gray-400 px-3 py-2 leading-6 w-full rounded border-gray-200 focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50" type="text" id="billing_city" name="billing_city">
                    </div>
                    <div class="space-y-1 sm:w-1/3">
                    <label class="font-medium" for="billing_postal">Postal</label>
                    <input class="block border placeholder-gray-400 px-3 py-2 leading-6 w-full rounded border-gray-200 focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50" type="text" id="billing_postal" name="billing_postal">
                    </div>
                </div>
                <div class="space-y-1">
                    <label class="font-medium" for="billing_vat">VAT Number</label>
                    <input class="block border placeholder-gray-400 px-3 py-2 leading-6 w-full rounded border-gray-200 focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50 bg-gray-100 cursor-not-allowed" type="text" id="billing_vat" name="billing_vat" disabled>
                </div>
                <button type="submit" class="inline-flex justify-center items-center space-x-2 border font-semibold focus:outline-none px-3 py-2 leading-5 text-sm rounded border-indigo-700 bg-indigo-700 text-white hover:text-white hover:bg-indigo-800 hover:border-indigo-800 focus:ring focus:ring-indigo-500 focus:ring-opacity-50 active:bg-indigo-700 active:border-indigo-700">
                    Save Changes
                </button>
                </form>
            </div>
            </div>
        </div>
        </div>
        <!-- END Billing Information -->
@endsection
