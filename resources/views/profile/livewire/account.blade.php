<div>
    <div class="row g-3 needs-validation">

        <div class="col-lg-6 col-md-12">
            <label for="profileFirstNameInput" class="form-label">이름</label>
            <input type="text" class="form-control" id="profileFirstNameInput"
                wire:model.defer="form.lastname" required />
            <div class="invalid-feedback">Please enter firstname.</div>
        </div>

        <div class="col-lg-6 col-md-12">
            <label for="profileLastNameInput" class="form-label">성</label>
            <input type="text" class="form-control" id="profileLastNameInput"
            wire:model.defer="form.firstname" required />
            <div class="invalid-feedback">Please enter lastname.</div>
        </div>

        <div class="col-lg-6">
            <label for="profileBirthdayInput" class="form-label">생년월일</label>
            <input type="text" class="form-control input-date" id="profileBirthdayInput"
                placeholder="yyyy/mm/dd"
                wire:model.defer="form.birth"/>
            <div class="invalid-feedback">Please enter birthday.</div>
        </div>

        <div class="col-lg-6">

            {{--
            <label for="profilePhoneInput" class="form-label">전화번호</label>
            <input type="text" class="form-control input-phone" id="profilePhoneInput"
                placeholder="+82 010 XXX XXXX" required
                />
            <div class="invalid-feedback">Please enter phone.</div>
            --}}

        </div>


        <div class="col-12 mt-4">
            <button class="btn btn-primary me-2" wire:click="submit">변경</button>
            <button class="btn btn-light" type="cancel">취소</button>
        </div>
    </div>


</div>

