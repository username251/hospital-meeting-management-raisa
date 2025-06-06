@extends('admin.layout') {{-- Pastikan path ini benar ke layout utama Anda --}}

@section('content')

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Manajemen Janji Temu</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Manajemen Janji Temu</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
             <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Daftar Janji Temu Saya</h3>
                    <div class="card-tools">
                        <a href="{{ route('appointments.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Jadwalkan Janji Temu Baru
                        </a>
                    </div>
                </div>
                <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Pasien</th>
                                <th>Dokter</th>
                                <th>Spesialisasi</th>
                                <th>Tanggal</th>
                                <th>Waktu</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($appointments as $appointment)
                                <tr>
                                    <td>{{ $appointment->id }}</td>
                                    {{-- Mengakses nama pasien melalui relasi user --}}
                                    <td>{{ $appointment->patient->user->name ?? 'N/A' }}</td>
                                    {{-- Mengakses nama dokter melalui relasi user --}}
                                    <td>{{ $appointment->doctor->user->name ?? 'N/A' }}</td>
                                    {{-- Mengakses nama spesialisasi --}}
                                    <td>{{ $appointment->specialty->name ?? 'N/A' }}</td>
                                    <td>{{ \Carbon\Carbon::parse($appointment->appointment_date)->format('d M Y') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($appointment->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($appointment->end_time)->format('H:i') }}</td>
                                    <td>
                                        @php
                                            $statusClass = '';
                                            switch ($appointment->status) {
                                                case 'pending': $statusClass = 'badge badge-secondary'; break;
                                                case 'confirmed': $statusClass = 'badge badge-info'; break;
                                                case 'completed': $statusClass = 'badge badge-success'; break;
                                                case 'cancelled': $statusClass = 'badge badge-danger'; break;
                                                case 'rescheduled': $statusClass = 'badge badge-warning'; break;
                                            }
                                        @endphp
                                        <span class="{{ $statusClass }}">{{ ucfirst($appointment->status) }}</span>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.appointments.show', $appointment->id) }}" class="btn btn-info btn-sm">Lihat</a>
                                        <a href="{{ route('admin.appointments.edit', $appointment->id) }}" class="btn btn-warning btn-sm">Edit</a>
                                        <form action="{{ route('admin.appointments.destroy', $appointment->id) }}" method="POST" style="display:inline-block;">
                                            @csrf
                                            {{-- @method('DELETE') --}} {{-- Hapus ini jika rute Anda POST untuk delete --}}
                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus janji temu ini?');">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center">Tidak ada janji temu yang tersedia.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    {{ $appointments->links('pagination::bootstrap-4') }} {{-- Pastikan Anda punya paginasi yang disiapkan --}}
                </div>
            </div>
        </div>
    </section>
</div>
@endsection