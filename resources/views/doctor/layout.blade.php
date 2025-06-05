<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <base href="{{ asset('admincss') }}/">
    <title>Dokter Panel | Dashboard</title> {{-- Mengganti AdminLTE 3 menjadi Dokter Panel --}}

    <!-- Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    {{-- <link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/main.min.css' rel='stylesheet' /> --}}

    <!-- CSS Libraries -->
    <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <link rel="stylesheet" href="plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">
    <link rel="stylesheet" href="plugins/icheck-bootstrap/icheck-bootstrap.min.css">
    <link rel="stylesheet" href="plugins/jqvmap/jqvmap.min.css">
    <link rel="stylesheet" href="dist/css/adminlte.min2167.css?v=3.2.0"> {{-- Sesuai kode Anda --}}
    <link rel="stylesheet" href="plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
    <link rel="stylesheet" href="plugins/daterangepicker/daterangepicker.css">
    <link rel="stylesheet" href="plugins/summernote/summernote-bs4.min.css">
    
    <!-- Custom CSS -->
    @yield('customCss')
</head>

<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">
        <!-- Preloader -->
        <div class="preloader flex-column justify-content-center align-items-center">
            <img class="animation__shake" src="dist/img/AdminLTELogo.png" alt="AdminLTELogo" height="60" width="60">
        </div>

        <!-- Main Header / Navbar -->
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <!-- Left navbar links -->
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button">
                        <i class="fas fa-bars"></i>
                    </a>
                </li>
                <li class="nav-item d-none d-sm-inline-block">
                    <a href="{{ route('home.dashboard') }}" class="nav-link">Home</a>
                </li>
                <li class="nav-item d-none d-sm-inline-block">
                    <a href="#" class="nav-link">Contact</a>
                </li>
            </ul>

            <!-- Right navbar links -->
            <ul class="navbar-nav ml-auto">
                <!-- Navbar Search -->
                <li class="nav-item">
                    <a class="nav-link" data-widget="navbar-search" href="#" role="button">
                        <i class="fas fa-search"></i>
                    </a>
                    <div class="navbar-search-block">
                        <form class="form-inline">
                            <div class="input-group input-group-sm">
                                <input class="form-control form-control-navbar" type="search" placeholder="Search" aria-label="Search">
                                <div class="input-group-append">
                                    <button class="btn btn-navbar" type="submit">
                                        <i class="fas fa-search"></i>
                                    </button>
                                    <button class="btn btn-navbar" type="button" data-widget="navbar-search">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </li>

                <!-- User Menu Dropdown (DITAMBAHKAN) -->
                @auth
                    @if(Auth::user()->doctor) {{-- Pastikan user adalah dokter dan memiliki relasi doctor --}}
                        <li class="nav-item dropdown user-menu">
                            <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">
                                <img src="{{ Auth::user()->doctor->profile_picture ? asset('storage/' . Auth::user()->doctor->profile_picture) : asset('admincss/dist/img/user2-160x160.jpg') }}" class="user-image img-circle elevation-2" alt="User Image">
                                <span class="d-none d-md-inline">{{ Auth::user()->name }}</span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                                <!-- User image -->
                                <li class="user-header bg-info"> {{-- Anda bisa mengganti bg-info --}}
                                    <img src="{{ Auth::user()->doctor->profile_picture ? asset('storage/' . Auth::user()->doctor->profile_picture) : asset('admincss/dist/img/user2-160x160.jpg') }}" class="img-circle elevation-2" alt="User Image">
                                    <p>
                                        {{ Auth::user()->name }} - {{ Auth::user()->doctor->specialty->name ?? 'Dokter' }}
                                        <small>Bergabung sejak {{ Auth::user()->created_at->translatedFormat('M. Y') }}</small>
                                    </p>
                                </li>
                                <!-- Menu Footer-->
                                <li class="user-footer">
                                    <a href="{{ route('doctor.profile.edit') }}" class="btn btn-default btn-flat">Profil Saya</a>
                                    <a href="#" class="btn btn-default btn-flat float-right"
                                       onclick="event.preventDefault(); document.getElementById('logout-form-navbar').submit();">
                                        Logout
                                    </a>
                                    <form id="logout-form-navbar" action="{{ route('logout') }}" method="POST" style="display: none;">
                                        @csrf
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @else
                        {{-- Fallback jika user login tapi bukan dokter, bisa juga dikosongkan --}}
                        <li class="nav-item">
                            <span class="nav-link">{{ Auth::user()->name }}</span>
                        </li>
                    @endif
                @endauth
                <!-- Akhir User Menu Dropdown -->

                <!-- Tombol Logout (Standalone - Sesuai kode Anda, bisa dihapus jika sudah ada di dropdown) -->
                <li class="nav-item">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-link nav-link" style="border: none; padding: 0; color: inherit;">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </button>
                    </form>
                </li>

                <!-- Fullscreen -->
                <li class="nav-item">
                    <a class="nav-link" data-widget="fullscreen" href="#" role="button">
                        <i class="fas fa-expand-arrows-alt"></i>
                    </a>
                </li>

                <!-- Control Sidebar -->
                <li class="nav-item">
                    <a class="nav-link" data-widget="control-sidebar" data-controlsidebar-slide="true" href="#" role="button">
                        <i class="fas fa-th-large"></i>
                    </a>
                </li>
            </ul>
        </nav>

        <!-- Main Sidebar Container -->
        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <!-- Brand Logo -->
            <a href="{{ route('doctor.dashboard') }}" class="brand-link">
                <img src="dist/img/AdminLTELogo.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
                <span class="brand-text font-weight-light">Doctor Panel</span>
            </a>

            <!-- Sidebar -->
            <div class="sidebar">
                <!-- User Panel (DIPERBARUI FOTO & LINK) -->
                @auth
                  @if(Auth::user()->doctor)
                    <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                        <div class="image">
                            {{-- Menampilkan foto profil dokter, atau default jika tidak ada --}}
                            <img src="{{ Auth::user()->doctor->profile_picture ? asset('storage/' . Auth::user()->doctor->profile_picture) : asset('admincss/dist/img/user2-160x160.jpg') }}" class="img-circle elevation-2" alt="User Image">
                        </div>
                        <div class="info">
                            {{-- Link ke halaman edit profil dokter --}}
                            <a href="{{ route('doctor.profile.edit') }}" class="d-block">{{ Auth::user()->name }}</a>
                        </div>
                    </div>
                  @endif
                @endauth

                <!-- SidebarSearch Form (Sesuai kode Anda) -->
                <div class="form-inline">
                    <div class="input-group" data-widget="sidebar-search">
                        <input class="form-control form-control-sidebar" type="search" placeholder="Search" aria-label="Search">
                        <div class="input-group-append">
                            <button class="btn btn-sidebar">
                                <i class="fas fa-search fa-fw"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Sidebar Menu (RUTE SELAIN PROFIL TIDAK DIUBAH) -->
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                        <!-- Dashboard (Sesuai kode Anda) -->
                        <li class="nav-item">
                            <a href="{{ route('doctor.dashboard') }}" class="nav-link {{ request()->routeIs('doctor.dashboard') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-tachometer-alt"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>

                        <!-- Profil Saya (DITAMBAHKAN) -->
                        <li class="nav-item">
                            <a href="{{ route('doctor.profile.edit') }}" class="nav-link {{ request()->routeIs('doctor.profile.edit') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-user-edit"></i>
                                <p>Profil Saya</p>
                            </a>
                        </li>

                        <!-- Jadwal Saya (Sesuai kode Anda) -->
                        <li class="nav-item">
                            <a href="{{ route('doctor.availability.index') }}" class="nav-link {{ request()->routeIs('doctor.availabilities.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-calendar-alt"></i>
                                <p>Jadwal Saya</p>
                            </a>
                        </li>

                        <!-- Manajemen Antrean (Sesuai kode Anda, termasuk routeIs check) -->
                        <li class="nav-item">
                            <a href="{{ route('doctor.queue.index') }}" class="nav-link {{ request()->routeIs('staff.queue.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-hospital-user"></i>
                                <p>Antrean Pasien</p>
                            </a>
                        </li>

                        <!-- Manajemen Janji Temu (Sesuai kode Anda, termasuk routeIs check) -->
                        <li class="nav-item">
                            <a href="{{ route('doctor.appointments.index') }}" class="nav-link {{ request()->routeIs('staff.appointments.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-calendar-check"></i>
                                <p>Manajemen Janji Temu</p>
                            </a>
                        </li>

                        <!-- Janji Temu Hari Ini (Sesuai kode Anda) -->
                        <li class="nav-item">
                            <a href="{{ route('doctor.appointments.today') }}" class="nav-link {{ request()->routeIs('doctor.appointments.today') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-notes-medical"></i>
                                <p>Janji Temu Hari Ini</p>
                            </a>
                        </li>
                        {{-- Jika Anda memiliki item menu lain, biarkan seperti di kode Anda --}}
                    </ul>
                </nav>
            </div>
        </aside>

        @yield('content')

        <footer class="main-footer">
            <strong>&copy; 2014-{{ date('Y') }} <a href="https://adminlte.io/">AdminLTE.io</a>.</strong> All rights reserved.
            <div class="float-right d-none d-sm-inline-block">
                <b>Version</b> 3.2.0
            </div>
        </footer>

        <aside class="control-sidebar control-sidebar-dark"></aside>
    </div>

    <!-- JavaScript Libraries -->
    <script src="plugins/jquery/jquery.min.js"></script>
    <script src="plugins/jquery-ui/jquery-ui.min.js"></script>
    <script>
        $.widget.bridge('uibutton', $.ui.button)
    </script>
    {{-- <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js'></script> --}}
    {{-- <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/locales/id.js'></script> --}}
    <script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="plugins/chart.js/Chart.min.js"></script>
    <script src="plugins/sparklines/sparkline.js"></script>
    <script src="plugins/jqvmap/jquery.vmap.min.js"></script>
    <script src="plugins/jqvmap/maps/jquery.vmap.usa.js"></script>
    <script src="plugins/jquery-knob/jquery.knob.min.js"></script>
    <script src="plugins/moment/moment.min.js"></script>
    <script src="plugins/daterangepicker/daterangepicker.js"></script>
    <script src="plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>
    <script src="plugins/summernote/summernote-bs4.min.js"></script>
    <script src="plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
    <script src="dist/js/adminlte2167.js?v=3.2.0"></script> {{-- Sesuai kode Anda --}}
    <script src="dist/js/demo.js"></script>
    <script src="dist/js/pages/dashboard.js"></script>
    
    @yield('customJs')
    {{-- <script src="{{ asset('dist/js/adminlte.min.js') }}"></script> --}} {{-- Ini mungkin duplikat atau versi berbeda dari adminlte2167.js --}}
    @stack('scripts')
</body>
</html>
