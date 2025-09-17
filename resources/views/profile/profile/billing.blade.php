<x-www-layout>
    <main>
        <!--Billing start-->
        <section class="py-lg-7 py-5 bg-light-subtle">
            <div class="container">
                <div class="row">
                    <div class="col-lg-3 col-md-4">
                        @include("www::mypage.partials.info")
                        @include("www::mypage.partials.sidemenu")
                    </div>
                    <div class="col-lg-9 col-md-8">
                        <div class="mb-4">
                            <h1 class="mb-0 h3">Billing</h1>
                        </div>

                        <div class="card border-0 mb-4 shadow-sm">
                            <div class="card-body p-lg-5">
                                <div class="mb-6 d-lg-flex align-items-center justify-content-between">
                                    <h4 class="mb-0">Current plan</h4>
                                    <div class="mt-3 mt-lg-0 d-md-flex">
                                        <a href="#" class="btn btn-light me-2">Cancel plan</a>
                                        <a href="#" class="btn btn-outline-primary">
                                            Upgrade plan
                                            <span class="ms-2">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14"
                                                    fill="currentColor" class="bi bi-arrow-up-right-circle-fill"
                                                    viewBox="0 0 16 16">
                                                    <path
                                                        d="M0 8a8 8 0 1 0 16 0A8 8 0 0 0 0 8zm5.904 2.803a.5.5 0 1 1-.707-.707L9.293 6H6.525a.5.5 0 1 1 0-1H10.5a.5.5 0 0 1 .5.5v3.975a.5.5 0 0 1-1 0V6.707l-4.096 4.096z" />
                                                </svg>
                                            </span>
                                        </a>
                                    </div>
                                </div>
                                <div class="row gx-4">
                                    <div class="col-lg-6">
                                        <div class="card border-0 mb-4 mb-lg-0 bg-light-subtle">
                                            <div class="card-body py-lg-3 px-lg-4">
                                                <div class="mb-5">
                                                    <h6>Current monthly bill</h6>
                                                    <h4>$19.00</h4>
                                                </div>

                                                <a href="#" class="icon-link icon-link-hover text-primary">
                                                    Switch to yearly billing
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14"
                                                        height="14" fill="currentColor" class="bi bi-arrow-right"
                                                        viewBox="0 0 16 16">
                                                        <path fill-rule="evenodd"
                                                            d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8z">
                                                        </path>
                                                    </svg>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="card border-0 mb-4 mb-lg-0 bg-light-subtle">
                                            <div class="card-body py-lg-3 px-lg-4">
                                                <div class="mb-5">
                                                    <h6>Next payment due</h6>
                                                    <h4>30 Jan, 2024</h4>
                                                </div>

                                                <a href="#" class="icon-link icon-link-hover text-primary">
                                                    View payment history
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14"
                                                        height="14" fill="currentColor" class="bi bi-arrow-right"
                                                        viewBox="0 0 16 16">
                                                        <path fill-rule="evenodd"
                                                            d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8z">
                                                        </path>
                                                    </svg>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card border-0 mb-4 shadow-sm">
                            <div class="card-body p-lg-5">
                                <div class="mb-5">
                                    <h4 class="mb-1">Payment Methods</h4>
                                    <p class="fs-6 mb-0">The block theme notification system notifies you of important
                                        events such as replies, mentions, updates, etc.</p>
                                </div>
                                <div class="border py-3 px-4 rounded-3 mb-3">
                                    <div class="d-flex justify-content-between">
                                        <div class="d-flex">
                                            <img src="/assets/images/landings/account/visa.svg" alt="card"
                                                class="me-3" />
                                            <div>
                                                <h6 class="mb-0">Visa ending in 1234</h6>
                                                <small>Expires in 03/2025</small>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <span class="dropdown dropstart">
                                                <a class="text-reset" href="#" role="button"
                                                    id="paymentDropdown1" data-bs-toggle="dropdown"
                                                    data-bs-offset="-20,20" aria-expanded="false">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14"
                                                        height="14" fill="currentColor"
                                                        class="bi bi-three-dots-vertical" viewBox="0 0 16 16">
                                                        <path
                                                            d="M9.5 13a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0zm0-5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0zm0-5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0z" />
                                                    </svg>
                                                </a>
                                                <span class="dropdown-menu" aria-labelledby="paymentDropdown1">
                                                    <span class="dropdown-header">Setting</span>
                                                    <a class="dropdown-item" href="#">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="14"
                                                            height="14" fill="currentColor"
                                                            class="bi bi-pencil-square" viewBox="0 0 16 16">
                                                            <path
                                                                d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z" />
                                                            <path fill-rule="evenodd"
                                                                d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5v11z" />
                                                        </svg>
                                                        <span class="ms-2">Edit</span>
                                                    </a>
                                                    <a class="dropdown-item" href="#">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="14"
                                                            height="14" fill="currentColor" class="bi bi-trash"
                                                            viewBox="0 0 16 16">
                                                            <path
                                                                d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5Zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5Zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6Z" />
                                                            <path
                                                                d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1ZM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118ZM2.5 3h11V2h-11v1Z" />
                                                        </svg>
                                                        <span class="ms-2">Remove</span>
                                                    </a>
                                                    <a class="dropdown-item" href="#">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="14"
                                                            height="14" fill="currentColor"
                                                            class="bi bi-credit-card" viewBox="0 0 16 16">
                                                            <path
                                                                d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V4zm2-1a1 1 0 0 0-1 1v1h14V4a1 1 0 0 0-1-1H2zm13 4H1v5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V7z" />
                                                            <path
                                                                d="M2 10a1 1 0 0 1 1-1h1a1 1 0 0 1 1 1v1a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1v-1z" />
                                                        </svg>
                                                        <span class="ms-2">Make it Primary</span>
                                                    </a>
                                                </span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="border py-3 px-4 rounded-3">
                                    <div class="d-flex justify-content-between">
                                        <div class="d-flex">
                                            <img src="/assets/images/landings/account/mastercard.svg" alt="card"
                                                class="me-3" />
                                            <div>
                                                <h6 class="mb-0">Master ending in 1234</h6>
                                                <small>Expires in 03/2026</small>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <span class="dropdown dropstart">
                                                <a class="text-reset" href="#" role="button"
                                                    id="paymentDropdown2" data-bs-toggle="dropdown"
                                                    data-bs-offset="-20,20" aria-expanded="false">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14"
                                                        height="14" fill="currentColor"
                                                        class="bi bi-three-dots-vertical" viewBox="0 0 16 16">
                                                        <path
                                                            d="M9.5 13a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0zm0-5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0zm0-5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0z" />
                                                    </svg>
                                                </a>
                                                <span class="dropdown-menu" aria-labelledby="paymentDropdown2">
                                                    <span class="dropdown-header">Setting</span>
                                                    <a class="dropdown-item" href="#">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="14"
                                                            height="14" fill="currentColor"
                                                            class="bi bi-pencil-square" viewBox="0 0 16 16">
                                                            <path
                                                                d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z" />
                                                            <path fill-rule="evenodd"
                                                                d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5v11z" />
                                                        </svg>
                                                        <span class="ms-2">Edit</span>
                                                    </a>
                                                    <a class="dropdown-item" href="#">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="14"
                                                            height="14" fill="currentColor" class="bi bi-trash"
                                                            viewBox="0 0 16 16">
                                                            <path
                                                                d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5Zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5Zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6Z" />
                                                            <path
                                                                d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1ZM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118ZM2.5 3h11V2h-11v1Z" />
                                                        </svg>
                                                        <span class="ms-2">Remove</span>
                                                    </a>
                                                    <a class="dropdown-item" href="#">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="14"
                                                            height="14" fill="currentColor"
                                                            class="bi bi-credit-card" viewBox="0 0 16 16">
                                                            <path
                                                                d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V4zm2-1a1 1 0 0 0-1 1v1h14V4a1 1 0 0 0-1-1H2zm13 4H1v5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V7z" />
                                                            <path
                                                                d="M2 10a1 1 0 0 1 1-1h1a1 1 0 0 1 1 1v1a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1v-1z" />
                                                        </svg>
                                                        <span class="ms-2">Make it Primary</span>
                                                    </a>
                                                </span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <a href="#" class="btn btn-outline-primary mt-4" data-bs-toggle="modal"
                                    data-bs-target="#paymentModal">Add Payment Method</a>
                            </div>
                        </div>
                        <div class="card border-0 mb-4 shadow-sm">
                            <div class="card-body p-lg-5">
                                <div class="mb-5">
                                    <h4 class="mb-1">Invoice History</h4>
                                    <p class="fs-6 mb-0">The Invoice History Report is needed to justify the balance
                                        for a given range of invoices.</p>
                                </div>

                                <ul class="list-group list-group-flush mb-0">
                                    <li class="list-group-item px-0 py-3 border-top">
                                        <div class="d-flex justify-content-between text-dark fw-semibold fs-6">
                                            <span>01 Feb 2023</span>
                                            <span>$26.96</span>
                                            <a href="#">PDF</a>
                                        </div>
                                    </li>
                                    <li class="list-group-item px-0 py-3">
                                        <div class="d-flex justify-content-between text-dark fw-semibold fs-6">
                                            <span>31 Jan 2023</span>
                                            <span>$34.30</span>
                                            <a href="#">PDF</a>
                                        </div>
                                    </li>
                                    <li class="list-group-item px-0 py-3">
                                        <div class="d-flex justify-content-between text-dark fw-semibold fs-6">
                                            <span>30 Jan 2023</span>
                                            <span>$93.10</span>
                                            <a href="#">PDF</a>
                                        </div>
                                    </li>
                                    <li class="list-group-item px-0 py-3">
                                        <div class="d-flex justify-content-between text-dark fw-semibold fs-6">
                                            <span>29 Jan 2023</span>
                                            <span>$44.39</span>
                                            <a href="#">PDF</a>
                                        </div>
                                    </li>
                                    <li class="list-group-item px-0 py-3 border-bottom">
                                        <div class="d-flex justify-content-between text-dark fw-semibold fs-6">
                                            <span>23 Jan 2023</span>
                                            <span>$26.96</span>
                                            <a href="#">PDF</a>
                                        </div>
                                    </li>
                                </ul>

                                <div class="mt-4">
                                    <a href="#" class="icon-link icon-link-hover">
                                        View All Invoices
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14"
                                            fill="currentColor" class="bi bi-arrow-right" viewBox="0 0 16 16">
                                            <path fill-rule="evenodd"
                                                d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8z">
                                            </path>
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="card border-primary mb-4 border-1 shadow-sm">
                            <div class="card-header border-0 bg-primary bg-opacity-10 py-3">
                                <h3 class="mb-1 text-primary-emphasis">Free Plan - 30 Days Trial</h3>
                                <p class="mb-0 fs-6 text-primary-emphasis">For new web developers ready to create their
                                    first site.</p>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="mb-4 mb-lg-0">
                                            <h6 class="mb-3">Included:</h6>
                                            <ul class="list-unstyled mb-0">
                                                <li class="mb-2">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14"
                                                        height="14" fill="currentColor"
                                                        class="bi bi-check-circle-fill text-success"
                                                        viewBox="0 0 16 16">
                                                        <path
                                                            d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z" />
                                                    </svg>
                                                    <span class="ms-2">Browse Website with ads</span>
                                                </li>
                                                <li class="mb-2">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14"
                                                        height="14" fill="currentColor"
                                                        class="bi bi-check-circle-fill text-success"
                                                        viewBox="0 0 16 16">
                                                        <path
                                                            d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z" />
                                                    </svg>
                                                    <span class="ms-2">Build static sites</span>
                                                </li>
                                                <li class="mb-2">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14"
                                                        height="14" fill="currentColor"
                                                        class="bi bi-check-circle-fill text-success"
                                                        viewBox="0 0 16 16">
                                                        <path
                                                            d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z" />
                                                    </svg>
                                                    <span class="ms-2">1 spaces</span>
                                                </li>
                                                <li class="mb-2">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14"
                                                        height="14" fill="currentColor"
                                                        class="bi bi-check-circle-fill text-success"
                                                        viewBox="0 0 16 16">
                                                        <path
                                                            d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z" />
                                                    </svg>
                                                    <span class="ms-2">10 MB storage</span>
                                                </li>
                                                <li class="mb-2">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14"
                                                        height="14" fill="currentColor"
                                                        class="bi bi-check-circle-fill text-success"
                                                        viewBox="0 0 16 16">
                                                        <path
                                                            d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z" />
                                                    </svg>
                                                    <span class="ms-2">75 MB data / month</span>
                                                </li>
                                                <li class="mb-2">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14"
                                                        height="14" fill="currentColor"
                                                        class="bi bi-check-circle-fill text-success"
                                                        viewBox="0 0 16 16">
                                                        <path
                                                            d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z" />
                                                    </svg>
                                                    <span class="ms-2">500 requests / month</span>
                                                </li>
                                                <li class="mb-2">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14"
                                                        height="14" fill="currentColor"
                                                        class="bi bi-check-circle-fill text-success"
                                                        viewBox="0 0 16 16">
                                                        <path
                                                            d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z" />
                                                    </svg>
                                                    <span class="ms-2">Subdomain</span>
                                                </li>
                                                <li class="mb-2">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14"
                                                        height="14" fill="currentColor"
                                                        class="bi bi-check-circle-fill text-success"
                                                        viewBox="0 0 16 16">
                                                        <path
                                                            d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z" />
                                                    </svg>
                                                    <span class="ms-2">SSL certificate</span>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="mb-4 mb-lg-0">
                                            <h6 class="mb-3">Not included:</h6>
                                            <ul class="list-unstyled mb-0">
                                                <li class="mb-2">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14"
                                                        height="14" fill="currentColor"
                                                        class="bi bi-x-circle text-danger" viewBox="0 0 16 16">
                                                        <path
                                                            d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z" />
                                                        <path
                                                            d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z" />
                                                    </svg>
                                                    <span class="ms-2">Access to HTML video</span>
                                                </li>
                                                <li class="mb-2">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14"
                                                        height="14" fill="currentColor"
                                                        class="bi bi-x-circle text-danger" viewBox="0 0 16 16">
                                                        <path
                                                            d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z" />
                                                        <path
                                                            d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z" />
                                                    </svg>
                                                    <span class="ms-2">Priority support</span>
                                                </li>
                                                <li class="mb-2">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14"
                                                        height="14" fill="currentColor"
                                                        class="bi bi-x-circle text-danger" viewBox="0 0 16 16">
                                                        <path
                                                            d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z" />
                                                        <path
                                                            d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z" />
                                                    </svg>
                                                    <span class="ms-2">Advance static sites</span>
                                                </li>
                                                <li class="mb-2">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14"
                                                        height="14" fill="currentColor"
                                                        class="bi bi-x-circle text-danger" viewBox="0 0 16 16">
                                                        <path
                                                            d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z" />
                                                        <path
                                                            d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z" />
                                                    </svg>
                                                    <span class="ms-2">Analytics</span>
                                                </li>
                                                <li class="mb-2">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14"
                                                        height="14" fill="currentColor"
                                                        class="bi bi-x-circle text-danger" viewBox="0 0 16 16">
                                                        <path
                                                            d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z" />
                                                        <path
                                                            d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z" />
                                                    </svg>
                                                    <span class="ms-2">SEO</span>
                                                </li>
                                                <li class="mb-2">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14"
                                                        height="14" fill="currentColor"
                                                        class="bi bi-x-circle text-danger" viewBox="0 0 16 16">
                                                        <path
                                                            d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z" />
                                                        <path
                                                            d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z" />
                                                    </svg>
                                                    <span class="ms-2">Easy Integration Add ons</span>
                                                </li>
                                                <li class="mb-2">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14"
                                                        height="14" fill="currentColor"
                                                        class="bi bi-x-circle text-danger" viewBox="0 0 16 16">
                                                        <path
                                                            d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z" />
                                                        <path
                                                            d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z" />
                                                    </svg>
                                                    <span class="ms-2">Pages for static website hosting</span>
                                                </li>
                                                <li class="mb-2">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14"
                                                        height="14" fill="currentColor"
                                                        class="bi bi-x-circle text-danger" viewBox="0 0 16 16">
                                                        <path
                                                            d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z" />
                                                        <path
                                                            d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z" />
                                                    </svg>
                                                    <span class="ms-2">Web-based support</span>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="col-lg-12">
                                        <div class="mt-4">
                                            <a href="#" class="icon-link icon-link-hover">
                                                See all features and compare plans
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14"
                                                    fill="currentColor" class="bi bi-arrow-right"
                                                    viewBox="0 0 16 16">
                                                    <path fill-rule="evenodd"
                                                        d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8z">
                                                    </path>
                                                </svg>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!--Billing end-->
    </main>
    <!--Modal start-->
    <div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header align-items-center d-flex">
                    <h4 class="modal-title" id="paymentModalLabel">Add New Payment Method</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <!-- Modal body -->
                <div class="modal-body">
                    <!-- Form -->
                    <form class="row mb-4 needs-validation" novalidate>
                        <div class="mb-3 col-12 col-md-12 mb-4">
                            <h5 class="mb-3">Credit / Debit card</h5>
                            <!-- Radio button -->
                            <div class="d-inline-flex">
                                <div class="form-check me-2">
                                    <input type="radio" id="paymentRadioOne" name="paymentRadioOne"
                                        class="form-check-input" />
                                    <label class="form-check-label" for="paymentRadioOne"><img
                                            src="/assets/images/landings/account/americanexpress.svg"
                                            alt="card" /></label>
                                </div>
                                <!-- Radio button -->
                                <div class="form-check me-2">
                                    <input type="radio" id="paymentRadioTwo" name="paymentRadioOne"
                                        class="form-check-input" />
                                    <label class="form-check-label" for="paymentRadioTwo"><img
                                            src="/assets/images/landings/account/mastercard.svg"
                                            alt="card" /></label>
                                </div>

                                <!-- Radio button -->
                                <div class="form-check">
                                    <input type="radio" id="paymentRadioFour" name="paymentRadioOne"
                                        class="form-check-input" />
                                    <label class="form-check-label" for="paymentRadioFour"><img
                                            src="/assets/images/landings/account/visa.svg" alt="card" /></label>
                                </div>
                            </div>
                        </div>
                        <!-- Name on card -->
                        <div class="mb-3 col-12 col-md-4">
                            <label for="nameoncard" class="form-label">Name on card</label>
                            <input id="nameoncard" type="text" class="form-control" name="nameoncard"
                                placeholder="Name" required="" />
                        </div>
                        <!-- Month -->

                        <div class="mb-3 col-12 col-md-4">
                            <label class="form-label" for="month">Month</label>
                            <select class="form-select" id="month" required>
                                <option selected disabled value="">Choose...</option>
                                <option value="Month">Month</option>
                                <option value="Jan">Jan</option>
                                <option value="Feb">Feb</option>
                                <option value="Mar">Mar</option>
                                <option value="Apr">Apr</option>
                                <option value="May">May</option>
                                <option value="June">June</option>
                                <option value="July">July</option>
                                <option value="Aug">Aug</option>
                                <option value="Sep">Sep</option>
                                <option value="Oct">Oct</option>
                                <option value="Nov">Nov</option>
                                <option value="Dec">Dec</option>
                            </select>
                        </div>

                        <!-- Year -->
                        <div class="mb-3 col-12 col-md-4">
                            <label class="form-label" for="year">Year</label>
                            <select class="form-select" id="year" required>
                                <option selected disabled value="">Choose...</option>
                                <option value="Year">Year</option>
                                <option value="2020">2020</option>
                                <option value="2021">2021</option>
                                <option value="2022">2022</option>
                                <option value="2023">2023</option>
                                <option value="2024">2024</option>
                            </select>
                        </div>

                        <!-- Card number -->
                        <div class="mb-3 col-md-8 col-12">
                            <label for="cc-mask" class="form-label">Card Number</label>
                            <input type="text" class="form-control input-credit-card" id="cc-mask"
                                placeholder="xxxx-xxxx-xxxx-xxxx" required="" />
                        </div>
                        <!-- CVV -->
                        <div class="mb-3 col-md-4 col-12">
                            <div class="mb-3">
                                <label for="cvv" class="form-label">
                                    CVV Code
                                    <i class="bi bi-question-circle ms-1" data-bs-toggle="tooltip"
                                        data-placement="top"
                                        aria-label="A 3 - digit number, typically printed on the back of a card."
                                        data-bs-original-title="A 3 - digit number, typically printed on the back of a card."></i>
                                </label>
                                <input type="password" class="form-control" name="cvv" id="cvv"
                                    placeholder="xxx" maxlength="3" inputmode="numeric" required="" />
                            </div>
                        </div>
                        <!-- Button -->
                        <div class="col-md-6 col-12">
                            <button class="btn btn-primary" type="submit">Add New Card</button>
                            <button class="btn btn-light" type="button" data-bs-dismiss="modal">Close</button>
                        </div>
                    </form>
                    <span>
                        <span class="fw-bold">Note:</span>
                        that you can later remove your card at the account setting page.
                    </span>
                </div>
            </div>
        </div>
    </div>
    <!--Modal end-->

    @include('block.partials.footer')
    @include('block.partials.scripts')

    <script src="/assets/js/vendors/sidenav.js"></script>
    <script src="/node_modules/cleave.js/dist/cleave.min.js"></script>
    <script src="/assets/js/vendors/cleave-function.js"></script>
</x-www-layout>
