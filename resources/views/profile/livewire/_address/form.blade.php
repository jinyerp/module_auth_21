<div class="row g-3 needs-validation">

    <div class="col-lg-3">
        <label for="profileCountryInput" class="form-label">국가</label>
        <select class="form-select" id="profileCountryInput" required
        wire:model.defer="form.country">
            <option selected disabled value="">Choose...</option>
            <option selected value="">India</option>
            <option value="Australia">Australia</option>
            <option value="Canada">Canada</option>
            <option value="Germany">Germany</option>
        </select>
        <div class="invalid-feedback">Please select state.</div>
    </div>

    <div class="col-lg-3">
        <label for="profileStateInput" class="form-label">State / Region</label>
        <select class="form-select" id="profileStateInput" required
            wire:model.defer="form.region">
            <option selected disabled value="">Choose...</option>
            <option selected value="">Gujarat</option>
            <option value="Rajasthan">Rajasthan</option>
            <option value="Goa">Goa</option>
            <option value="Maharashtra">Maharashtra</option>
        </select>
        <div class="invalid-feedback">Please select state / region.</div>
    </div>

    <div class="col-lg-3">
        <label for="profileCityInput" class="form-label">State</label>
        <select class="form-select" id="profileCityInput" required
        wire:model.defer="form.state">
            <option selected disabled value="">Choose...</option>
            <option value="Ahmedabad" selected="">Ahmedabad</option>
            <option value="Surat">Surat</option>
            <option value="Vapi">Vapi</option>
            <option value="Rajkot">Rajkot</option>
        </select>
        <div class="invalid-feedback">Please select a valid city.</div>
    </div>

    <div class="col-lg-3">
        <label for="profilezipInput" class="form-label">Zip/Code</label>
        <input type="text" class="form-control" id="profilezipInput" required
        wire:model.defer="form.zipcode"/>
        <div class="invalid-feedback">Please provide a zip.</div>
    </div>

    <div class="col-lg-12">
        <label for="profileAddressInput" class="form-label">주소</label>
        <input type="text" class="form-control" id="profileAddressInput" required
        wire:model.defer="form.address1"/>
        <div class="invalid-feedback">Please enter addredss.</div>
    </div>

    <div class="col-lg-12">
        <label for="profileAddressInput2" class="form-label">상세</label>
        <input type="text" class="form-control" id="profileAddressInput2" required
        wire:model.defer="form.address2"/>
        <div class="invalid-feedback">Please enter addredss.</div>
    </div>



</div>
