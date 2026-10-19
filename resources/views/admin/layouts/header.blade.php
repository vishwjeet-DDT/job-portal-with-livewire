<header class="topbar">
    <nav class="navbar top-navbar navbar-expand-md navbar-dark">
        <div class="navbar-header">
            <a class="nav-toggler waves-effect waves-light d-block d-md-none" href="javascript:void(0)"><i
                    class="ri-close-line ri-menu-2-line fs-6"></i></a>
            <a class="navbar-brand" href="javascript:void(0)">
                <b class="logo-icon wdth"><img src="{{ url('assets/backend/images/logo-ddt.jpeg') }}" alt="homepage"
                        class="dark-logo bgmini" /> </b>
                <span class="logo-text">
                </span>
            </a>
            <a class="topbartoggler d-block d-md-none waves-effect waves-light" href="javascript:void(0)"
                data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
                aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation"><i
                    class="ri-more-line fs-6"></i></a>
        </div>
        <div class="navbar-collapse collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link sidebartoggler d-none d-md-block" href="javascript:void(0)"><i
                            data-feather="menu"></i></a>
                </li>

            </ul>
            <ul class="navbar-nav">
                <li class="nav-item dropdown profile-dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="javascript:void(0)"
                        data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        @if (Auth::user()->profile != '')
                            <img src="{{ url('assets/upload/users') . '/' . Auth::user()->profile }}" alt="user" width="30"
                                class="profile-pic rounded-circle" id="output" />
                        @else
                            <img src="{{ url('assets/backend/images/default_user.webp') }}" alt="user" width="30"
                                class="profile-pic rounded-circle" id="output" />
                        @endif
                        <div class="d-none d-md-flex">
                            <span class="ms-2">Hi,
                                <span class="text-dark fw-bold">{{ Auth::user()->name ?? '' }}</span></span>
                            <span>
                                <i data-feather="chevron-down" class="feather-sm"></i>
                            </span>
                        </div>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end mailbox  dropdown-menu-animate-up ">
                        <ul class="list-style-none">
                            <li class="p-30 pb-2">
                                <div class="rounded-top d-flex align-items-center">
                                    <h3 class="card-title mb-0">User Profile</h3>
                                    <div class="ms-auto">
                                        <a href="" class="link py-0">
                                            <i data-feather="x-circle"></i>
                                        </a>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center mt-4  pt-3 pb-4  border-bottom">
                                    @if (Auth::user()->profile != '')
                                        <img src="{{ url('assets/upload/users') . '/' . Auth::user()->profile }}" alt="user"
                                            width="80" class="rounded-circle" />
                                    @else
                                        <img src="{{ url('assets/backend/images/default_user.webp') }}" alt="user"
                                            width="80" class="rounded-circle" />
                                    @endif
                                    <div class="ms-4">
                                        <h4 class="mb-0">{{ Auth::user()->name ?? '' }}</h4>
                                        <span class="text-muted">
                                            @if (Auth::user()->user_role_id == 1)
                                                {{ 'Super Admin' }}
                                            @elseif(Auth::user()->user_role_id == 2)
                                                {{ 'Admin' }}
                                            @endif
                                        </span>
                                        <p class="text-muted mb-0 mt-1"><i data-feather="mail"
                                                class="feather-sm me-1"></i>{{ Auth::user()->email ?? '' }}</p>
                                    </div>
                                </div>
                            </li>

                            <li class="p-30 pt-0">



                                <div class="message-center message-body position-relative" style="height: 110px">
                                    <a href=""
                                        class=" message-item  px-2 d-flex align-items-center border-bottom  py-3 ">
                                        <span class="btn btn-light-info btn-rounded-lg text-info">
                                            <i class="ri-user-line"></i>
                                        </span>
                                        <div class="w-75 d-inline-block v-middle ps-3 ms-1">
                                            <h5 class="message-title mb-0  mt-1 fs-4 font-weight-medium ">
                                                My Profile
                                            </h5>
                                            <span
                                                class="fs-3 text-nowrap d-block time text-truncate fw-normal mt-1 text-muted">Account
                                                Settings</span>
                                        </div>
                                    </a>
                                </div>
                                <div class="mt-4">
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <x-dropdown-link :href="route('logout')"
                                            class="btn btn-info text-white text-center" onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                            {{ __('Log Out') }}
                                        </x-dropdown-link>
                                    </form>
                                </div>
                            </li>
                        </ul>
                    </div>

                </li>
            </ul>
        </div>
    </nav>
</header>
