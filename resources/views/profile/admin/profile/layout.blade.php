<x-admin>
    <h1 class="h3 mb-3">프로파일</h1>
    <div class="row">
        <div class="col-md-4 col-xl-3">
            @include('jiny-profile::admin.profile.detail')
        </div>

        <div class="col-md-8 col-xl-9">
            @include('jiny-profile::admin.profile.message')
        </div>
    </div>
</x-admin>
