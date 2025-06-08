<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <base href="{{ asset('admincss')}}/">
    <title>Pasien Panel | Dashboard</title> {{-- Mengganti judul --}}

    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&amp;display=fallback">
    <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css"> {{-- Pastikan path ini benar jika tidak online --}}
    <link rel="stylesheet" href="plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">
    <link rel="stylesheet" href="plugins/icheck-bootstrap/icheck-bootstrap.min.css">
    <link rel="stylesheet" href="plugins/jqvmap/jqvmap.min.css">
    <link rel="stylesheet" href="dist/css/adminlte.min2167.css?v=3.2.0">
    <link rel="stylesheet" href="plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
    <link rel="stylesheet" href="plugins/daterangepicker/daterangepicker.css">
    <link rel="stylesheet" href="plugins/summernote/summernote-bs4.min.css">
    @yield('customCss')
</head>
<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">

        <div class="preloader flex-column justify-content-center align-items-center">
            <img class="animation__shake" src="dist/img/AdminLTELogo.png" alt="AdminLTELogo" height="60" width="60">
        </div>

        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
                </li>
                <li class="nav-item d-none d-sm-inline-block">
                    <a href="{{ route('home.dashboard') }}" class="nav-link">Home</a>
                </li>
            </ul>

            <ul class="navbar-nav ml-auto">
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

                @auth
                    {{-- GANTI 'patient' DENGAN NAMA RELASI YANG BENAR JIKA BERBEDA --}}
                    @if(Auth::user()->patient)
                        <li class="nav-item dropdown user-menu">
                            <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">
                                {{-- Pastikan Auth::user()->patient->profile_picture ada dan pathnya benar --}}
                                <img src="{{ Auth::user()->patient->profile_picture ? asset('storage/' . Auth::user()->patient->profile_picture) : asset('admincss/dist/img/user2-160x160.jpg') }}" class="user-image img-circle elevation-2" alt="User Image">
                                <span class="d-none d-md-inline">{{ Auth::user()->name }}</span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                                <li class="user-header bg-primary"> {{-- Warna bisa disesuaikan (misal: bg-success, bg-teal) --}}
                                    <img src="{{ Auth::user()->patient->profile_picture ? asset('storage/' . Auth::user()->patient->profile_picture) : asset('admincss/dist/img/user2-160x160.jpg') }}" class="img-circle elevation-2" alt="User Image">
                                    <p>
                                        {{ Auth::user()->name }} - Pasien
                                        <small>Bergabung sejak {{ Auth::user()->created_at->translatedFormat('M. Y') }}</small>
                                    </p>
                                </li>
                                <li class="user-footer">
                                    {{-- Pastikan route 'patient.profile.edit' sudah ada --}}
                                    <a href="{{ route('patient.profile.edit') }}" class="btn btn-default btn-flat">Profil Saya</a>
                                    <a href="#" class="btn btn-default btn-flat float-right"
                                       onclick="event.preventDefault(); document.getElementById('logout-form-navbar-patient').submit();">
                                        Logout
                                    </a>
                                    <form id="logout-form-navbar-patient" action="{{ route('logout') }}" method="POST" style="display: none;">
                                        @csrf
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @elseif (Auth::check()) {{-- Jika user login tapi tidak punya profil pasien (misal admin atau role lain) --}}
                        <li class="nav-item">
                            <span class="nav-link">{{ Auth::user()->name }}</span>
                        </li>
                        <li class="nav-item">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="btn btn-link nav-link" style="border: none; padding: 0; color: inherit;">
                                    <i class="fas fa-sign-out-alt"></i> Logout
                                </button>
                            </form>
                        </li>
                    @endif
                @endauth
                {{-- Tombol Logout standalone yang lama bisa dihapus jika sudah tercover di dropdown --}}
                 <li class="nav-item">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-link nav-link" style="border: none; padding: 0; color: inherit;">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </button>
                    </form>
                </li> 

                <li class="nav-item">
                    <a class="nav-link" data-widget="control-sidebar" data-controlsidebar-slide="true" href="#" role="button">
                        <i class="fas fa-th-large"></i>
                    </a>
                </li>
            </ul>
        </nav>

        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <a href="{{ route('patient.index') }}" class="brand-link"> {{-- Arahkan ke dashboard pasien --}}
                <img src="dist/img/AdminLTELogo.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
                <span class="brand-text font-weight-light">Pasien Panel</span> {{-- Ganti nama panel --}}
            </a>

            <div class="sidebar">
                @auth
                    {{-- GANTI 'patient' DENGAN NAMA RELASI YANG BENAR JIKA BERBEDA --}}
                    @if(Auth::user()->patient)
                        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                            <div class="image">
                                <img src="{{ Auth::user()->patient->profile_picture ? asset('storage/' . Auth::user()->patient->profile_picture) : asset('admincss/dist/img/user2-160x160.jpg') }}" class="img-circle elevation-2" alt="User Image">
                            </div>
                            <div class="info">
                                {{-- Pastikan route 'patient.profile.edit' sudah ada --}}
                                <a href="{{ route('patient.profile.edit') }}" class="d-block">{{ Auth::user()->name }}</a>
                            </div>
                        </div>
                    @elseif(Auth::check()) {{-- User login tapi bukan pasien --}}
                         <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                            <div class="image">
                                <img src="{{ asset('admincss/dist/img/user2-160x160.jpg') }}" class="img-circle elevation-2" alt="User Image">
                            </div>
                            <div class="info">
                                <a href="#" class="d-block">{{ Auth::user()->name }}</a> {{-- Link default jika bukan pasien --}}
                            </div>
                        </div>
                    @endif
                @endauth

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

                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                        <li class="nav-item">
                            {{-- Pastikan route 'patient.index' atau dashboard pasien sesuai --}}
                            <a href="{{ route('patient.index') }}" class="nav-link {{ request()->routeIs('patient.index') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-tachometer-alt"></i> {{-- Icon bisa disesuaikan --}}
                                <p>Dashboard</p>
                            </a>
                        </li>
                        {{-- Tambahkan menu untuk edit profil pasien jika belum ada --}}
                        <li class="nav-item">
                            <a href="{{ route('patient.profile.edit') }}" class="nav-link {{ request()->routeIs('patient.profile.edit') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-user-edit"></i>
                                <p>Profil Saya</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            {{-- Pastikan route 'appointments.index' sesuai untuk pasien --}}
                            <a href="{{ route('appointments.index') }}" class="nav-link {{ request()->routeIs('appointments.index') || request()->routeIs('patient.appointments.*') ? 'active' : '' }}"> {{-- Menyesuaikan routeIs check --}}
                                <i class="nav-icon fas fa-calendar-check"></i>
                                <p>Manajemen Janji Temu</p>
                            </a>
                        </li>
                          <li class="nav-item">
                            {{-- Pastikan route 'appointments.index' sesuai untuk pasien --}}
                            <a href="{{ route('patient.doctor-schedule.index') }}" class="nav-link {{ request()->routeIs('appointments.index') || request()->routeIs('patient.appointments.*') ? 'active' : '' }}"> {{-- Menyesuaikan routeIs check --}}
                                <i class="nav-icon fas fa-calendar-check"></i>
                                <p>Jadwal Dokter</p>
                            </a>
                        </li>
                        {{-- Item menu lain untuk pasien bisa ditambahkan di sini --}}
                    </ul>
                </nav>
            </div>
        </aside>

        @yield('content')

       

        <aside class="control-sidebar control-sidebar-dark"></aside>
    </div>

    <script src="plugins/jquery/jquery.min.js"></script>
    <script src="plugins/jquery-ui/jquery-ui.min.js"></script>
    <script>
        $.widget.bridge('uibutton', $.ui.button)
    </script>
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
    <script src="dist/js/adminlte2167.js?v=3.2.0"></script>
    <script src="dist/js/demo.js"></script>
    <script src="dist/js/pages/dashboard.js"></script> {{-- Mungkin perlu disesuaikan jika dashboard pasien berbeda --}}
    @yield('customJs')
    @stack('scripts') {{-- Menambahkan @stack('scripts') jika belum ada --}}
</body>
</html> 