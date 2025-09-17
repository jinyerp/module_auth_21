<x-account-layout>


    <!-- -->
    <div class="mb-4">
        <h1 class="mb-0 h3">
            {{account()->name}}님은 {{account()->utype}} 회원 입니다.
        </h1>
        <p class="mb-0 fs-6">최근 접속일은 xxx 입니다.</p>
    </div>

    {{--
    <div class="mb-5">
        <h4 class="mb-1">Earning</h4>
        <p class="mb-0 fs-6">Upload a picture to make your profile stand out and let people recognize your comments and contributions easily!</p>
    </div>
    <div class="row mb-5 g-4">
        <div class="col-lg-4 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <span>Balance</span>
                    <h3 class="mb-0 mt-4">$0.61</h3>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <span>Past 7 days</span>
                    <h3 class="mb-0 mt-4">$0</h3>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <span>Total Earnings</span>
                    <h3 class="mb-0 mt-4">$0.61</h3>
                </div>
            </div>
        </div>
    </div>
    --}}

    {{--
    <div class="mb-3">
        <h4 class="mb-0">More ways to earn</h4>
    </div>
    <div class="row g-4">
        <div class="col-lg-4 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-person-vcard text-warning" viewBox="0 0 16 16">
                            <path
                                d="M5 8a2 2 0 1 0 0-4 2 2 0 0 0 0 4Zm4-2.5a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 0 1h-4a.5.5 0 0 1-.5-.5ZM9 8a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 0 1h-4A.5.5 0 0 1 9 8Zm1 2.5a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 0 1h-3a.5.5 0 0 1-.5-.5Z" />
                            <path
                                d="M2 2a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H2ZM1 4a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v8a1 1 0 0 1-1 1H8.96c.026-.163.04-.33.04-.5C9 10.567 7.21 9 5 9c-2.086 0-3.8 1.398-3.984 3.181A1.006 1.006 0 0 1 1 12V4Z" />
                        </svg>
                    </div>
                    <div class="mb-4">
                        <h5 class="mb-2">Enable Memberships</h5>
                        <p class="mb-0 pe-xl-7">Monthly membership for your biggest fans.</p>
                    </div>

                    <a href="#" class="icon-link icon-link-hover text-inherit">
                        Set up Now
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-arrow-right" viewBox="0 0 16 16">
                            <path
                                fill-rule="evenodd"
                                d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8z"></path>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-file-earmark-lock text-danger" viewBox="0 0 16 16">
                            <path
                                d="M10 7v1.076c.54.166 1 .597 1 1.224v2.4c0 .816-.781 1.3-1.5 1.3h-3c-.719 0-1.5-.484-1.5-1.3V9.3c0-.627.46-1.058 1-1.224V7a2 2 0 1 1 4 0zM7 7v1h2V7a1 1 0 0 0-2 0zM6 9.3v2.4c0 .042.02.107.105.175A.637.637 0 0 0 6.5 12h3a.64.64 0 0 0 .395-.125c.085-.068.105-.133.105-.175V9.3c0-.042-.02-.107-.105-.175A.637.637 0 0 0 9.5 9h-3a.637.637 0 0 0-.395.125C6.02 9.193 6 9.258 6 9.3z" />
                            <path
                                d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2zM9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5v2z" />
                        </svg>
                    </div>
                    <div class="mb-4">
                        <h5 class="mb-2">Locked posts</h5>
                        <p class="mb-0">Publish your best content exclusively for your supporters and members.</p>
                    </div>

                    <a href="#" class="icon-link icon-link-hover text-inherit">
                        Start Write Post
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-arrow-right" viewBox="0 0 16 16">
                            <path
                                fill-rule="evenodd"
                                d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8z"></path>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-gift-fill text-info" viewBox="0 0 16 16">
                            <path
                                d="M3 2.5a2.5 2.5 0 0 1 5 0 2.5 2.5 0 0 1 5 0v.006c0 .07 0 .27-.038.494H15a1 1 0 0 1 1 1v1a1 1 0 0 1-1 1H1a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h2.038A2.968 2.968 0 0 1 3 2.506V2.5zm1.068.5H7v-.5a1.5 1.5 0 1 0-3 0c0 .085.002.274.045.43a.522.522 0 0 0 .023.07zM9 3h2.932a.56.56 0 0 0 .023-.07c.043-.156.045-.345.045-.43a1.5 1.5 0 0 0-3 0V3zm6 4v7.5a1.5 1.5 0 0 1-1.5 1.5H9V7h6zM2.5 16A1.5 1.5 0 0 1 1 14.5V7h6v9H2.5z" />
                        </svg>
                    </div>
                    <div class="mb-4">
                        <h5 class="mb-2">Sell Extras</h5>
                        <p class="mb-0">Introducing Extras, the creative way to sell.</p>
                    </div>

                    <a href="#" class="icon-link icon-link-hover text-inherit">
                        Set up Now
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-arrow-right" viewBox="0 0 16 16">
                            <path
                                fill-rule="evenodd"
                                d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8z"></path>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
    --}}

</x-account-layout>
