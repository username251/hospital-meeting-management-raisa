@extends('staff.layout')
@section('customCss')
<link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
@endsection

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Detail Pasien: {{ $patient->user->name ?? 'N/A' }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('staff.index') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('staff.patients.index') }}">Manajemen Pasien</a></li>
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
                    <h3 class="card-title">Informasi Dasar Pasien</h3>
                    <div class="card-tools">
                        <a href="{{ route('staff.patients.edit', $patient->id) }}" class="btn btn-warning btn-sm">Edit Informasi Pasien</a>
                        <a href="{{ route('staff.patients.index') }}" class="btn btn-secondary btn-sm">Kembali ke Daftar</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <dl class="row">
                                <dt class="col-sm-4">ID Pasien:</dt>
                                <dd class="col-sm-8">{{ $patient->id }}</dd>

                                <dt class="col-sm-4">Nama:</dt>
                                <dd class="col-sm-8">{{ $patient->user->name ?? 'N/A' }}</dd>

                                <dt class="col-sm-4">Email:</dt>
                                <dd class="col-sm-8">{{ $patient->user->email ?? 'N/A' }}</dd>

                                <dt class="col-sm-4">Telepon:</dt>
                                <dd class="col-sm-8">{{ $patient->phone ?? '-' }}</dd>

                                <dt class="col-sm-4">Alamat:</dt>
                                <dd class="col-sm-8">{{ $patient->address ?? '-' }}</dd>
                            </dl>
                        </div>
                        <div class="col-md-6">
                            <dl class="row">
                                <dt class="col-sm-4">Tanggal Lahir:</dt>
                                <dd class="col-sm-8">{{ $patient->date_of_birth ? \Carbon\Carbon::parse($patient->date_of_birth)->format('d M Y') : '-' }}</dd>

                                <dt class="col-sm-4">Jenis Kelamin:</dt>
                                <dd class="col-sm-8">{{ ucfirst($patient->gender) ?? '-' }}</dd>

                                <dt class="col-sm-4">Golongan Darah:</dt>
                                <dd class="col-sm-8">{{ $patient->blood_type ?? '-' }}</dd>

                                <dt class="col-sm-4">Terdaftar Sejak:</dt>
                                <dd class="col-sm-8">{{ $patient->created_at ? \Carbon\Carbon::parse($patient->created_at)->format('d M Y H:i') : '-' }}</dd>
                            </dl>
                        </div>
                    </div>
                    <hr>
                    <h5>Informasi Medis Tambahan:</h5>
                    <dl class="row">
                        <dt class="col-sm-3">Riwayat Medis:</dt>
                        <dd class="col-sm-9">{{ $patient->medical_history ?? '-' }}</dd>

                        <dt class="col-sm-3">Alergi:</dt>
                        <dd class="col-sm-9">{{ $patient->allergies ?? '-' }}</dd>

                        <dt class="col-sm-3">Obat-obatan Saat Ini:</dt>
                        <dd class="col-sm-9">{{ $patient->current_medications ?? '-' }}</dd>
                    </dl>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header">
                    <h3 class="card-title">Riwayat Janji Temu Pasien</h3>
                </div>
                <div class="card-body">
                    @if($appointments->isEmpty())
                        <p class="text-center">Tidak ada riwayat janji temu untuk pasien ini.</p>
                    @else
                        <div class="table-responsive">
                            <table id="patientAppointmentsTable" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>ID Janji</th>
                                        <th>Dokter</th>
                                        <th>Spesialisasi</th>
                                        <th>Tanggal</th>
                                        <th>Waktu</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($appointments as $appointment)
                                        <tr>
                                            <td>{{ $appointment->id }}</td>
                                            <td>{{ $appointment->doctor->user->name ?? 'N/A' }}</td>
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
                                                        default: $statusClass = 'badge badge-secondary'; break;
                                                    }
                                                @endphp
                                                <span class="{{ $statusClass }}">{{ ucfirst($appointment->status) }}</span>
                                            </td>
                                            <td>
                                                <a href="{{ route('staff.appointments.show', $appointment->id) }}" class="btn btn-sm btn-primary">Lihat Detail Janji</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">
                            {{ $appointments->links('pagination::bootstrap-4') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@section('customJs')
<script src="{{ asset('plugins/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
<script>
    $(function () {
        // Hanya inisialisasi DataTables untuk tabel riwayat janji temu pasien
        $("#patientAppointmentsTable").DataTable({
            "paging": true,
            "lengthChange": false,
            "searching": true,
            "ordering": true,
            "info": true,
            "autoWidth": false,
            "responsive": true,
            "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"]
        }).buttons().container().appendTo('#patientAppointmentsTable_wrapper .col-md-6:eq(0)');
    });
</script>
@endsection