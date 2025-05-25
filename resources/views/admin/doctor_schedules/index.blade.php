@extends('admin.layout')

@section('customCss')
{{-- Sertakan CSS DataTables jika belum ada di layout utama --}}
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
                    <h1>Manajemen Jadwal Dokter</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Home</a></li>
                        <li class="breadcrumb-item active">Jadwal Dokter</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
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
                            <h3 class="card-title">Daftar Jadwal Dokter</h3>
                            <div class="card-tools">
                                <a href="{{ route('admin.doctor_schedules.create') }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus"></i> Tambah Jadwal
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <table id="doctorSchedulesTable" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Dokter</th>
                                        <th>Hari</th>
                                        <th>Mulai</th>
                                        <th>Selesai</th>
                                        <th>Tersedia</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($schedules as $schedule)
                                    <tr>
                                        <td>{{ $schedule->id }}</td>
                                        <td>{{ $schedule->doctor->user->name ?? 'N/A' }}</td>
                                        <td>
                                            @php
                                                $days = [
                                                    1 => 'Senin',
                                                    2 => 'Selasa',
                                                    3 => 'Rabu',
                                                    4 => 'Kamis',
                                                    5 => 'Jumat',
                                                    6 => 'Sabtu',
                                                    7 => 'Minggu',
                                                ];
                                                echo $days[$schedule->day_of_week] ?? $schedule->day_of_week;
                                            @endphp
                                        </td>
                                        <td>{{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }}</td>
                                        <td>{{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}</td>
                                        <td>
                                            @if($schedule->is_available)
                                                <span class="badge badge-success">Ya</span>
                                            @else
                                                <span class="badge badge-danger">Tidak</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            {{-- Jika Anda ingin halaman detail terpisah, uncomment ini --}}
                                            {{-- <a class="btn btn-primary btn-sm" href="{{ route('admin.doctor_schedules.show', $schedule->id) }}">
                                                <i class="fas fa-eye"></i> Detail
                                            </a> --}}
                                            <a class="btn btn-info btn-sm" href="{{ route('admin.doctor_schedules.edit', $schedule->id) }}">
                                                <i class="fas fa-pencil-alt"></i> Edit
                                            </a>
                                            <form action="{{ route('admin.doctor_schedules.destroy', $schedule->id) }}" method="POST" style="display:inline;">
                                                @csrf
                                                @method('DELETE') {{-- Penting: Gunakan DELETE method --}}
                                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus jadwal ini?')">
                                                    <i class="fas fa-trash"></i> Hapus
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <div class="mt-3">
                                {{ $schedules->links('pagination::bootstrap-4') }} {{-- Untuk pagination Laravel --}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@section('customJs')
{{-- Sertakan JS DataTables jika belum ada di layout utama --}}
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
        // Inisialisasi DataTables tanpa paging, searching, ordering, info, karena kita sudah pakai pagination Laravel
        $("#doctorSchedulesTable").DataTable({
            "responsive": true,
            "lengthChange": false,
            "autoWidth": false,
            "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"],
            "paging": false,     // Nonaktifkan paging DataTable karena kita pakai paginate Laravel
            "searching": false,  // Nonaktifkan searching DataTable
            "ordering": false,   // Nonaktifkan sorting DataTable
            "info": false        // Nonaktifkan info DataTable
        }).buttons().container().appendTo('#doctorSchedulesTable_wrapper .col-md-6:eq(0)');
    });
</script>
@endsection