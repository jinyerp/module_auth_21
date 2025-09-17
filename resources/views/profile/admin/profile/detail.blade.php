<div class="card mb-3">
    <div class="card-header">
        <h5 class="card-title mb-0">프로파일 상세</h5>
    </div>
    <div class="card-body text-center">
        <img src="/home/user/avatar/{{$user->id}}"
            alt="{{$user->name}}"
            class="img-fluid rounded-circle mb-2"
            width="128" height="128">
        <h5 class="card-title mb-0">{{$user->name}}</h5>
        <div class="text-muted mb-2">{{$user->email}}</div>

        <div>
            <a class="btn btn-primary btn-sm" href="javascript:void(0);">팀관리</a>
            <a class="btn btn-primary btn-sm" href="javascript:void(0);">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="message-square" class="lucide lucide-message-square">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                </svg>
                메시지
            </a>
        </div>
    </div>
    {{-- <hr class="my-0">
    <div class="card-body">
        <h5 class="h6 card-title">Skills</h5>
        <a href="#" class="badge badge-subtle-primary me-1 my-1">HTML</a>
        <a href="#" class="badge badge-subtle-primary me-1 my-1">JavaScript</a>
        <a href="#" class="badge badge-subtle-primary me-1 my-1">Sass</a>
        <a href="#" class="badge badge-subtle-primary me-1 my-1">Angular</a>
        <a href="#" class="badge badge-subtle-primary me-1 my-1">Vue</a>
        <a href="#" class="badge badge-subtle-primary me-1 my-1">React</a>
        <a href="#" class="badge badge-subtle-primary me-1 my-1">Redux</a>
        <a href="#" class="badge badge-subtle-primary me-1 my-1">UI</a>
        <a href="#" class="badge badge-subtle-primary me-1 my-1">UX</a>
    </div> --}}


    {{-- <hr class="my-0">
    <div class="card-body">
        <h5 class="h6 card-title">About</h5>
        <ul class="list-unstyled mb-0">
            <li class="mb-1"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="home" class="lucide lucide-home lucide-sm me-1"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg> Lives in <a href="#">San Francisco, SA</a></li>

            <li class="mb-1"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="briefcase" class="lucide lucide-briefcase lucide-sm me-1"><path d="M16 20V4a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path><rect width="20" height="14" x="2" y="6" rx="2"></rect></svg> Works at <a href="#">GitHub</a></li>
            <li class="mb-1"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="map-pin" class="lucide lucide-map-pin lucide-sm me-1"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"></path><circle cx="12" cy="10" r="3"></circle></svg> From <a href="#">Boston</a></li>
        </ul>
    </div> --}}

    <hr class="my-0">
    <div class="card-body">
        <h5 class="h6 card-title">다른 사이트</h5>
        <ul class="list-unstyled mb-0">
            <li class="mb-1">
                <span class="fas fa-globe fa-fw me-1"></span>
                <a href="javascript:void(0);">staciehall.co</a></li>
            <li class="mb-1">
                <span class="fab fa-twitter fa-fw me-1"></span>
                <a href="javascript:void(0);">Twitter</a></li>
            <li class="mb-1">
                <span class="fab fa-facebook fa-fw me-1"></span>
                <a href="javascript:void(0);">Facebook</a></li>
            <li class="mb-1">
                <span class="fab fa-instagram fa-fw me-1"></span>
                <a href="javascript:void(0);">Instagram</a></li>
            <li class="mb-1">
                <span class="fab fa-linkedin fa-fw me-1"></span>
                <a href="javascript:void(0);">LinkedIn</a></li>
        </ul>
    </div>
</div>
