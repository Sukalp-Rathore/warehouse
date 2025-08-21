<!DOCTYPE html>
<html lang="en" dir="ltr" data-nav-layout="vertical" data-theme-mode="light" data-header-styles="light"
    data-menu-styles="dark" data-toggled="close">

<head>

    <!-- Meta Data -->
    <meta charset="UTF-8">
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Health</title>
    <meta name="Description" content="Health">
    <meta name="Author" content="Health">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="keywords" content="Health">

    <!-- Favicon -->
    <link rel="icon" href="assets/images/brand-logos/1.png" type="image/x-icon">

    <!-- Choices JS -->
    <script src="assets/libs/choices.js/public/assets/scripts/choices.min.js"></script>

    <!-- Main Theme Js -->
    <script src="assets/js/main.js"></script>

    <!-- Bootstrap Css -->
    <link id="style" href="assets/libs/bootstrap/css/bootstrap.min.css" rel="stylesheet">

    <!-- Style Css -->
    <link href="assets/css/styles.css" rel="stylesheet">

    <!-- Icons Css -->
    <link href="assets/css/icons.css" rel="stylesheet">

    <!-- Node Waves Css -->
    <link href="assets/libs/node-waves/waves.min.css" rel="stylesheet">

    <!-- Simplebar Css -->
    <link href="assets/libs/simplebar/simplebar.min.css" rel="stylesheet">

    <!-- Color Picker Css -->
    <link rel="stylesheet" href="assets/libs/flatpickr/flatpickr.min.css">
    <link rel="stylesheet" href="assets/libs/@simonwep/pickr/themes/nano.min.css">

    <!-- Choices Css -->
    <link rel="stylesheet" href="assets/libs/choices.js/public/assets/styles/choices.min.css">

    <!-- FlatPickr CSS -->
    <link rel="stylesheet" href="assets/libs/flatpickr/flatpickr.min.css">

    <link rel="stylesheet" href="assets/libs/prismjs/themes/prism-coy.min.css">

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">

    <!-- Auto Complete CSS -->
    <link rel="stylesheet" href="assets/libs/@tarekraafat/autocomplete.js/css/autoComplete.css">

    <link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.3.0/css/responsive.bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

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
        .ttmain {
            width: 150px;
            height: 45px !important;
        }
    </style>

</head>

<body>
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

    <div class="page">
        <!-- app-header -->
        <header class="app-header sticky" id="header">

            <!-- Start::main-header-container -->
            <div class="main-header-container container-fluid">

                <!-- Start::header-content-left -->
                <div class="header-content-left">

                    <!-- Start::header-element -->
                    <div class="header-element">
                        <div class="horizontal-logo">
                            <a href="index.html" class="header-logo">
                                <img src="assets/images/brand-logos/florist.png" alt="logo"
                                    style="mix-blend-mode:color-burn" class="desktop-logo">
                                <img src="assets/images/brand-logos/florist.png" alt="logo"
                                    style="mix-blend-mode:color-burn" class="toggle-dark">
                                <img src="assets/images/brand-logos/florist.png" alt="logo"
                                    style="mix-blend-mode:color-burn" class="desktop-dark">
                                <img src="assets/images/brand-logos/florist.png" alt="logo"
                                    style="mix-blend-mode:color-burn" class="toggle-logo">
                                <img src="assets/images/brand-logos/florist.png" alt="logo"
                                    style="mix-blend-mode:color-burn" class="toggle-white">
                                <img src="assets/images/brand-logos/florist.png" alt="logo"
                                    style="mix-blend-mode:color-burn" class="desktop-white">
                            </a>
                        </div>
                    </div>
                    <!-- End::header-element -->

                    <!-- Start::header-element -->
                    <div class="header-element mx-lg-0 mx-2">
                        <a aria-label="Hide Sidebar"
                            class="sidemenu-toggle header-link animated-arrow hor-toggle horizontal-navtoggle"
                            data-bs-toggle="sidebar" href="javascript:void(0);"><span></span></a>
                    </div>
                </div>
                <!-- End::header-content-left -->

                <!-- Start::header-content-right -->
                <ul class="header-content-right">
                    <!-- Start::header-element -->
                    <li class="header-element dropdown">
                        <!-- Start::header-link|dropdown-toggle -->
                        <a href="javascript:void(0);" class="header-link dropdown-toggle" id="mainHeaderProfile"
                            data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                            <div class="d-flex align-items-center">
                                <div>
                                    <img src="assets/images/faces/user.jpg" alt="img" class="avatar avatar-sm">
                                </div>
                            </div>
                        </a>
                        <!-- End::header-link|dropdown-toggle -->
                        <ul class="main-header-dropdown dropdown-menu pt-0 overflow-hidden header-profile-dropdown dropdown-menu-end"
                            aria-labelledby="mainHeaderProfile">
                            <li>
                                <div class="dropdown-item text-center border-bottom">
                                    <span>
                                        Admin
                                    </span>
                                </div>
                            </li>
                            <li><a class="dropdown-item d-flex align-items-center" href="{{ route('logout') }}"><i
                                        class="fe fe-lock p-1 rounded-circle bg-primary-transparent ut me-2 fs-16"></i>Log
                                    Out</a></li>
                        </ul>
                    </li>
                    <!-- End::header-element -->
                </ul>
                <!-- End::header-content-right -->

            </div>
            <!-- End::main-header-container -->

        </header>
        <!-- /app-header -->
        <!-- Start::app-sidebar -->
        <aside class="app-sidebar sticky" id="sidebar">

            <!-- Start::main-sidebar-header -->
            <div class="main-sidebar-header">
                <a class="header-logo">
                    <img src="assets/images/brand-logos/florist.png" alt="logo" class="desktop-dark ttmain">
                    <img src="assets/images/brand-logos/florist.png" alt="logo" class="toggle-dark">
                </a>
            </div>
            <!-- End::main-sidebar-header -->

            <!-- Start::main-sidebar -->
            <div class="main-sidebar" id="sidebar-scroll">

                <!-- Start::nav -->
                <nav class="main-menu-container nav nav-pills flex-column sub-open">
                    <div class="slide-left" id="slide-left">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191" width="24" height="24"
                            viewBox="0 0 24 24">
                            <path d="M13.293 6.293 7.586 12l5.707 5.707 1.414-1.414L10.414 12l4.293-4.293z"></path>
                        </svg>
                    </div>
                    <ul class="main-menu">
                        <!-- Start::slide -->
                        <li class="slide">
                            <a href="{{ route('dashboard') }}"
                                class="side-menu__item {{ url()->current() == route('dashboard') ? 'active' : '' }}">
                                <i class="bi bi-bar-chart-fill w-7 h-7 side-menu__icon"></i>
                                <span class="side-menu__label">Dashboard</span>
                            </a>
                        </li>
                        <li class="slide">
                            <a href="{{ route('patients') }}"
                                class="side-menu__item {{ url()->current() == route('patients') ? 'active' : '' }}">
                                <i class="bi bi-person w-7 h-7 side-menu__icon"></i>
                                <span class="side-menu__label">Patients</span>
                            </a>
                        </li>
                        <li class="slide">
                            <a href="{{ route('visits') }}"
                                class="side-menu__item {{ url()->current() == route('visits') ? 'active' : '' }}">
                                <i class="bi bi-person w-7 h-7 side-menu__icon"></i>
                                <span class="side-menu__label">Visits</span>
                            </a>
                        </li>
                        <li class="slide">
                            <a href="{{ route('appointments') }}"
                                class="side-menu__item {{ url()->current() == route('appointments') ? 'active' : '' }}">
                                <i class="bi bi-plus w-7 h-7 side-menu__icon"></i>
                                <span class="side-menu__label">Show Appointment</span>
                            </a>
                        </li>
                        <li class="slide">
                            <a href="{{ route('create.appointment') }}"
                                class="side-menu__item {{ url()->current() == route('create.appointment') ? 'active' : '' }}">
                                <i class="bi bi-plus w-7 h-7 side-menu__icon"></i>
                                <span class="side-menu__label">Create Appointment</span>
                            </a>
                        </li>
                        <li class="slide">
                            <a href="{{ route('inventory') }}"
                                class="side-menu__item  {{ url()->current() == route('inventory') ? 'active' : '' }}">
                                <i class="bi bi-truck w-7 h-7 side-menu__icon"></i>
                                <span class="side-menu__label">Inventory Management</span>
                            </a>
                        </li>
                        <!-- Start::slide -->
                    </ul>
                    <div class="slide-right" id="slide-right"><svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191"
                            width="24" height="24" viewBox="0 0 24 24">
                            <path d="M10.707 17.707 16.414 12l-5.707-5.707-1.414 1.414L13.586 12l-4.293 4.293z"></path>
                        </svg></div>
                </nav>
                <!-- End::nav -->

            </div>
            <!-- End::main-sidebar -->

        </aside>
        <!-- End::app-sidebar -->

        <!-- Start::app-content -->
        <div class="main-content app-content">
            <div class="container-fluid">
                @yield('content')
            </div>
        </div>

        <!-- Footer Start -->
        <footer class="footer mt-auto py-3 bg-white text-center">
            <div class="container">
                <span class="text-muted"> Copyright © <span id="year"></span> <a href="javascript:void(0);"
                        class="text-dark fw-medium">Health</a>
                </span>
            </div>
        </footer>
        <!-- Footer End -->

        <div class="modal fade" id="header-responsive-search" tabindex="-1"
            aria-labelledby="header-responsive-search" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-body">
                        <div class="input-group">
                            <input type="text" class="form-control border-end-0" placeholder="Search Anything ..."
                                aria-label="Search Anything ..." aria-describedby="button-addon2">
                            <button class="btn btn-primary" type="button" id="button-addon2"><i
                                    class="bi bi-search"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Scroll To Top -->
    <div class="scrollToTop">
        <span class="arrow"><i class="ti ti-arrow-narrow-up fs-20"></i></span>
    </div>
    <div id="responsive-overlay"></div>
    <!-- Scroll To Top -->

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

    <!-- Popper JS -->
    <script src="assets/libs/@popperjs/core/umd/popper.min.js"></script>

    <!-- Prism JS -->
    <script src="assets/libs/prismjs/prism.js"></script>
    <script src="assets/js/prism-custom.js"></script>

    <!-- Bootstrap JS -->
    <script src="assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Defaultmenu JS -->
    <script src="assets/js/defaultmenu.min.js"></script>

    <!-- Node Waves JS-->
    <script src="assets/libs/node-waves/waves.min.js"></script>

    <!-- Sticky JS -->
    <script src="assets/js/sticky.js"></script>

    <!-- Simplebar JS -->
    <script src="assets/libs/simplebar/simplebar.min.js"></script>
    <script src="assets/js/simplebar.js"></script>

    <!-- Auto Complete JS -->
    <script src="assets/libs/@tarekraafat/autocomplete.js/autoComplete.min.js"></script>

    <!-- Color Picker JS -->
    <script src="assets/libs/@simonwep/pickr/pickr.es5.min.js"></script>

    <!-- Date & Time Picker JS -->
    <script src="assets/libs/flatpickr/flatpickr.min.js"></script>

    <!-- Datatables Cdn -->
    <script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.12.1/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.3.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.print.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.6/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.html5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>

    <!-- Internal Datatables JS -->
    <script src="assets/js/datatables.js"></script>
    <!-- Select2 Cdn -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- Internal Select-2.js -->
    <script src="assets/js/select2.js"></script>

    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

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
    </script>
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $(document).ajaxStart(function() {
            $('<div class="overlay"><center>' + html_loader() + '</center></div>').insertAfter("body");
        });
        $(document).ajaxStop(function() {
            $(".overlay").remove();
        });
    </script>
    @yield('js')
</body>
