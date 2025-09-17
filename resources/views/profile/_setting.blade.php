<x-account-layout>
    <div class="mb-4">
        <div class="mb-5">
            <h1 class="mb-0 h3">
                Custom Setting
            </h1>
            <p class="mb-0 fs-6">{{account()->name}}님은 {{account()->utype}} 회원 입니다.</p>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-lg-5">

                <div class="mb-5">
                    <h4 class="mb-1">리다이렉션</h4>
                    <p class="mb-0 fs-6">회원 로그인후 이동될 주소를 지정합니다.</p>
                </div>

                <div class="mb-3">
                    <label class="form-label">로그인후 동작</label>
                    <input name="text" type="text" class="form-control" value="">
                </div>

            </div>
        </div>





    </div>
</x-account-layout>
