<div>
    <x-loading-indicator/>

    @if(Session::has('message'))
        <div class="mb-4 text-sm font-medium text-red-600">{{Session::get('message')}}</div>
    @endif

    <div class="row gy-3 needs-validation" >
        <div class="col-lg-7">
            <label for="securityOldPasswordInput" class="form-label">Old Password</label>
            <input type="password" class="form-control" id="securityOldPasswordInput" required
            wire:model.defer="form.current"/>
            <div class="invalid-feedback">Please enter old password.</div>
        </div>

        <div class="col-lg-7">
            <label for="securityNewPasswordInput" class="form-label">New Password</label>
            <input type="password" class="form-control" id="securityNewPasswordInput" required
            wire:model.defer="form.password"/>
            <div class="invalid-feedback">Please enter new password.</div>
            <div class="form-text">
                숫자와 소문자를 포함하여 15자 이상 또는 8자 이상이어야 합니다.
            </div>
            <div class="form-text">
                더 알아보기.
            </div>
        </div>

        <div class="col-lg-7">
            <label for="securityConfirmPasswordInput" class="form-label">Confirm New Password</label>
            <input type="password" class="form-control" id="securityConfirmPasswordInput" required
            wire:model.defer="form.confirm"/>
            <div class="invalid-feedback">Please enter confirm password.</div>
            <div class="form-text">Make sure match with above new password</div>
        </div>
        <div class="col-12">
            <button class="btn btn-primary me-2" type="submit" wire:click="update">변경하기</button>

        </div>
    </div>
</div>
