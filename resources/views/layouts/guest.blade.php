<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/js/app.js'])
    <link href="{{ asset('assets/frontend/styles.css')}}" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Jost:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">
    <!--- select 2 css link-->
    <link href="{{ url('assets/backend/libs/select2/select2.min.css') }} " rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.css" />

    <style>
        * {
            padding: 0px;
            margin: 0px;
            box-sizing: border-box;
            font-family: "Jost", sans-serif;
        }

        body {
            height: 100vh;
            overflow: hidden;
        }

        .login_condidate {
            width: 80%;
        }

        .form-group input:focus {
            outline: none;
            box-shadow: none;
        }

        .sign_inButton {
            border: none;
            background-color: #131D4F;
            padding: 10px;
            color: #fff;
            font-weight: 500;
            border-radius: 10px;
        }

        .headings {
            font-size: 22px;
        }

        .eye_elements {
            position: absolute;
            top: 50%;
            right: 5px;
            transform: translateY(-50%);
            font-size: 14px;
            cursor: pointer;
        }

        .select2-container .select2-selection--single {
            height: 35px !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 33px !important;
        }

        .select2-container--default .select2-selection--single {
            border: 1px solid #dee2e6 !important;
        }

        .select2-container--open .select2-dropdown--below {
            border: 1px solid #dee2e6 !important;
        }
    </style>
</head>

<body>

    <section class="w-100 h-100">
        <div class="row h-100 w-100">
            <div class="col-md-6 d-flex flex-column justify-content-center align-items-center"
                style="background-color: #131D4F;">
                <img src="{{ asset('assets/frontend/logo-ddt.jpeg') }}" alt="BoltHeights" class="img-fluid mb-3"
                    style="max-width:200px; height:auto;">
                <h1 class="text-white" id="welcome_text">Welcome to @yield('guest_heading')</h1>
            </div>
            <div class="col-md-6 d-flex align-items-center justify-content-center">
                <div class="card" style="width:80%;">
                    <div class="card-body">
                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>
    </section>


    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="{{ url('assets/frontend/script/script.js') }}"></script>
    <!--- select 2 link-->
    <script src="{{ url('assets/backend/libs/select2/select2.full.min.js') }}"></script>
    <script src="{{ url('assets/backend/libs/select2/select2.min.js') }}"></script>
    <script src="{{ url('assets/backend/libs/select2/select2.init.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/intlTelInput.min.js"></script>

    @if (session('login_error'))
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Login Failed',
                text: "{{ session('login_error') }}",
                confirmButtonColor: '#131D4F'
            });
        </script>
    @endif

    @if (session('register_error'))
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Register Failed',
                text: "{{ session('register_error') }}",
                confirmButtonColor: '#131D4F'
            });
        </script>
    @endif

    @if (session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: "{{ session('success') }}",
                confirmButtonColor: '#131D4F'
            });
        </script>
    @endif
    @if (session('status'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: "{{ session('status') }}",
                confirmButtonColor: '#3085d6'
            })
        </script>
    @endif




</body>
@stack('js')
    <!-- @if ($errors->has('email'))
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: "{{ $errors->first('email') }}",
                confirmButtonColor: '#d33'
            })
        </script>
    @endif -->
</html>
