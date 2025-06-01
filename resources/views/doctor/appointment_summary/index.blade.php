@extends('doctor.layout') {{-- Pastikan path ini benar ke layout utama doctor Anda --}}

@section('customCss')
{{-- Pastikan Anda sudah menyertakan CSS DataTables di layout doctor Anda atau di sini --}}
<link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
@endsection

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
                        <li class="breadcrumb-item"><a href="{{ route('doctor.appointments.index') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Manajemen Janji Temu</li>
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
                    <h3 class="card-title">Daftar Janji Temu</h3>
                    <div class="card-tools">
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="appointmentsTabledoctor" class="table table-bordered table-striped">
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
                                        <td>{{ $appointment->patient->user->name ?? 'N/A' }}</td>
                                        <td>{{ $appointment->doctor->user->name ?? 'N/A' }}</td>
                                        <td>{{ $appointment->specialty?->name ?? 'Tidak Ada Spesialisasi' }}</td>                                        <td>{{ \Carbon\Carbon::parse($appointment->appointment_date)->format('d M Y') }}</td>
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
                                            <a href="{{ route('doctor.appointments.show', $appointment->id) }}" class="btn btn-info btn-sm">Lihat</a>
                                           
                                            {{-- Tombol untuk update status cepat --}}
                                            <form action="{{ route('doctor.appointments.updateStatus', $appointment->id) }}" method="POST" style="display:inline-block;">
                                                @csrf
                                                @method('PATCH')
                                                <select name="status" onchange="this.form.submit()" class="form-control form-control-sm d-inline-block w-auto">
                                                    <option value="pending" {{ $appointment->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                                    <option value="confirmed" {{ $appointment->status == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                                    <option value="completed" {{ $appointment->status == 'completed' ? 'selected' : '' }}>Completed</option>
                                                    <option value="cancelled" {{ $appointment->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                                    <option value="rescheduled" {{ $appointment->status == 'rescheduled' ? 'selected' : '' }}>Rescheduled</option>
                                                </select>
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
                        {{ $appointments->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@section('customJs')
{{-- Pastikan Anda sudah menyertakan JS DataTables di layout doctor Anda atau di sini --}}
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
        $("#appointmentsTableDoctor").DataTable({
            "responsive": true,
            "lengthChange": false,
            "autoWidth": false,
            "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"],
            "paging": true, // Biarkan paging aktif untuk tabel daftar janji temu
            "searching": true, // Biarkan searching aktif
            "ordering": true, // Biarkan ordering aktif
            "info": true // Biarkan info aktif
        }).buttons().container().appendTo('#appointmentsTableDoctor_wrapper .col-md-6:eq(0)');
    });
</script>
@endsection