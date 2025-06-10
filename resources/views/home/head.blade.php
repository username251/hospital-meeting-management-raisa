<!-- Navbar Start -->
<nav class="navbar navbar-expand-lg bg-white navbar-light sticky-top p-0 wow fadeIn" data-wow-delay="0.1s">
    <a href="{{ route('home.dashboard') }}" class="navbar-brand d-flex align-items-center px-4 px-lg-5">
        <h1 class="m-0 text-primary"><i class="far fa-hospital me-3"></i>Hospital of Airlangga</h1>
    </a>
    <button type="button" class="navbar-toggler me-4" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarCollapse">
        <div class="navbar-nav ms-auto p-4 p-lg-0">
            <a href="{{ route('home.dashboard') }}" class="nav-item nav-link active">Home</a>
            <a href="#about-section" class="nav-item nav-link">About</a>
            <a href="#service-section" class="nav-item nav-link">Service</a>
            <div class="nav-item dropdown">
                <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">Pages</a>
                <div class="dropdown-menu rounded-0 rounded-bottom m-0">
                    <a href="#feature-section" class="dropdown-item">Feature</a>
                    <a href="#doctors-section" class="dropdown-item">Our Doctor</a>
                    <a href="{{ route('patient.index') }}" class="dropdown-item">Dashboard</a>
                    @auth
                        @if (auth()->user()->role === 'patient')
                            <a href="{{ route('appointments.create') }}" class="dropdown-item">Appointment</a>
                        @endif
                    @else
                        <a href="{{ route('register') }}" class="dropdown-item">Appointment</a>
                    @endauth
                    <a href="#testimonial-section" class="dropdown-item">Testimonial</a>                      
                </div>
            </div>
            <a href="#contact-section" class="nav-item nav-link">Contact</a>
            @guest
                <a href="{{ route('login') }}" class="nav-item nav-link">Login</a>
            @endguest
        </div>
        @auth
            @if(auth()->user()->role === 'admin')
                <a href="{{ route('dashboard') }}" class="btn btn-primary rounded-0 py-4 px-lg-5 d-none d-lg-block">Dashboard<i class="fa fa-arrow-right ms-3"></i></a>
            @elseif(auth()->user()->role === 'patient')
                <a href="{{ route('patient.index') }}" class="btn btn-primary rounded-0 py-4 px-lg-5 d-none d-lg-block">Dashboard<i class="fa fa-arrow-right ms-3"></i></a>
            @elseif(auth()->user()->role === 'staff')
                <a href="{{ route('staff.index') }}" class="btn btn-primary rounded-0 py-4 px-lg-5 d-none d-lg-block">Dashboard<i class="fa fa-arrow-right ms-3"></i></a>
            @elseif(auth()->user()->role === 'doctor')
                <a href="{{ route('doctor.dashboard') }}" class="btn btn-primary rounded-0 py-4 px-lg-5 d-none d-lg-block">Dashboard<i class="fa fa-arrow-right ms-3"></i></a>
            @else
                <a href="{{ route('home.dashboard') }}" class="btn btn-primary rounded-0 py-4 px-lg-5 d-none d-lg-block">Home<i class="fa fa-arrow-right ms-3"></i></a>
            @endif
        @endauth
    </div>
</nav>
<!-- Navbar End -->

<!-- JavaScript untuk menutup menu -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const navLinks = document.querySelectorAll('.nav-link, .dropdown-item'); // Target semua nav-link dan dropdown-item
        const navbarCollapse = document.getElementById('navbarCollapse');

        if (navbarCollapse) {
            navLinks.forEach(link => {
                link.addEventListener('click', function (e) {
                    // Hanya tutup menu jika layar kecil (breakpoint lg = 992px)
                    if (window.innerWidth <= 991) {
                        const bsCollapse = new bootstrap.Collapse(navbarCollapse, {
                            toggle: false
                        });
                        bsCollapse.hide();
                    }
                });
            });
        } else {
            console.log('Elemen #navbarCollapse tidak ditemukan');
        }
    });
</script>