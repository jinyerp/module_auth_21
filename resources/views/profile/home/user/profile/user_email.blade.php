<div>
    <x-loading-indicator/>

    <div class="row needs-validation">
        <div class="col-lg-7">

            <div class="mb-3">
                <label for="profile-name" class="form-label">이름</label>
                <input type="text" class="form-control"
                    id="profile-name" required
                    wire:model.defer="form.name"/>
            </div>

            <div class="mb-3">
                <label for="profile-email" class="form-label">새로운 이메일 주소</label>
                <input type="email" class="form-control"
                    id="profile-email"
                    placeholder="userid@example.com" required
                    wire:model.defer="form.email"/>
                <div class="invalid-feedback">이메일 주소를 입력해 주세요.</div>
            </div>
            <div>
                <button type="submit" class="btn btn-primary" wire:click="submit">변경하기</button>
            </div>
        </div>
    </div>
</div>
