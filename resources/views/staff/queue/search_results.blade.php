@extends('staff.layout') {{-- Pastikan path ini benar --}}

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Hasil Pencarian Antrean</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('staff.index') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('staff.queue.index') }}">Antrean Pasien</a></li>
                        <li class="breadcrumb-item active">Hasil Pencarian</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Hasil untuk "{{ $query }}"</h3>
                    <div class="card-tools">
                        <a href="{{ route('staff.queue.index') }}" class="btn btn-secondary btn-sm">Kembali ke Antrean</a>
                    </div>
                </div>
                <div class="card-body">
                    @if($searchResults->isEmpty())
                        <p class="text-center">Tidak ditemukan janji temu untuk pencarian "{{ $query }}" hari ini.</p>
                    @else
                        <div class="row">
                            @foreach($searchResults as $appointment)
                                <div class="col-md-6 mb-3">
                                    <div class="card card-queue-item status-{{ $appointment->status }} h-100">
                                        <div class="card-body d-flex flex-column">
                                            <h5 class="card-title">{{ $appointment->patient->user->name ?? 'N/A' }}</h5>
                                            <h6 class="card-subtitle mb-2 text-muted">Dr. {{ $appointment->doctor->user->name ?? 'N/A' }} ({{ $appointment->specialty->name ?? 'N/A' }})</h6>
                                            <p class="card-text mb-1">Waktu: **{{ \Carbon\Carbon::parse($appointment->start_time)->format('H:i') }}**</p>
                                            <p class="card-text mb-1">Status: <span class="badge badge-pill badge-{{ $appointment->status }}">{{ ucfirst($appointment->status) }}</span></p>
                                            <div class="mt-auto d-flex justify-content-between align-items-center">
                                                <small class="text-muted">ID Janji: {{ $appointment->id }}</small>
                                                <div>
                                                    <a href="{{ route('staff.appointments.show', $appointment->id) }}" class="btn btn-info btn-sm ml-2">Detail Janji</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
</div>
@endsection