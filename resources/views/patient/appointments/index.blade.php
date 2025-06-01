@extends('patient.layout') {{-- Sesuaikan path ini ke layout utama pasien Anda --}}

@section('customCss')
{{-- Pastikan Anda sudah menyertakan CSS DataTables di layout pasien Anda atau di sini --}}
<link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
{{-- Tambahkan CSS kustom Anda di sini jika ada --}}
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
                        {{-- Link untuk membuat janji temu baru (opsional, bisa di tempat lain juga) --}}
                        <a href="{{ route('appointments.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Jadwalkan Janji Temu Baru
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    {{-- Filter Form --}}
                    <form action="{{ route('appointments.index') }}" method="GET" class="mb-3">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="filter_date">Tanggal</label>
                                    <input type="date" name="date" id="filter_date" class="form-control" value="{{ request('date') }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="filter_status">Status</label>
                                    <select name="status" id="filter_status" class="form-control">
                                        <option value="">Semua Status</option>
                                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                        <option value="rescheduled" {{ request('status') == 'rescheduled' ? 'selected' : '' }}>Rescheduled</option>
                                        <option value="scheduled" {{ request('status') == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                                        <option value="check-in" {{ request('status') == 'check-in' ? 'selected' : '' }}>Check-in</option>
                                        <option value="waiting" {{ request('status') == 'waiting' ? 'selected' : '' }}>Waiting</option>
                                        <option value="in-consultation" {{ request('status') == 'in-consultation' ? 'selected' : '' }}>In-consultation</option>
                                        <option value="no-show" {{ request('status') == 'no-show' ? 'selected' : '' }}>No-Show</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary mr-2">Filter</button>
                                <a href="{{ route('appointments.index') }}" class="btn btn-secondary">Reset</a>
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
                                    <th>Alasan</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($appointments as $appointment)
                                    <tr>
                                        <td>{{ $appointment->id }}</td>
                                        <td>{{ $appointment->doctor->user->name ?? 'N/A' }}</td>
                                        <td>{{ $appointment->specialty?->name ?? 'Tidak Ada Spesialisasi' }}</td>
                                        <td>{{ \Carbon\Carbon::parse($appointment->appointment_date)->format('d M Y') }}</td>
                                        <td>{{ \Carbon\Carbon::parse($appointment->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($appointment->end_time)->format('H:i') }}</td>
                                        <td>{{ Str::limit($appointment->reason, 50, '...') }}</td>
                                        <td>
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
                                        </td>
                                        <td>
                                            <a href="{{ route('patient.appointments.show', $appointment->id) }}" class="btn btn-info btn-sm">Lihat Detail</a>
                                            {{-- Tombol batalkan janji temu --}}
                                            @if (in_array($appointment->status, ['pending', 'confirmed', 'scheduled']))
                                                <form action="{{ route('patient.appointments.cancel', $appointment->id) }}" method="POST" style="display:inline-block;">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin membatalkan janji temu ini?');">Batalkan</button>
                                                </form>
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
                        {{ $appointments->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@section('customJs')
{{-- Pastikan Anda sudah menyertakan JS DataTables di layout pasien Anda atau di sini --}}
<script src="{{ asset('plugins/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-buttons/js/dataTables.buttons.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-buttons/js/buttons.bootstrap4.min.js') }}"></script>
<script src="{{ asset('plugins/jszip/jszip.min.js') }}"></script>
<script src="{{ asset('plugins/pdfmake/pdfmake.min.js') }}"></script>
<script src="{{ asset('plugins/pdfmake/vfs_fonts.js') }}"></script>
<script src="{{ asset('plugins/datatables-buttons/js/buttons.html5.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-buttons/js/buttons.print.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-buttons/js/buttons.colVis.min.js') }}"></script>
<script src="{{ asset('dist/js/adminlte.min.js') }}"></script>
<script src="{{ asset('dist/js/demo.js') }}"></script>
<script>
    $(function () {
        // Inisialisasi DataTables tanpa tombol ekspor yang terlalu banyak untuk pasien
        // Biarkan pagination dan searching aktif.
        // Hapus "buttons" jika Anda tidak ingin pasien bisa export/print daftar janji temu mereka.
        $("#patientAppointmentsTable").DataTable({
            "responsive": true,
            "lengthChange": false,
            "autoWidth": false,
            "paging": true,
            "searching": true,
            "ordering": true,
            "info": true
            // "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"] // Nonaktifkan atau sesuaikan jika tidak diperlukan
        });
        // .buttons().container().appendTo('#patientAppointmentsTable_wrapper .col-md-6:eq(0)'); // Hapus baris ini jika tidak ada tombol
    });
</script>
@endsection