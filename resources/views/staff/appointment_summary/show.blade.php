@extends('staff.layout') {{-- Pastikan path ini benar ke layout utama Staff Anda --}}

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
                        <li class="breadcrumb-item"><a href="{{ route('staff.index') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('staff.appointments.index') }}">Manajemen Janji Temu</a></li>
                        <li class="breadcrumb-item active">Detail</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Informasi Janji Temu #{{ $appointment->id }}</h3>
                    <div class="card-tools">
                        <a href="{{ route('staff.appointments.edit', $appointment->id) }}" class="btn btn-warning btn-sm">Edit</a>
                        <form action="{{ route('staff.appointments.destroy', $appointment->id) }}" method="POST" style="display:inline-block;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus janji temu ini?');">Hapus</button>
                        </form>
                        <a href="{{ route('staff.appointments.index') }}" class="btn btn-secondary btn-sm">Kembali ke Daftar</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <dl class="row">
                                <dt class="col-sm-4">ID:</dt>
                                <dd class="col-sm-8">{{ $appointment->id }}</dd>

                                <dt class="col-sm-4">Pasien:</dt>
                                <dd class="col-sm-8">{{ $appointment->patient->user->name ?? 'N/A' }}</dd>

                                <dt class="col-sm-4">Dokter:</dt>
                                <dd class="col-sm-8">{{ $appointment->doctor->user->name ?? 'N/A' }}</dd>

                                <dt class="col-sm-4">Spesialisasi:</dt>
                                <dd class="col-sm-8">{{ $appointment->specialty->name ?? 'N/A' }}</dd>

                                <dt class="col-sm-4">Tanggal:</dt>
                                <dd class="col-sm-8">{{ \Carbon\Carbon::parse($appointment->appointment_date)->format('d M Y') }}</dd>

                                <dt class="col-sm-4">Waktu:</dt>
                                <dd class="col-sm-8">{{ \Carbon\Carbon::parse($appointment->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($appointment->end_time)->format('H:i') }}</dd>

                                <dt class="col-sm-4">Status:</dt>
                                <dd class="col-sm-8">
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
                                </dd>
                            </dl>
                        </div>
                        <div class="col-md-6">
                            <dl class="row">
                                <dt class="col-sm-4">Alasan:</dt>
                                <dd class="col-sm-8">{{ $appointment->reason ?? '-' }}</dd>

                                <dt class="col-sm-4">Catatan:</dt>
                                <dd class="col-sm-8">{{ $appointment->notes ?? '-' }}</dd>

                                <dt class="col-sm-4">Dibuat Pada:</dt>
                                <dd class="col-sm-8">{{ \Carbon\Carbon::parse($appointment->created_at)->format('d M Y H:i') }}</dd>

                                <dt class="col-sm-4">Terakhir Diperbarui:</dt>
                                <dd class="col-sm-8">{{ \Carbon\Carbon::parse($appointment->updated_at)->format('d M Y H:i') }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection