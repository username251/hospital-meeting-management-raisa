@extends('staff.layout')

@section('content')

<div class="content-wrapper">

    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Dashboard Staff</h1>
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

    <section class="content">
        <div class="container-fluid">

            {{-- Baris untuk Statistik Utama Hari Ini --}}
            <div class="row">
                {{-- Janji Temu Hari Ini (Total) --}}
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3>{{ $appointmentsToday }}</h3>
                            <p>Total Janji Temu Hari Ini</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-calendar-day"></i>
                        </div>
                        <a href="{{ route('staff.appointments.index') }}" class="small-box-footer">Lihat Semua <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>

                {{-- Pasien di Antrean --}}
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            {{-- Menggunakan variabel $currentQueue yang lebih akurat --}}
                            <h3>{{ $currentQueue }}</h3>
                            <p>Pasien Dalam Antrean</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <a href="{{ route('staff.queue.index') ?? '#' }}" class="small-box-footer">Lihat Antrean <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>

                {{-- Janji Temu Selesai Hari Ini --}}
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3>{{ $appointmentsTodayCompleted }}</h3>
                            <p>Selesai Konsultasi Hari Ini</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <a href="{{ route('staff.appointments.index') }}" class="small-box-footer">Lihat Riwayat <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                
                {{-- Janji Temu Belum Check-in --}}
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-secondary">
                        <div class="inner">
                            <h3>{{ $appointmentsTodayPending }}</h3>
                            <p>Belum Check-in Hari Ini</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-hourglass-start"></i>
                        </div>
                        <a href="{{ route('staff.appointments.index') }}" class="small-box-footer">Lihat Detail <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
            </div>

            {{-- Baris untuk Konten Utama (Tabel dan Statistik Tambahan) --}}
            <div class="row">
                <div class="col-lg-8">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-user-clock mr-2"></i>Janji Temu Akan Datang Hari Ini</h3>
                        </div>
                        <div class="card-body p-0">
                           {{-- resources/views/staff/index.blade.php (bagian tabel) --}}

<table class="table table-hover">
    <thead>
        <tr>
            <th>Waktu</th>
            <th>Pasien</th>
            <th>Dokter</th>
            <th class="text-center">Status</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($upcomingAppointments as $appointment)
        <tr>
            {{-- DIUBAH: dari $appointment->appointment_time menjadi $appointment->start_time --}}
            <td><strong>{{ \Carbon\Carbon::parse($appointment->start_time)->format('H:i') }}</strong></td>
            <td>{{ $appointment->patient->name ?? 'N/A' }}</td>
            <td>{{ $appointment->doctor->user->name ?? 'N/A' }}</td> {{-- Asumsi nama dokter ada di relasi user --}}
            <td class="text-center">
                @switch($appointment->status)
                    @case('pending')
                        <span class="badge bg-secondary">Pending</span>
                        @break
                    @case('waiting')
                        <span class="badge bg-warning">Waiting</span>
                        @break
                    @case('in-consultation')
                        <span class="badge bg-info">In Consultation</span>
                        @break
                    @case('check-in')
                        <span class="badge bg-primary">Checked In</span>
                        @break
                    @default
                        <span class="badge bg-light">{{ ucfirst($appointment->status) }}</span>
                @endswitch
            </td>
            <td>
                <a href="#" class="btn btn-sm btn-outline-primary">Detail</a>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="5" class="text-center py-4">
                Tidak ada janji temu yang akan datang hari ini.
            </td>
        </tr>
        @endforelse
    </tbody>
</table>
                        </div>
                        <div class="card-footer text-center">
                           <a href="{{ route('staff.appointments.index') }}">Lihat Semua Jadwal Janji Temu</a>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    {{-- Total Pasien --}}
                    <div class="info-box mb-3">
                        <span class="info-box-icon bg-info elevation-1"><i class="fas fa-hospital-user"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Pasien Terdaftar</span>
                            <span class="info-box-number">{{ $totalPatients }}</span>
                        </div>
                    </div>
                    {{-- Total Dokter --}}
                    <div class="info-box mb-3">
                        <span class="info-box-icon bg-success elevation-1"><i class="fas fa-user-md"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Dokter</span>
                            <span class="info-box-number">{{ $totalDoctors }}</span>
                        </div>
                    </div>
                     {{-- Janji Temu Menunggu (Total) --}}
                    <div class="info-box mb-3">
                        <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-sync-alt"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Janji Temu Tertunda</span>
                            <span class="info-box-number">{{ $totalPendingAppointments }}</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>

</div>

@endsection