<header class="condidate_header  bg-white sticky-top" style="z-index:1030">
    <nav class="">
        <div class="container d-flex align-items-center">
            <a class="navbar-brand company_logo" href="{{ route('index') }}">
                <img src="{{ url('assets/frontend/logo-ddt.jpeg') }}" style="max-width: 180px;">
            </a>

            <div class="nav_list w-100 d-flex justify-content-between align-items-center">
                <div class="navmenu d-flex gap-5 align-items-center">
                    <ul class="job_list">
                        <li>
                            <a href="{{ route('jobs.index') }}">Jobs</a>
                            <span class="nottifications bg-danger"><span
                                    class="d-flex justify-content-center align-items-center">{{ \App\Models\Job::count() ?? '0' }}</span>
                        </li>
                    </ul>

                    <div class="positionrelative_job d-none" id="job_search">
                        <div class="d-flex search_job_element">
                            <input type="text" id="jobSearchInput" class="w-100 typeText"
                                placeholder="Search job titles, categories, cities" data-bs-toggle="modal"
                                data-bs-target="#jobSearchModal">
                            <button class="search_job" id="searchJobBtn" data-bs-toggle="modal"
                                data-bs-target="#jobSearchModal">
                                <i class="ri-search-line"></i>
                            </button>
                        </div>
                    </div>

                </div>
                <div class="profileMenu d-flex align-items-center gap-3">

                    <div class="candidate_element">
                        @if(auth()->check())
                                            <button class="candidate_profile d-flex align-items-center gap-3" data-bs-toggle="offcanvas"
                                                data-bs-target="#profileSideBar" aria-controls="profileSideBar">

                                                <span><i class="ri-menu-3-line"></i></span>
                                                <img src="{{ optional(auth()->user()->candidate)->profile_image
                            ? asset('storage/' . auth()->user()->candidate->profile_image)
                            : asset('assets/default_user.webp') }}" class="candidate_img" alt="user img">
                                            </button>
                        @else
                            <div class="d-flex gap-2">
                                <a href="{{ route('login') }}" class="btn btn-outline-primary">Login</a>
                                <a href="{{ route('register') }}"
                                    class="btn btn-primary">Register</a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </nav>
</header>
