<div>
    @if(Session::has('message'))
        <div class="mb-4 text-sm font-medium text-red-600">{{Session::get('message')}}</div>
    @endif

    <form class="space-y-6" onsubmit="return false;">
        <div class="space-y-1">
            <label class="font-medium" for="password">Current Password</label>
            <input wire:model.defer="form.current"
            class="block border placeholder-gray-400 px-3 py-2 leading-6 w-full rounded border-gray-200 focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50" type="password" id="password" name="password">
        </div>
        <div class="space-y-1">
            <label class="font-medium" for="password_new">New Password</label>
            <input wire:model.defer="form.password"
            class="block border placeholder-gray-400 px-3 py-2 leading-6 w-full rounded border-gray-200 focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50" type="password" id="password_new" name="password_new">
        </div>
        <div class="space-y-1">
            <label class="font-medium" for="password_new_confirm">Confirm Password</label>
            <input wire:model.defer="form.confirm"
            class="block border placeholder-gray-400 px-3 py-2 leading-6 w-full rounded border-gray-200 focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50" type="password" id="password_new_confirm" name="password_new_confirm">
        </div>

        <button type="submit" wire:click="update"
        class="inline-flex justify-center items-center space-x-2 border font-semibold focus:outline-none px-3 py-2 leading-5 text-sm rounded border-indigo-700 bg-indigo-700 text-white hover:text-white hover:bg-indigo-800 hover:border-indigo-800 focus:ring focus:ring-indigo-500 focus:ring-opacity-50 active:bg-indigo-700 active:border-indigo-700">
            Update Password
        </button>
    </form>
</div>
