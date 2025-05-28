@extends('staff.layout') {{-- Pastikan path ini benar ke layout utama Staff Anda --}}

@section('customCss')
{{-- Optional: Jika menggunakan DataTables atau styling khusus untuk antrean --}}
<style>
    .card-queue-item {
        border-left: 5px solid;
        border-radius: 0.25rem;
    }
    .status-check-in { border-color: #007bff; } /* blue */
    .status-waiting { border-color: #ffc107; } /* yellow */
    .status-in-consultation { border-color: #28a745; } /* green */
    .status-completed { border-color: #6c757d; } /* grey */
    .status-cancelled, .status-no-show { border-color: #dc3545; } /* red */

    .badge-check-in { background-color: #007bff; color: white; }
    .badge-waiting { background-color: #ffc107; color: black; }
    .badge-in-consultation { background-color: #28a745; color: white; }
    .badge-completed { background-color: #6c757d; color: white; }
    .badge-cancelled, .badge-no-show { background-color: #dc3545; color: white; }

    /* Gaya untuk pasien yang sedang dipanggil */
    .highlight-patient {
        background-color: #e0f7fa; /* Light cyan */
        border: 2px solid #00bcd4; /* Cyan border */
        box-shadow: 0 0 10px rgba(0, 188, 212, 0.5);
    }
</style>
@endsection

@section('content')

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Antrean Pasien Hari Ini</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('staff.index') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Antrean Pasien</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
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

            {{-- Bagian Pencarian Universal --}}
            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title">Cari Pasien / Janji Temu</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('staff.queue.search') }}" method="GET">
                        <div class="input-group">
                            <input type="text" name="query" class="form-control" placeholder="Cari Pasien, Dokter, atau ID Janji Temu..." value="{{ request('query') }}">
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-info"><i class="fas fa-search"></i> Cari</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Pasien Selanjutnya yang Akan Dipanggil --}}
            @if($nextPatient)
                <div class="card card-outline card-primary highlight-patient">
                    <div class="card-header">
                        <h3 class="card-title">Pasien Selanjutnya:</h3>
                        <div class="card-tools">
                            <form action="{{ route('staff.queue.callPatient', $nextPatient->id) }}" method="POST" style="display:inline-block;">
                                @csrf
                                <button type="submit" class="btn btn-success btn-sm"><i class="fas fa-phone"></i> Panggil Pasien Ini</button>
                            </form>
                            <a href="{{ route('staff.appointments.edit', $nextPatient->id) }}" class="btn btn-warning btn-sm ml-2"><i class="fas fa-edit"></i> Edit Janji</a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h4>{{ $nextPatient->patient->user->name ?? 'N/A' }}</h4>
                                <p><strong>Waktu Janji:</strong> {{ \Carbon\Carbon::parse($nextPatient->start_time)->format('H:i') }}</p>
                                <p><strong>Dokter:</strong> {{ $nextPatient->doctor->user->name ?? 'N/A' }} ({{ $nextPatient->specialty->name ?? 'N/A' }})</p>
                            </div>
                            <div class="col-md-6 text-right">
                                <p><strong>Status:</strong> <span class="badge badge-{{ $nextPatient->status }}">{{ ucfirst($nextPatient->status) }}</span></p>
                                <form action="{{ route('staff.queue.updateStatus', $nextPatient->id) }}" method="POST" class="mt-2">
                                    @csrf
                                    @method('PATCH')
                                    <div class="input-group input-group-sm">
                                        <select name="status" class="form-control form-control-sm">
                                            <option value="check-in" {{ $nextPatient->status == 'check-in' ? 'selected' : '' }}>Check-in</option>
                                            <option value="waiting" {{ $nextPatient->status == 'waiting' ? 'selected' : '' }}>Menunggu</option>
                                            <option value="in-consultation" {{ $nextPatient->status == 'in-consultation' ? 'selected' : '' }}>Sedang Konsultasi</option>
                                            <option value="completed" {{ $nextPatient->status == 'completed' ? 'selected' : '' }}>Selesai</option>
                                            <option value="cancelled" {{ $nextPatient->status == 'cancelled' ? 'selected' : '' }}>Batal</option>
                                            <option value="no-show" {{ $nextPatient->status == 'no-show' ? 'selected' : '' }}>Tidak Hadir</option>
                                        </select>
                                        <div class="input-group-append">
                                            <button type="submit" class="btn btn-primary btn-sm">Ubah Status</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="alert alert-info">Tidak ada pasien yang saat ini berada dalam antrean atau siap dipanggil.</div>
            @endif

            {{-- Daftar Antrean Pasien --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Daftar Pasien Antrean Hari Ini</h3>
                </div>
                <div class="card-body">
                    @if($queueAppointments->isEmpty())
                        <p class="text-center">Tidak ada janji temu aktif dalam antrean hari ini.</p>
                    @else
                        <div class="row">
                            @foreach($queueAppointments as $appointment)
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
                                                    <form action="{{ route('staff.queue.updateStatus', $appointment->id) }}" method="POST" style="display:inline-block;">
                                                        @csrf
                                                        @method('PATCH')
                                                        <select name="status" onchange="this.form.submit()" class="form-control form-control-sm d-inline-block w-auto">
                                                            <option value="check-in" {{ $appointment->status == 'check-in' ? 'selected' : '' }}>Check-in</option>
                                                            <option value="waiting" {{ $appointment->status == 'waiting' ? 'selected' : '' }}>Menunggu</option>
                                                            <option value="in-consultation" {{ $appointment->status == 'in-consultation' ? 'selected' : '' }}>Sedang Konsultasi</option>
                                                            <option value="completed" {{ $appointment->status == 'completed' ? 'selected' : '' }}>Selesai</option>
                                                            <option value="cancelled" {{ $appointment->status == 'cancelled' ? 'selected' : '' }}>Batal</option>
                                                            <option value="no-show" {{ $appointment->status == 'no-show' ? 'selected' : '' }}>Tidak Hadir</option>
                                                        </select>
                                                    </form>
                                                    <a href="{{ route('staff.appointments.show', $appointment->id) }}" class="btn btn-info btn-sm ml-2">Detail</a>
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

@section('customJs')
{{-- Opsional: Skrip JavaScript khusus untuk antrean, misalnya refresh otomatis atau websocket --}}
@endsection