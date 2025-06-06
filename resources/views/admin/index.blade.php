@extends('admin.layout')

@section('content')
<div class="content-wrapper">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Dashboard Admin</h1>
                    <p class="text-muted">Selamat datang di panel administrasi sistem manajemen janji temu</p>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('home.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active">Dashboard</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            
            <!-- Statistics Cards -->
            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3>{{ $totalPatients }}</h3>
                            <p>Total Pasien</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <a href="#" class="small-box-footer">
                            Lihat Detail <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3>{{ $totalDoctors }}</h3>
                            <p>Total Dokter</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-user-md"></i>
                        </div>
                        <a href="#" class="small-box-footer">
                            Lihat Detail <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3>{{ $totalAppointments }}</h3>
                            <p>Total Janji Temu</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <a href="#" class="small-box-footer">
                            Lihat Detail <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3>{{ $pendingAppointments }}</h3>
                            <p>Janji Temu Pending</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <a href="#" class="small-box-footer">
                            Lihat Detail <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Additional Statistics -->
            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="info-box">
                        <span class="info-box-icon bg-purple"><i class="fas fa-calendar-check"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Janji Temu Hari Ini</span>
                            <span class="info-box-number">{{ $todayAppointments }}</span>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="info-box">
                        <span class="info-box-icon bg-teal"><i class="fas fa-check-circle"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Janji Temu Selesai</span>
                            <span class="info-box-number">{{ $completedAppointments }}</span>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="info-box">
                        <span class="info-box-icon bg-orange"><i class="fas fa-user-plus"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Pasien Baru Bulan Ini</span>
                            <span class="info-box-number">{{ $monthlyNewPatients }}</span>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="info-box">
                        <span class="info-box-icon bg-cyan"><i class="fas fa-stethoscope"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Spesialisasi</span>
                            <span class="info-box-number">{{ $totalSpecialties }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts and Tables Row -->
            <div class="row">
                <!-- Appointment Status Chart -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Status Janji Temu</h3>
                        </div>
                        <div class="card-body">
                            <canvas id="appointmentStatusChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Monthly Trend Chart -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Trend Janji Temu 6 Bulan Terakhir</h3>
                        </div>
                        <div class="card-body">
                            <canvas id="monthlyTrendChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activities -->
            <div class="row">
                <!-- Recent Appointments -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Janji Temu Terbaru</h3>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Pasien</th>
                                            <th>Dokter</th>
                                            <th>Tanggal</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($recentAppointments as $appointment)
                                        <tr>
                                            <td>{{ $appointment->patient->user->name }}</td>
                                            <td>{{ $appointment->doctor->user->name }}</td>
                                            <td>{{ \Carbon\Carbon::parse($appointment->appointment_date)->format('d/m/Y') }}</td>
                                            <td>
                                                <span class="badge badge-{{ 
                                                    $appointment->status == 'completed' ? 'success' : 
                                                    ($appointment->status == 'pending' ? 'warning' : 
                                                    ($appointment->status == 'cancelled' ? 'danger' : 'info')) 
                                                }}">
                                                    {{ ucfirst($appointment->status) }}
                                                </span>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top Rated Doctors -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Dokter Berperingkat Teratas</h3>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Dokter</th>
                                            <th>Rating</th>
                                            <th>Reviews</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($doctorRatings as $doctor)
                                        <tr>
                                            <td>{{ $doctor->user->name }}</td>
                                            <td>
                                                @if($doctor->avg_rating)
                                                    <div class="rating">
                                                        @for($i = 1; $i <= 5; $i++)
                                                            @if($i <= round($doctor->avg_rating))
                                                                <i class="fas fa-star text-warning"></i>
                                                            @else
                                                                <i class="far fa-star text-muted"></i>
                                                            @endif
                                                        @endfor
                                                        <span class="ml-1">{{ number_format($doctor->avg_rating, 1) }}</span>
                                                    </div>
                                                @else
                                                    <span class="text-muted">Belum ada rating</span>
                                                @endif
                                            </td>
                                            <td>{{ $doctor->total_reviews }} review(s)</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Feedback -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Feedback Terbaru</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                @foreach($recentFeedback as $feedback)
                                <div class="col-md-6 mb-3">
                                    <div class="card card-outline card-primary">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <div>
                                                    <strong>{{ $feedback->patient->user->name }}</strong>
                                                    <br>
                                                    <small class="text-muted">untuk Dr. {{ $feedback->doctor->user->name }}</small>
                                                </div>
                                                <div class="rating">
                                                    @for($i = 1; $i <= 5; $i++)
                                                        @if($i <= $feedback->rating)
                                                            <i class="fas fa-star text-warning"></i>
                                                        @else
                                                            <i class="far fa-star text-muted"></i>
                                                        @endif
                                                    @endfor
                                                </div>
                                            </div>
                                            <p class="mb-1">{{ $feedback->comment }}</p>
                                            <small class="text-muted">{{ $feedback->created_at->diffForHumans() }}</small>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Appointment Status Chart
    const statusCtx = document.getElementById('appointmentStatusChart').getContext('2d');
    const statusData = @json($appointmentsByStatus);
    
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: Object.keys(statusData).map(key => key.charAt(0).toUpperCase() + key.slice(1)),
            datasets: [{
                data: Object.values(statusData),
                backgroundColor: [
                    '#ffc107', // pending - warning
                    '#28a745', // completed - success  
                    '#dc3545', // cancelled - danger
                    '#17a2b8', // confirmed - info
                    '#6c757d', // rescheduled - secondary
                    '#fd7e14', // scheduled - orange
                    '#20c997', // check-in - teal
                    '#6f42c1', // waiting - purple
                    '#e83e8c', // in-consultation - pink
                    '#343a40'  // no-show - dark
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                }
            }
        }
    });

    // Monthly Trend Chart
    const trendCtx = document.getElementById('monthlyTrendChart').getContext('2d');
    const trendData = @json($monthlyTrend);
    
    new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: trendData.map(item => item.month),
            datasets: [{
                label: 'Janji Temu',
                data: trendData.map(item => item.count),
                borderColor: '#007bff',
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
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
</script>
@endpush
@endsection