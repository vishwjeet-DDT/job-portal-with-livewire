<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="{{ url('assets/frontend/styles.css') }}" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet" />

    <link rel="stylesheet" href="{{ asset('assets/frontend/select2/select2.min.css') }}">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css" />
    <link rel="stylesheet" type="text/css"
        href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick-theme.css" />

    {{-- select 2 year --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
    .select2-container .select2-selection {
        height: 38px !important;
    }
    </style>
    @stack('css')
</head>

<body>
    @php #jobSearchModal

    $candidate = auth()->user();
    @endphp
    @include('layouts.frontend.header')


    <main>
        <section>
            <div class="offcanvas offcanvas-end" tabindex="-1" id="profileSideBar"
                aria-labelledby="profileSideBarLabel">
                <div class="offcanvas-header">
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                </div>
                <div class="offcanvas-body p-4">
                    <div class="sider_profile d-flex align-items-center">
                        <a href="javascript:void(0)">
                            <img src="{{ optional($candidate)->profile_image
                                ? asset('storage/' . optional($candidate)->profile_image)
                                : asset('assets/default_user.webp') }}" class="user_profile" alt="user profile">
                        </a>

                        <div class="profile_edit">
                            <h2 class="candidateName mb-0">{{ Auth::user()->name ?? '' }}</h2>
                            @if (isset($employmenthome))
                            <p class="designations_profile mb-1 p-0">
                                {{ $employmenthome->job_title }}
                            </p>
                            @else
                            <p class="designations_profile mb-1 p-0">No employment record</p>
                            @endif

                            <a href="{{ route('profile.edit') }}" class="profileEdit text-decoration-none">View
                                & Update Profile</a>
                        </div>
                    </div>
                    <hr>
                    <div class="mt-3">
                        <ul class="list-unstyled">
                            <li class="profile_list">
                                <a href="{{route('profile.edit')}}" class="d-flex gap-3 text-decoration-none text-dark">
                                    <i class="ri-settings-2-line"></i>
                                    <span>Settings</span>
                                </a>
                            </li>

                            <li class="profile_list">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <x-dropdown-link :href="route('logout')"
                                        class="d-flex gap-3 text-decoration-none text-dark" onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                        <i class="ri-logout-circle-line"></i>
                                        {{ __('Log Out') }}
                                    </x-dropdown-link>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        @yield('content')
    </main>

    @include('layouts.frontend.footer')
    @livewireScripts

</body>
@stack('js')
<script>
window.addEventListener('login-required', event => {
    Swal.fire({
        icon: 'warning',
        title: 'Warning!',
        text: 'Please login first!',
        showCancelButton: true,
        confirmButtonText: 'Login',
        cancelButtonText: 'Register',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '/login';
        } else if (result.dismiss === Swal.DismissReason.cancel) {
            window.location.href = '/register';
        }
    });
});
</script>
<script>
$('#add_language').select2({
    dropdownParent: $('#add_language').parent(),
    dropdownCss: {
        'z-index': 999999

    }
});
</script>

</html>
