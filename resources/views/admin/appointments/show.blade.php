@extends('admin.layout') {{-- Pastikan path ini benar ke layout utama Anda --}}

@section('content')

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Detail Janji Temu</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.appointments.index') }}">Manajemen Janji Temu</a></li>
                        <li class="breadcrumb-item active">Detail Janji Temu</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Informasi Janji Temu</h3>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <strong>ID:</strong>
                    <p>{{ $appointment->id }}</p>
                </div>
                <div class="form-group">
                    <strong>Pasien:</strong>
                    {{-- Mengakses nama pasien melalui relasi user --}}
                    <p>{{ $appointment->patient->user->name ?? '-' }}</p>
                </div>
                <div class="form-group">
                    <strong>Dokter:</strong>
                    {{-- Mengakses nama dokter melalui relasi user --}}
                    <p>{{ $appointment->doctor->user->name ?? '-' }}</p>
                </div>
                <div class="form-group">
                    <strong>Spesialisasi:</strong>
                    {{-- Mengakses nama spesialisasi --}}
                    <p>{{ $appointment->specialty->name ?? '-' }}</p>
                </div>
                <div class="form-group">
                    <strong>Tanggal Janji Temu:</strong>
                    <p>{{ \Carbon\Carbon::parse($appointment->appointment_date)->format('d M Y') }}</p>
                </div>
                <div class="form-group">
                    <strong>Waktu Mulai:</strong>
                    <p>{{ \Carbon\Carbon::parse($appointment->start_time)->format('H:i') }}</p>
                </div>
                <div class="form-group">
                    <strong>Waktu Selesai:</strong>
                    <p>{{ \Carbon\Carbon::parse($appointment->end_time)->format('H:i') }}</p>
                </div>
                <div class="form-group">
                    <strong>Alasan:</strong>
                    <p>{{ $appointment->reason ?? '-' }}</p>
                </div>
                <div class="form-group">
                    <strong>Status:</strong>
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
                    <p><span class="{{ $statusClass }}">{{ ucfirst($appointment->status) }}</span></p>
                </div>
                <div class="form-group">
                    <strong>Catatan:</strong>
                    <p>{{ $appointment->notes ?? '-' }}</p>
                </div>
                <div class="form-group">
                    <strong>Dibuat Pada:</strong>
                    <p>{{ $appointment->created_at->format('d M Y H:i') }}</p>
                </div>
                <div class="form-group">
                    <strong>Diperbarui Pada:</strong>
                    <p>{{ $appointment->updated_at->format('d M Y H:i') }}</p>
                </div>

                <div class="mt-4">
                    <a href="{{ route('admin.appointments.edit', $appointment->id) }}" class="btn btn-warning">Edit</a> {{-- Ganti ke btn-warning --}}
                    <form action="{{ route('admin.appointments.destroy', $appointment->id) }}" method="POST" style="display:inline-block;">
                        @csrf
                        {{-- @method('DELETE') --}} {{-- Hapus ini jika rute Anda POST untuk delete --}}
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus janji temu ini?');">Hapus</button>
                    </form>
                    <a href="{{ route('admin.appointments.index') }}" class="btn btn-secondary">Kembali</a>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection