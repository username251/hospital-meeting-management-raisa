@extends('doctor.layout')

@section('content')
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Dashboard Dokter</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">Dashboard</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- Welcome Card -->
            <div class="row">
                <div class="col-12">
                    <div class="card bg-gradient-info">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-8">
                                    <h3 class="text-white mb-1">Selamat Datang, Dr. {{ $doctor->user->name }}!</h3>
                                    <p class="text-white mb-0">
                                        <i class="fas fa-stethoscope mr-1"></i>
                                        {{ $doctor->specialty->name ?? 'Dokter Umum' }}
                                    </p>
                                    <small class="text-white opacity-75">
                                        <i class="fas fa-calendar mr-1"></i>
                                        {{ \Carbon\Carbon::now()->isoFormat('dddd, D MMMM Y') }}
                                    </small>
                                </div>
                                <div class="col-4 text-right">
                                    <div class="text-white">
                                        <i class="fas fa-user-md fa-3x opacity-75"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row">
                <!-- Today's Appointments -->
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3>{{ $todayAppointments->count() }}</h3>
                            <p>Janji Temu Hari Ini</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-calendar-day"></i>
                        </div>
                        <a href="{{ route('doctor.appointments.today') }}" class="small-box-footer">
                            Lihat Detail <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>

                <!-- Pending Appointments -->
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3>{{ $todayAppointments->where('status', 'confirmed')->count() }}</h3>
                            <p>Menunggu Konsultasi</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <a href="{{ route('doctor.appointments.index') }}" class="small-box-footer">
                            Kelola <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>

                <!-- Completed Today -->
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-primary">
                        <div class="inner">
                            <h3>{{ $todayAppointments->where('status', 'completed')->count() }}</h3>
                            <p>Selesai Hari Ini</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <a href="#" class="small-box-footer">
                            <i class="fas fa-chart-line"></i>
                        </a>
                    </div>
                </div>

                <!-- Queue Management -->
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3>{{ $todayAppointments->where('status', 'in_consultation')->count() }}</h3>
                            <p>Sedang Konsultasi</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-user-injured"></i>
                        </div>
                        <a href="{{ route('doctor.queue.index') }}" class="small-box-footer">
                            Antrean <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Today's Schedule -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-calendar-alt mr-1"></i>
                                Jadwal Hari Ini
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            @if($todayAppointments->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Waktu</th>
                                                <th>Pasien</th>
                                                <th>Status</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($todayAppointments->take(10) as $appointment)
                                                <tr>
                                                    <td>
                                                        <strong>{{ \Carbon\Carbon::parse($appointment->start_time)->format('H:i') }}</strong>
                                                        <small class="text-muted d-block">
                                                            {{ \Carbon\Carbon::parse($appointment->end_time)->format('H:i') }}
                                                        </small>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <img src="{{ asset('admincss/dist/img/user1-128x128.jpg') }}" 
                                                                 alt="Patient" class="img-circle mr-2" style="width: 30px; height: 30px;">
                                                            <div>
                                                                <strong>{{ $appointment->patient->user->name ?? 'N/A' }}</strong>
                                                                <small class="text-muted d-block">
                                                                    {{ $appointment->complaint ?? 'Tidak ada keluhan' }}
                                                                </small>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        @switch($appointment->status)
                                                            @case('confirmed')
                                                                <span class="badge badge-warning">Terkonfirmasi</span>
                                                                @break
                                                            @case('completed')
                                                                <span class="badge badge-success">Selesai</span>
                                                                @break
                                                            @case('cancelled')
                                                                <span class="badge badge-danger">Dibatalkan</span>
                                                                @break
                                                            @case('missed')
                                                                <span class="badge badge-secondary">Terlewat</span>
                                                                @break
                                                            @case('in_consultation')
                                                                <span class="badge badge-info">Konsultasi</span>
                                                                @break
                                                            @default
                                                                <span class="badge badge-light">{{ ucfirst($appointment->status) }}</span>
                                                        @endswitch
                                                    </td>
                                                    <td>
                                                        <div class="btn-group btn-group-sm">
                                                            @if($appointment->status == 'confirmed')
                                                                <form method="POST" action="{{ route('doctor.appointments.update-status', $appointment) }}" class="d-inline">
                                                                    @csrf
                                                                    @method('PATCH')
                                                                    <input type="hidden" name="status" value="in_consultation">
                                                                    <button type="submit" class="btn btn-info btn-sm" title="Mulai Konsultasi">
                                                                        <i class="fas fa-play"></i>
                                                                    </button>
                                                                </form>
                                                            @endif
                                                            @if($appointment->status == 'in_consultation')
                                                                <form method="POST" action="{{ route('doctor.appointments.update-status', $appointment) }}" class="d-inline">
                                                                    @csrf
                                                                    @method('PATCH')
                                                                    <input type="hidden" name="status" value="completed">
                                                                    <button type="submit" class="btn btn-success btn-sm" title="Selesai">
                                                                        <i class="fas fa-check"></i>
                                                                    </button>
                                                                </form>
                                                            @endif
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                @if($todayAppointments->count() > 10)
                                    <div class="text-center mt-3">
                                        <a href="{{ route('doctor.appointments.today') }}" class="btn btn-primary">
                                            Lihat Semua Janji Temu Hari Ini
                                        </a>
                                    </div>
                                @endif
                            @else
                                <div class="text-center py-5">
                                    <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">Tidak ada janji temu hari ini</h5>
                                    <p class="text-muted">Anda bisa beristirahat atau mengatur jadwal untuk hari mendatang.</p>
                                    <a href="{{ route('doctor.availability.index') }}" class="btn btn-primary">
                                        <i class="fas fa-calendar-plus mr-1"></i>
                                        Atur Jadwal
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Quick Actions & Info -->
                <div class="col-md-4">
                    <!-- Quick Actions -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-bolt mr-1"></i>
                                Aksi Cepat
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-6">
                                    <a href="{{ route('doctor.availability.index') }}" class="btn btn-primary btn-block mb-2">
                                        <i class="fas fa-calendar-alt"></i><br>
                                        <small>Jadwal Saya</small>
                                    </a>
                                </div>
                                <div class="col-6">
                                    <a href="{{ route('doctor.queue.index') }}" class="btn btn-warning btn-block mb-2">
                                        <i class="fas fa-users"></i><br>
                                        <small>Antrean</small>
                                    </a>
                                </div>
                                <div class="col-6">
                                    <a href="{{ route('doctor.appointments.index') }}" class="btn btn-success btn-block mb-2">
                                        <i class="fas fa-calendar-check"></i><br>
                                        <small>Janji Temu</small>
                                    </a>
                                </div>
                                <div class="col-6">
                                    <a href="{{ route('doctor.profile.edit') }}" class="btn btn-info btn-block mb-2">
                                        <i class="fas fa-user-edit"></i><br>
                                        <small>Profil</small>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Doctor Info -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-info-circle mr-1"></i>
                                Informasi Dokter
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="text-center mb-3">
                                <img src="{{ $doctor->profile_picture ? asset('storage/' . $doctor->profile_picture) : asset('admincss/dist/img/user2-160x160.jpg') }}" 
                                     alt="Doctor Photo" class="img-circle" style="width: 80px; height: 80px;">
                            </div>
                            <ul class="list-unstyled">
                                <li><strong>Nama:</strong> Dr. {{ $doctor->user->name }}</li>
                                <li><strong>Spesialisasi:</strong> {{ $doctor->specialty->name ?? 'Dokter Umum' }}</li>
                                <li><strong>SIP:</strong> {{ $doctor->license_number ?? '-' }}</li>
                                <li><strong>Email:</strong> {{ $doctor->user->email }}</li>
                                <li><strong>Bergabung:</strong> {{ $doctor->user->created_at->translatedFormat('M Y') }}</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-history mr-1"></i>
                                Aktivitas Terakhir
                            </h3>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled">
                                @foreach($todayAppointments->where('tus', 'completed')->take(3) as $completed)
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success mr-2"></i>
                                        Selesai konsultasi dengan {{ $completed->patient->user->name ?? 'N/A' }}
                                        <br><small class="text-muted">{{ \Carbon\Carbon::parse($completed->updated_at)->diffForHumans() }}</small>
                                    </li>
                                @endforeach
                                @if($todayAppointments->where('status', 'completed')->count() == 0)
                                    <li class="text-muted text-center">
                                        <i class="fas fa-clock mr-1"></i>
                                        Belum ada aktivitas hari ini
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Weekly Schedule Overview (Optional) -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-chart-line mr-1"></i>
                                Statistik Mingguan
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-md-3">
                                    <div class="border rounded p-3">
                                        <h4 class="text-primary">{{ $todayAppointments->count() }}</h4>
                                        <small class="text-muted">Hari Ini</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="border rounded p-3">
                                        <h4 class="text-success">-</h4>
                                        <small class="text-muted">Minggu Ini</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="border rounded p-3">
                                        <h4 class="text-warning">-</h4>
                                        <small class="text-muted">Bulan Ini</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="border rounded p-3">
                                        <h4 class="text-info">{{ $doctor->user->created_at->diffInDays() }}</h4>
                                        <small class="text-muted">Hari Bergabung</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@section('customCss')
<style>
    .small-box {
        border-radius: 0.5rem;
        box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
        transition: transform 0.2s;
    }
    .small-box:hover {
        transform: translateY(-2px);
    }
    .card {
        box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
        border-radius: 0.5rem;
    }
    .table-responsive {
        border-radius: 0.5rem;
    }
    .btn-group-sm .btn {
        border-radius: 0.25rem;
    }
    .badge {
        font-size: 0.75em;
    }
    .bg-gradient-info {
        background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
    }
    .opacity-75 {
        opacity: 0.75;
    }
</style>
@endsection

@section('customJs')
<script>
    // Auto refresh setiap 5 menit untuk update status real-time
    setInterval(function() {
        // Optional: implementasi AJAX untuk refresh otomatis
        // location.reload();
    }, 300000); // 5 menit

    // Confirmation untuk update status
    $('form[action*="update-status"]').on('submit', function(e) {
        const status = $(this).find('input[name="status"]').val();
        let message = '';
        
        switch(status) {
            case 'in_consultation':
                message = 'Mulai konsultasi dengan pasien ini?';
                break;
            case 'completed':
                message = 'Tandai konsultasi sebagai selesai?';
                break;
            default:
                message = 'Ubah status janji temu?';
        }
        
        if (!confirm(message)) {
            e.preventDefault();
        }
    });

    // Tooltip untuk aksi buttons
    $('[title]').tooltip();
</script>
@endsection