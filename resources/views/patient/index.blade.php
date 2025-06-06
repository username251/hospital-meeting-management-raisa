@extends('patient.layout')

@section('customCss')
<style>
    .info-box {
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease;
    }
    .info-box:hover {
        transform: translateY(-5px);
    }
    .card {
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        border: none;
    }
    .card-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 10px 10px 0 0 !important;
    }
    .small-box {
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease;
    }
    .small-box:hover {
        transform: translateY(-5px);
    }
    .timeline-item {
        padding: 15px;
        margin-bottom: 15px;
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        border-left: 4px solid #007bff;
    }
    .timeline-item.today {
        border-left-color: #28a745;
        background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
    }
    .timeline-item.completed {
        border-left-color: #6c757d;
        background: #f8f9fa;
    }
    .welcome-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 15px;
        padding: 30px;
        margin-bottom: 30px;
    }
    .chart-container {
        position: relative;
        height: 300px;
    }
    .doctor-card {
        text-align: center;
        padding: 20px;
        border-radius: 10px;
        background: white;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease;
    }
    .doctor-card:hover {
        transform: translateY(-3px);
    }
    .avatar {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        margin: 0 auto 15px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 24px;
        font-weight: bold;
    }
</style>
@endsection

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <!-- Welcome Card -->
            <div class="welcome-card">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h2 class="mb-2">Selamat Datang, {{ Auth::user()->name }}! 👋</h2>
                        <p class="mb-0">Kelola janji temu medis Anda dengan mudah dan tetap terhubung dengan dokter terpercaya.</p>
                    </div>
                    <div class="col-md-4 text-right">
                        <div class="d-none d-md-block">
                            <i class="fas fa-user-md" style="font-size: 4rem; opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- Statistics Cards -->
            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3>{{ $totalAppointments }}</h3>
                            <p>Total Janji Temu</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <a href="{{ route('appointments.index') }}" class="small-box-footer">
                            Lihat Semua <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3>{{ $upcomingAppointments }}</h3>
                            <p>Janji Temu Mendatang</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <a href="{{ route('appointments.index') }}?status=confirmed" class="small-box-footer">
                            Lihat Detail <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3>{{ $completedAppointments }}</h3>
                            <p>Janji Temu Selesai</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <a href="{{ route('appointments.index') }}?status=completed" class="small-box-footer">
                            Lihat Riwayat <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3>{{ $cancelledAppointments }}</h3>
                            <p>Janji Temu Dibatalkan</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-times-circle"></i>
                        </div>
                        <a href="{{ route('appointments.index') }}?status=cancelled" class="small-box-footer">
                            Lihat Detail <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Today's Appointments -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-calendar-day"></i> Janji Temu Hari Ini
                            </h3>
                        </div>
                        <div class="card-body">
                            @if($todayAppointments->count() > 0)
                                @foreach($todayAppointments as $appointment)
                                    <div class="timeline-item today">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="mb-1">{{ $appointment->doctor->user->name ?? 'N/A' }}</h6>
                                                <p class="mb-1 text-muted">{{ $appointment->specialty->name ?? 'Umum' }}</p>
                                                <small class="text-success">
                                                    <i class="fas fa-clock"></i> 
                                                    {{ \Carbon\Carbon::parse($appointment->start_time)->format('H:i') }} - 
                                                    {{ \Carbon\Carbon::parse($appointment->end_time)->format('H:i') }}
                                                </small>
                                            </div>
                                            <span class="badge badge-success">{{ ucfirst($appointment->status) }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="text-center py-4">
                                    <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Tidak ada janji temu hari ini</p>
                                    <a href="{{ route('appointments.create') }}" class="btn btn-primary">
                                        <i class="fas fa-plus"></i> Buat Janji Temu
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Upcoming Appointments -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-calendar-week"></i> Janji Temu Mendatang
                            </h3>
                        </div>
                        <div class="card-body">
                            @if($nextAppointments->count() > 0)
                                @foreach($nextAppointments as $appointment)
                                    <div class="timeline-item">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="mb-1">{{ $appointment->doctor->user->name ?? 'N/A' }}</h6>
                                                <p class="mb-1 text-muted">{{ $appointment->specialty->name ?? 'Umum' }}</p>
                                                <small class="text-primary">
                                                    <i class="fas fa-calendar"></i> 
                                                    {{ \Carbon\Carbon::parse($appointment->appointment_date)->format('d M Y') }}
                                                    <i class="fas fa-clock ml-2"></i> 
                                                    {{ \Carbon\Carbon::parse($appointment->start_time)->format('H:i') }}
                                                </small>
                                            </div>
                                            <span class="badge badge-primary">{{ ucfirst($appointment->status) }}</span>
                                        </div>
                                    </div>
                                @endforeach
                                <div class="text-center mt-3">
                                    <a href="{{ route('appointments.index') }}" class="btn btn-outline-primary btn-sm">
                                        Lihat Semua Janji Temu
                                    </a>
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="fas fa-calendar-plus fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Belum ada janji temu mendatang</p>
                                    <a href="{{ route('appointments.create') }}" class="btn btn-primary">
                                        <i class="fas fa-plus"></i> Jadwalkan Janji Temu
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Monthly Statistics Chart -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-chart-line"></i> Statistik Janji Temu (6 Bulan Terakhir)
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="appointmentChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Favorite Doctors -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-star"></i> Dokter Favorit
                            </h3>
                        </div>
                        <div class="card-body">
                            @if($favoriteDoctors->count() > 0)
                                @foreach($favoriteDoctors as $doctor)
                                    <div class="doctor-card mb-3">
                                        <div class="avatar">
                                            {{ substr($doctor->doctor->user->name ?? 'N', 0, 1) }}
                                        </div>
                                        <h6 class="mb-1">{{ $doctor->doctor->user->name ?? 'N/A' }}</h6>
                                        <p class="text-muted mb-2">{{ $doctor->doctor->specialty->name ?? 'Umum' }}</p>
                                        <small class="text-success">
                                            <i class="fas fa-calendar-check"></i> 
                                            {{ $doctor->appointment_count }} kali konsultasi
                                        </small>
                                    </div>
                                @endforeach
                            @else
                                <div class="text-center py-4">
                                    <i class="fas fa-user-md fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Belum ada dokter favorit</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Completed Appointments -->
            @if($recentCompletedAppointments->count() > 0)
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-history"></i> Riwayat Konsultasi Terakhir
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                @foreach($recentCompletedAppointments as $appointment)
                                    <div class="col-md-6 mb-3">
                                        <div class="timeline-item completed">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <h6 class="mb-1">{{ $appointment->doctor->user->name ?? 'N/A' }}</h6>
                                                    <p class="mb-1 text-muted">{{ $appointment->specialty->name ?? 'Umum' }}</p>
                                                    <small class="text-muted">
                                                        <i class="fas fa-calendar"></i> 
                                                        {{ \Carbon\Carbon::parse($appointment->appointment_date)->format('d M Y') }}
                                                    </small>
                                                    @if($appointment->feedback)
                                                        <div class="mt-2">
                                                            <small class="text-warning">
                                                                @for($i = 1; $i <= 5; $i++)
                                                                    @if($i <= $appointment->feedback->rating)
                                                                        ★
                                                                    @else
                                                                        ☆
                                                                    @endif
                                                                @endfor
                                                                ({{ $appointment->feedback->rating }}/5)
                                                            </small>
                                                        </div>
                                                    @endif
                                                </div>
                                                <span class="badge badge-success">Selesai</span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Quick Actions -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-rocket"></i> Aksi Cepat
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <a href="{{ route('appointments.create') }}" class="btn btn-primary btn-block">
                                        <i class="fas fa-plus-circle"></i><br>
                                        Buat Janji Temu
                                    </a>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <a href="{{ route('appointments.index') }}" class="btn btn-info btn-block">
                                        <i class="fas fa-list"></i><br>
                                        Lihat Semua Janji Temu
                                    </a>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <a href="{{ route('patient.profile.edit') }}" class="btn btn-warning btn-block">
                                        <i class="fas fa-user-edit"></i><br>
                                        Edit Profil
                                    </a>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <a href="{{ route('appointments.index') }}?status=completed" class="btn btn-success btn-block">
                                        <i class="fas fa-history"></i><br>
                                        Riwayat Konsultasi
                                    </a>
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

@section('customJs')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    // Chart Configuration
    const ctx = document.getElementById('appointmentChart').getContext('2d');
    const monthlyData = @json($monthlyStats);
    
    const labels = monthlyData.map(item => item.month);
    const appointmentsData = monthlyData.map(item => item.appointments);
    const completedData = monthlyData.map(item => item.completed);

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Total Janji Temu',
                data: appointmentsData,
                borderColor: 'rgb(54, 162, 235)',
                backgroundColor: 'rgba(54, 162, 235, 0.1)',
                tension: 0.4,
                fill: true
            }, {
                label: 'Janji Temu Selesai',
                data: completedData,
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'Trend Janji Temu'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });

    // Auto-refresh untuk appointment hari ini setiap 5 menit
    setInterval(function() {
        // Anda bisa menambahkan AJAX call untuk refresh data real-time
        console.log('Auto-refresh appointment data...');
    }, 300000); // 5 menit
});
</script>
@endsection