<!DOCTYPE html>
<html lang="en" dir="ltr" data-nav-layout="vertical" data-vertical-style="overlay" data-theme-mode="light"
    data-header-styles="light" data-menu-styles="light" data-toggled="close">

<head>

    <!-- Meta Data -->
    <meta charset="UTF-8">
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title> Health </title>
    <meta name="Description" content="Health">
    <meta name="Author" content="Health">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="keywords"
        content="admin,admin dashboard,admin panel,admin template,bootstrap,clean,dashboard,flat,jquery,modern,responsive,premium admin templates,responsive admin,ui,ui kit.">

    <!-- Favicon -->
    <link rel="icon" href="assets/images/brand-logos/1.png" type="image/x-icon">

    <!-- Main Theme Js -->
    <script src="assets/js/authentication-main.js"></script>

    <!-- Bootstrap Css -->
    <link id="style" href="assets/libs/bootstrap/css/bootstrap.min.css" rel="stylesheet">

    <!-- Style Css -->
    <link href="assets/css/styles.css" rel="stylesheet">

    <!-- Icons Css -->
    <link href="assets/css/icons.css" rel="stylesheet">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <!-- Toastify CSS -->
    <link rel="stylesheet" href="assets/libs/toastify-js/src/toastify.css">

    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

    <style>
        /* custom loader */
        .overlay1 {
            position: fixed;
            width: 100%;
            height: 100vh;
            background: #fff;
            z-index: 9999;
            top: 0;
        }

        .loader {
            position: absolute;
            top: 50%;
            left: 48%;
        }

        .line {
            animation: expand 1s ease-in-out infinite;
            border-radius: 10px;
            display: inline-block;
            transform-origin: center center;
            margin: 0 3px;
            width: 1px;
            height: 25px;
        }

        .line:nth-child(1) {
            background: #27ae60;
        }

        .line:nth-child(2) {
            animation-delay: 180ms;
            background: #f1c40f;
        }

        .line:nth-child(3) {
            animation-delay: 360ms;
            background: #e67e22;
        }

        .line:nth-child(4) {
            animation-delay: 540ms;
            background: #2980b9;
        }

        @keyframes expand {
            0% {
                transform: scale(1);
            }

            25% {
                transform: scale(2);
            }
        }

        /* custom loader */
    </style>
</head>

<body class="bg-white">
    <!-- Loader -->
    <div class="overlay1">
        <center>
            <div class="loader">
                <div class="line"></div>
                <div class="line"></div>
                <div class="line"></div>
                <div class="line"></div>
            </div>
        </center>
    </div>
    <!-- Loader -->
    <div class="row authentication authentication-cover-main mx-0">
        <div class="col-xxl-6 col-xl-7">
            <div class="row justify-content-center align-items-center h-100">
                <div class="col-xxl-8 col-xl-9 col-lg-6 col-md-6 col-sm-8 col-12">
                    <img src="assets/images/brand-logos/florist.png" alt=""
                        class="authentication-brand desktop-white mb-3 log-logo">
                    <div class="card custom-card my-auto border">
                        <div class="card-body p-5">
                            <p class="h3 mb-3 text-center">Log into your account</p>
                            <p class="mb-4 text-muted op-7 fw-normal text-center">Insert your credentials below to
                                access your account</p>
                            <div class="row gy-3">
                                <form id="loginForm" action="{{ route('login.submit') }}" method="POST">
                                    @csrf
                                    <div class="row gy-3">
                                        <div class="col-xl-12 form-group">
                                            <label for="username" class="form-label text-default">User Name</label>
                                            <input type="text" class="form-control" id="username" name="username"
                                                placeholder="User name" autocomplete="off">
                                        </div>
                                        <div class="col-xl-12 form-group">
                                            <label for="password"
                                                class="form-label text-default d-block">Password</label>
                                            <div class="position-relative">
                                                <input type="password" class="form-control create-password-input"
                                                    name="password" id="password" placeholder="Password">
                                                <a href="javascript:void(0);" class="show-password-button text-muted"
                                                    onclick="createpassword('password',this)" id="button-addon2"><i
                                                        class="ri-eye-off-line align-middle"></i></a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-grid mt-4">
                                        <button type="submit" class="btn btn-primary">Sign In</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xxl-6 col-xl-5 col-lg-12 d-xl-block d-none px-0">
            <div class="authentication-cover overflow-hidden">
                <div class="aunthentication-cover-content d-flex align-items-center justify-content-center">
                    <div>
                        <h1 class="text-fixed-white mb-1 fw-medium" style="font-size:2.3rem">HEALTH!
                        </h1>
                        <h6 class="text-fixed-white mb-3">Health management system.</h6>
                        <p class="text-fixed-white mb-1 op-6">Health Industry Solutions</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <!-- Bootstrap JS -->
    <script src="assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Show Password JS -->
    <script src="assets/js/show-password.js"></script>

    <!-- Toastify JS -->
    <script src="assets/libs/toastify-js/src/toastify.js"></script>

    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $('#loginForm').on('submit', function(e) {
            e.preventDefault();
            var form = $(this);
            $.ajax({
                type: form.attr('method'),
                url: form.attr('action'),
                data: form.serialize(),
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        window.location.href = "{{ route('dashboard') }}";
                    }
                },
                error: function(response) {
                    if (response.responseJSON && response.responseJSON.error) {
                        toastr.error(response.responseJSON.error);
                    } else {
                        toastr.error("An unexpected error occurred.");
                    }
                }
            });
        });
    </script>
    <script>
        $(window).on("load", function() {
            // $('.overlay').fadeOut();
            $('.overlay1').fadeOut();
        });

        function html_loader() {
            return "<div class='loader'>" +
                "<div class='line'></div>" +
                "<div class='line'></div>" +
                "<div class='line'></div>" +
                "<div class='line'></div>" +
                "</div>";


        }

        $(document).ajaxStart(function() {
            $('<div class="overlay"><center>' + html_loader() + '</center></div>').insertAfter("body");
        });
        $(document).ajaxStop(function() {
            $(".overlay").remove();
        });
    </script>
</body>

</html>
