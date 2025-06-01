@extends('patient.layout') {{-- Sesuaikan path ini --}}

@section('customCss')
{{-- Tambahkan CSS kustom Anda di sini jika ada --}}
@endsection

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
                        <li class="breadcrumb-item"><a href="{{ route('patient.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('patient.appointments.index') }}">Janji Temu Saya</a></li>
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
                    <h3 class="card-title">Informasi Lengkap Janji Temu #{{ $appointment->id }}</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <dl class="row">
                                <dt class="col-sm-4">Dokter:</dt>
                                <dd class="col-sm-8">{{ $appointment->doctor->user->name ?? 'N/A' }}</dd>

                                <dt class="col-sm-4">Spesialisasi:</dt>
                                <dd class="col-sm-8">{{ $appointment->specialty?->name ?? 'Tidak Ada Spesialisasi' }}</dd>

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
                                            case 'scheduled': $statusClass = 'badge badge-primary'; break;
                                            case 'check-in': $statusClass = 'badge badge-dark'; break;
                                            case 'waiting': $statusClass = 'badge badge-warning'; break;
                                            case 'in-consultation': $statusClass = 'badge badge-info'; break;
                                            case 'no-show': $statusClass = 'badge badge-light'; break;
                                        }
                                    @endphp
                                    <span class="{{ $statusClass }}">{{ ucfirst($appointment->status) }}</span>
                                </dd>
                            </dl>
                        </div>
                        <div class="col-md-6">
                            <dl class="row">
                                <dt class="col-sm-4">Alasan:</dt>
                                <dd class="col-sm-8">{{ $appointment->reason ?? 'Tidak Ada' }}</dd>

                                <dt class="col-sm-4">Catatan (dari dokter):</dt>
                                <dd class="col-sm-8">{{ $appointment->notes ?? 'Tidak Ada Catatan' }}</dd>

                                <dt class="col-sm-4">Dibuat Pada:</dt>
                                <dd class="col-sm-8">{{ $appointment->created_at->format('d M Y H:i') }}</dd>

                                <dt class="col-sm-4">Terakhir Diperbarui:</dt>
                                <dd class="col-sm-8">{{ $appointment->updated_at->format('d M Y H:i') }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="{{ route('appointments.index') }}" class="btn btn-secondary">Kembali ke Daftar</a>
                    @if (in_array($appointment->status, ['pending', 'confirmed', 'scheduled']))
                        <form action="{{ route('patient.appointments.cancel', $appointment->id) }}" method="POST" style="display:inline-block;" class="ml-2">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Apakah Anda yakin ingin membatalkan janji temu ini?');">Batalkan Janji Temu</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </section>
</div>
@endsection