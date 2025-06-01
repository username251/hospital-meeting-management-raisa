@extends('doctor.layout') {{-- Memanggil layout dasar untuk dokter --}}

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Dashboard Dokter</h1>
                </div><div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('doctor.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active">Dashboard</li>
                    </ol>
                </div></div></div></div>
    <section class="content">
        <div class="container-fluid">
            {{-- Bagian untuk menampilkan pesan sukses atau error --}}
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Janji Temu Hari Ini ({{ \Carbon\Carbon::today()->format('d F Y') }})</h3>
                </div>
                <div class="card-body p-0">
                    @if($todayAppointments->isEmpty())
                        <div class="alert alert-info m-3">Tidak ada janji temu hari ini.</div>
                    @else
                        <table class="table table-striped projects">
                            <thead>
                                <tr>
                                    <th style="width: 10%">Waktu</th>
                                    <th style="width: 25%">Nama Pasien</th>
                                    <th style="width: 20%">Status</th>
                                    <th style="width: 45%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($todayAppointments as $appointment)
                                    @php
                                        // Highlight janji temu yang sedang berlangsung atau akan datang
                                        $currentTime = \Carbon\Carbon::now();
                                        // Menggunakan kolom 'appointment_date' dan 'start_time' dari database Anda
                                        $appointmentDateTime = \Carbon\Carbon::parse($appointment->appointment_date->format('Y-m-d') . ' ' . $appointment->start_time->format('H:i:s'));

                                        $rowClass = '';
                                        // Asumsi `consultation_duration` ada di model Doctor atau default 30 menit
                                        // Jika `consultation_duration` tidak ada di model Doctor, Anda bisa hardcode nilai di sini.
                                        $consultationDuration = $appointment->doctor->consultation_duration ?? 30;


                                        // Status 'in_consultation' berarti sedang berlangsung
                                        if ($appointment->status == 'in_consultation') {
                                            $rowClass = 'table-warning'; // Sedang berlangsung
                                        }
                                        // Janji temu yang akan datang dalam 30 menit dan statusnya 'scheduled' atau 'confirmed'
                                        elseif ($appointmentDateTime->isFuture() && $appointmentDateTime->diffInMinutes($currentTime) <= 30 && ($appointment->status == 'scheduled' || $appointment->status == 'confirmed')) {
                                            $rowClass = 'table-info'; // Akan datang
                                        }
                                        // Janji temu yang dibatalkan atau tidak hadir
                                        elseif ($appointment->status == 'cancelled' || $appointment->status == 'missed') {
                                            $rowClass = 'table-danger'; // Dibatalkan/Tidak Hadir
                                        }
                                        // Janji temu yang sudah selesai
                                        elseif ($appointment->status == 'completed') {
                                            $rowClass = 'table-success'; // Selesai
                                        }
                                    @endphp
                                    <tr class="{{ $rowClass }}">
                                        {{-- Menampilkan waktu mulai dari kolom `start_time` --}}
                                        <td>{{ $appointment->start_time->format('H:i') }}</td>
                                        <td>
                                            @if($appointment->patient && $appointment->patient->user)
                                                {{ $appointment->patient->user->name }}
                                            @else
                                                Pasien Tidak Ditemukan
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge {{
                                                $appointment->status == 'scheduled' ? 'badge-secondary' :
                                                ($appointment->status == 'confirmed' ? 'badge-primary' :
                                                ($appointment->status == 'completed' ? 'badge-success' :
                                                ($appointment->status == 'cancelled' ? 'badge-danger' :
                                                ($appointment->status == 'missed' ? 'badge-warning' :
                                                ($appointment->status == 'in_consultation' ? 'badge-info' : '')))))
                                            }}">
                                                {{ ucfirst(str_replace('_', ' ', $appointment->status)) }}
                                            </span>
                                        </td>
                                        <td class="project-actions">
                                            {{-- Tombol "Mulai Konsultasi" hanya muncul jika status 'scheduled' atau 'confirmed' --}}
                                            @if($appointment->status == 'scheduled' || $appointment->status == 'confirmed')
                                                <form action="{{ route('doctor.appointments.updateStatus', $appointment->id) }}" method="POST" style="display: inline-block;">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="status" value="in_consultation">
                                                    <button type="submit" class="btn btn-primary btn-sm">
                                                        <i class="fas fa-play-circle"></i> Mulai Konsultasi
                                                    </button>
                                                </form>
                                            @endif

                                            {{-- Tombol "Tandai Selesai" dan "Tandai Tidak Hadir" muncul jika status 'in_consultation' --}}
                                            @if($appointment->status == 'in_consultation')
                                                <form action="{{ route('doctor.appointments.updateStatus', $appointment->id) }}" method="POST" style="display: inline-block;">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="status" value="completed">
                                                    <button type="submit" class="btn btn-success btn-sm">
                                                        <i class="fas fa-check-circle"></i> Tandai Selesai
                                                    </button>
                                                </form>
                                                <form action="{{ route('doctor.appointments.updateStatus', $appointment->id) }}" method="POST" style="display: inline-block;" onsubmit="return confirm('Apakah Anda yakin ingin menandai pasien ini tidak hadir?');">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="status" value="missed">
                                                    <button type="submit" class="btn btn-warning btn-sm">
                                                        <i class="fas fa-user-times"></i> Tandai Tidak Hadir
                                                    </button>
                                                </form>
                                            @endif

                                            {{-- Anda bisa menambahkan tombol "Detail" di sini jika ada halaman detail janji temu --}}
                                            {{-- @if($appointment->status != 'completed' && $appointment->status != 'cancelled' && $appointment->status != 'missed')
                                                 <a class="btn btn-info btn-sm" href="{{ route('doctor.appointments.show', $appointment->id) }}">
                                                    <i class="fas fa-folder"></i> Detail
                                                </a>
                                            @endif --}}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
            {{-- Anda bisa menambahkan ringkasan lain, seperti jumlah janji temu yang akan datang, dll. --}}

        </div></section>
    </div>
@endsection