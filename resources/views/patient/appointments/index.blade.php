@extends('patient.layout') {{-- Pastikan path ini sesuai dengan layout utama pasien Anda --}}

@section('customCss')
{{-- CSS untuk DataTables --}}
<link rel="stylesheet" href="{{ asset('admincss/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('admincss/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('admincss/plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
{{-- Tambahkan CSS kustom Anda di sini jika ada --}}
<style>
    /* Custom CSS untuk rating bintang jika diperlukan */
    .star-rating-display {
        color: #ffc107; /* Warna bintang kuning */
    }
    .badge {
        font-size: 0.85em;
        padding: 0.4em 0.6em;
    }
    .table td, .table th {
        vertical-align: middle;
    }
</style>
@endsection

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Janji Temu Saya</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('patient.index') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Janji Temu Saya</li>
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
                    {{-- Filter Form --}}
                    <form action="{{ route('appointments.index') }}" method="GET" class="mb-4">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="filter_date">Tanggal</label>
                                    <input type="date" name="date" id="filter_date" class="form-control form-control-sm" value="{{ request('date') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="filter_doctor">Dokter</label>
                                    <select name="doctor_id" id="filter_doctor" class="form-control form-control-sm">
                                        <option value="">Semua Dokter</option>
                                        @foreach($doctors as $doctor)
                                            <option value="{{ $doctor->id }}" {{ request('doctor_id') == $doctor->id ? 'selected' : '' }}>
                                                {{ $doctor->user->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="filter_status">Status</label>
                                    <select name="status" id="filter_status" class="form-control form-control-sm">
                                        <option value="">Semua Status</option>
                                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                        <option value="scheduled" {{ request('status') == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                                        <option value="check-in" {{ request('status') == 'check-in' ? 'selected' : '' }}>Check-in</option>
                                        <option value="waiting" {{ request('status') == 'waiting' ? 'selected' : '' }}>Waiting</option>
                                        <option value="in-consultation" {{ request('status') == 'in-consultation' ? 'selected' : '' }}>In Consultation</option>
                                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                        <option value="rescheduled" {{ request('status') == 'rescheduled' ? 'selected' : '' }}>Rescheduled</option>
                                        <option value="no-show" {{ request('status') == 'no-show' ? 'selected' : '' }}>No-Show</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <div class="form-group w-100">
                                    <button type="submit" class="btn btn-primary btn-sm mr-1 w-auto">Filter</button>
                                    <a href="{{ route('appointments.index') }}" class="btn btn-secondary btn-sm w-auto">Reset</a>
                                </div>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table id="patientAppointmentsTable" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Dokter</th>
                                    <th>Spesialisasi</th>
                                    <th>Tanggal</th>
                                    <th>Waktu</th>
                                    <th>Status</th>
                                    <th>Rating Anda</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($appointments as $appointment)
                                    <tr>
                                        <td>{{ $appointment->id }}</td>
                                        <td>{{ $appointment->doctor->user->name ?? 'N/A' }}</td>
                                        <td>{{ $appointment->doctor->specialty->name ?? ($appointment->specialty->name ?? 'Umum') }}</td>
                                        <td>{{ \Carbon\Carbon::parse($appointment->appointment_date)->translatedFormat('d M Y') }}</td>
                                        <td>{{ \Carbon\Carbon::parse($appointment->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($appointment->end_time)->format('H:i') }}</td>
                                        <td>
                                            @php
                                                $statusClass = '';
                                                $statusText = ucfirst(str_replace('-', ' ', $appointment->status));
                                                switch ($appointment->status) {
                                                    case 'pending': $statusClass = 'badge badge-secondary'; break;
                                                    case 'confirmed': $statusClass = 'badge badge-primary'; break;
                                                    case 'scheduled': $statusClass = 'badge badge-info'; break;
                                                    case 'check-in': $statusClass = 'badge badge-dark'; break;
                                                    case 'waiting': $statusClass = 'badge badge-warning'; break;
                                                    case 'in-consultation': $statusClass = 'badge badge-info'; break;
                                                    case 'completed': $statusClass = 'badge badge-success'; break;
                                                    case 'cancelled': $statusClass = 'badge badge-danger'; break;
                                                    case 'rescheduled': $statusClass = 'badge badge-light text-dark'; break;
                                                    case 'no-show': $statusClass = 'badge badge-light text-muted'; break;
                                                    default: $statusClass = 'badge badge-secondary';
                                                }
                                            @endphp
                                            <span class="{{ $statusClass }}">{{ $statusText }}</span>
                                        </td>
                                        <td>
                                            @if ($appointment->feedback)
                                                <span class="star-rating-display">
                                                    @for ($i = 1; $i <= 5; $i++)
                                                        @if ($i <= $appointment->feedback->rating)
                                                            ★
                                                        @else
                                                            ☆
                                                        @endif
                                                    @endfor
                                                </span>
                                                ({{ $appointment->feedback->rating }}/5)
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('patient.appointments.show', $appointment->id) }}" class="btn btn-info btn-xs mb-1" title="Lihat Detail">
                                                <i class="fas fa-eye"></i> Detail
                                            </a>
                                            @if (in_array($appointment->status, ['pending', 'confirmed', 'scheduled']))
                                                <form action="{{ route('patient.appointments.cancel', $appointment->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan janji temu ini?');">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-danger btn-xs mb-1" title="Batalkan Janji Temu">
                                                        <i class="fas fa-times-circle"></i> Batalkan
                                                    </button>
                                                </form>
                                            @endif

                                            @if($appointment->status == 'completed')
                                                @if(!$appointment->feedback)
                                                    <a href="{{ route('feedback.create', $appointment->id) }}" class="btn btn-warning btn-xs mb-1" title="Beri Feedback">
                                                        <i class="fas fa-star"></i> Beri Feedback
                                                    </a>
                                                @else
                                                    <button class="btn btn-success btn-xs mb-1" disabled title="Feedback Telah Diberikan">
                                                        <i class="fas fa-check-circle"></i> Feedback Diberikan
                                                    </button>
                                                @endif
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">Anda belum memiliki janji temu.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $appointments->appends(request()->query())->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@section('customJs')
{{-- JS untuk DataTables dan plugin lainnya --}}
<script src="{{ asset('admincss/plugins/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('admincss/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('admincss/plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('admincss/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ asset('admincss/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
<script src="{{ asset('admincss/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
<script src="{{ asset('admincss/plugins/datatables-buttons/js/dataTables.buttons.min.js') }}"></script>
<script src="{{ asset('admincss/plugins/datatables-buttons/js/buttons.bootstrap4.min.js') }}"></script>
{{-- <script src="{{ asset('admincss/plugins/jszip/jszip.min.js') }}"></script> --}}
{{-- <script src="{{ asset('admincss/plugins/pdfmake/pdfmake.min.js') }}"></script> --}}
{{-- <script src="{{ asset('admincss/plugins/pdfmake/vfs_fonts.js') }}"></script> --}}
{{-- <script src="{{ asset('admincss/plugins/datatables-buttons/js/buttons.html5.min.js') }}"></script> --}}
{{-- <script src="{{ asset('admincss/plugins/datatables-buttons/js/buttons.print.min.js') }}"></script> --}}
{{-- <script src="{{ asset('admincss/plugins/datatables-buttons/js/buttons.colVis.min.js') }}"></script> --}}
{{-- AdminLTE App --}}
{{-- <script src="{{ asset('admincss/dist/js/adminlte.min.js') }}"></script> --}}
{{-- <script src="{{ asset('admincss/dist/js/demo.js') }}"></script> --}} {{-- Demo JS bisa dihilangkan untuk production --}}

<script>
$(function () {
    // Inisialisasi DataTables
    // Hapus "paging" jika Anda menggunakan pagination dari Laravel dan tidak ingin pagination DataTables.
    // Hapus "searching" jika Anda tidak ingin fitur search dari DataTables.
    // Hapus "ordering" jika Anda tidak ingin fitur sorting dari DataTables.
    // Hapus "info" jika Anda tidak ingin informasi jumlah entri dari DataTables.
    $("#patientAppointmentsTable").DataTable({
        "responsive": true,
        "lengthChange": false, // Sembunyikan opsi ubah jumlah entri per halaman
        "autoWidth": false,
        "paging": false,      // Matikan paging DataTables, gunakan pagination Laravel
        "searching": false,   // Matikan searching DataTables, gunakan filter form
        "ordering": true,     // Aktifkan sorting kolom
        "info": false,        // Matikan info DataTables
        "order": [[0, 'desc']], // Urutkan berdasarkan ID (kolom pertama) secara descending
        "columnDefs": [
            { "orderable": false, "targets": 7 } // Kolom 'Aksi' tidak bisa di-sort
        ]
        // "buttons": ["copy", "csv", "excel", "pdf", "print"].map(function(btn) { // Contoh jika ingin tombol export
        //     return { extend: btn, className: 'btn-sm' }
        // })
    });
    // Jika menggunakan tombol export DataTables:
    // $("#patientAppointmentsTable").buttons().container().appendTo('#patientAppointmentsTable_wrapper .col-md-6:eq(0)');
});
</script>
@endsection
