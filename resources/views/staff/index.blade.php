@extends('staff.layout')

@section('content')

    <div class="content-wrapper">

        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">Dashboard</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            {{-- Menggunakan rute name 'staff.dashboard' karena ini adalah dashboard staff --}}
                            <li class="breadcrumb-item"><a href="{{ route('home.dashboard') }}">Home</a></li>
                            <li class="breadcrumb-item active">Dashboard v1</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">

                <div class="row">
                    {{-- Total Pasien Terdaftar --}}
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-info">
                            <div class="inner">
                                <h3>{{ $totalPatients }}</h3>
                                <p>Total Pasien Terdaftar</p>
                            </div>
                            <div class="icon">
                                <i class="ion ion-person-add"></i> {{-- Icon pasien --}}
                            </div>
                            <a href="{{ route('staff.patients.index') }}" class="small-box-footer">Lihat Semua <i class="fas fa-arrow-circle-right"></i></a>
                        </div>
                    </div>

                    {{-- Total Dokter --}}
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-success">
                            <div class="inner">
                                <h3>{{ $totalDoctors }}</h3>
                                <p>Total Dokter</p>
                            </div>
                            <div class="icon">
                                <i class="ion ion-medkit"></i> {{-- Icon medis/dokter --}}
                            </div>
                            {{-- Asumsi ada rute untuk manajemen dokter, jika belum ada, pakai '#' dulu --}}
                            <a href="#" class="small-box-footer">Lihat Semua <i class="fas fa-arrow-circle-right"></i></a>
                        </div>
                    </div>

                    {{-- Janji Temu Hari Ini --}}
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-warning">
                            <div class="inner">
                                <h3>{{ $appointmentsToday }}</h3>
                                <p>Janji Temu Hari Ini</p>
                            </div>
                            <div class="icon">
                                <i class="ion ion-calendar"></i> {{-- Icon kalender --}}
                            </div>
                            {{-- Asumsi ada rute untuk melihat janji temu --}}
                            <a href="{{ route('staff.appointments.index') }}" class="small-box-footer">Lihat Jadwal <i class="fas fa-arrow-circle-right"></i></a>
                        </div>
                    </div>

                    {{-- Janji Temu Menunggu (misal: pending, waiting, check-in) --}}
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-danger">
                            <div class="inner">
                                <h3>{{ $pendingAppointments }}</h3>
                                <p>Janji Temu Menunggu</p>
                            </div>
                            <div class="icon">
                                <i class="ion ion-clock"></i> {{-- Icon jam/menunggu --}}
                            </div>
                            {{-- Asumsi ada rute untuk antrean pasien --}}
                            <a href="{{ route('staff.queue.index') ?? '#' }}" class="small-box-footer">Lihat Antrean <i class="fas fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                </div>
                {{-- Tambahan kotak statistik untuk detail lebih lanjut --}}
                <div class="row">
                    {{-- Pasien di Antrean Hari Ini (Status: waiting atau in-consultation) --}}
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-primary">
                            <div class="inner">
                                <h3>{{ $appointmentsTodayWaiting }}</h3>
                                <p>Pasien di Antrean</p>
                            </div>
                            <div class="icon">
                                <i class="ion ion-clipboard"></i> {{-- Icon clipboard/list --}}
                            </div>
                            <a href="{{ route('staff.queue.index') ?? '#' }}" class="small-box-footer">Detail Antrean <i class="fas fa-arrow-circle-right"></i></a>
                        </div>
                    </div>

                    {{-- Janji Temu Selesai Hari Ini --}}
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-success">
                            <div class="inner">
                                <h3>{{ $appointmentsTodayCompleted }}</h3>
                                <p>Janji Temu Selesai Hari Ini</p>
                            </div>
                            <div class="icon">
                                <i class="ion ion-checkmark-circled"></i> {{-- Icon centang/selesai --}}
                            </div>
                            <a href="{{ route('staff.appointments.index') }}" class="small-box-footer">Lihat Detail <i class="fas fa-arrow-circle-right"></i></a>
                        </div>
                    </div>

                    {{-- Janji Temu Belum Dikonsumsi Hari Ini (Status: pending) --}}
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-secondary">
                            <div class="inner">
                                <h3>{{ $appointmentsTodayPending }}</h3>
                                <p>Janji Temu Belum Dikonsumsi</p>
                            </div>
                            <div class="icon">
                                <i class="ion ion-hourglass"></i> {{-- Icon jam pasir/pending --}}
                            </div>
                            <a href="{{ route('staff.appointments.index') }}" class="small-box-footer">Lihat Detail <i class="fas fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                </div>

                {{-- Anda juga bisa menambahkan grafik atau tabel sederhana di sini --}}
                {{-- Contoh: Tabel janji temu yang akan datang --}}

            </div>
        </section>

    </div>

@endsection