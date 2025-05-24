@extends('admin.layout') {{-- Menggunakan layout yang kamu sebutkan --}}

@section('customCss')
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
                    <h1>Daftar Dokter</h1> {{-- Judul diubah --}}
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Home</a></li> {{-- Menggunakan route admin.dashboard --}}
                        <li class="breadcrumb-item active">Daftar Dokter</li> {{-- Breadcrumb diubah --}}
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Daftar Dokter</h3> {{-- Judul kartu diubah --}}
                            <div class="card-tools">
                                {{-- Tombol untuk menambah dokter baru --}}
                                <a href="{{ route('admin.doctors.create') }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus"></i> Tambah Dokter Baru
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            {{-- Form Filter (diadaptasi untuk dokter) --}}
                            <form action="#" method="GET" class="mb-3">
                                <div class="row">
                                    <div class="form-group col-md-4">
                                        <label for="specialty_filter">Filter Spesialisasi</label>
                                        <select name="specialty_id" id="specialty_filter" class="form-control">
                                            <option value="">Semua Spesialisasi</option>
                                            @foreach ($specialties as $specialty) {{-- Asumsikan $specialties dipass dari controller --}}
                                                <option value="{{ $specialty->id }}" {{ $specialty->id == request('specialty_id') ? 'selected' : ''}}>
                                                    {{ $specialty->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4 form-group d-flex align-items-end"> {{-- Align tombol ke bawah --}}
                                        <button type="submit" class="btn btn-success mr-2">Filter Data</button>
                                        <a href="#" class="btn btn-secondary">Reset Filter</a>
                                    </div>
                                </div>
                            </form>

                            <table id="example1" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nama Lengkap</th>
                                        <th>Email</th>
                                        <th>Spesialisasi</th>
                                        <th>Nomor Lisensi</th>
                                        <th>Telepon</th>
                                        <th>Biaya Konsultasi</th>
                                        <th>Aksi</th> {{-- Kolom untuk Edit/Delete --}}
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($doctors as $doctor) {{-- Menggunakan variabel $doctors --}}
                                    <tr>
                                        <td>{{ $doctor->id }}</td>
                                        <td>{{ $doctor->user->name }}</td> {{-- Akses nama dari relasi user --}}
                                        <td>{{ $doctor->user->email }}</td> {{-- Akses email dari relasi user --}}
                                        <td>{{ $doctor->specialty->name ?? '-' }}</td> {{-- Akses nama spesialisasi dari relasi --}}
                                        <td>{{ $doctor->license_number }}</td>
                                        <td>{{ $doctor->user->phone_number ?? '-' }}</td> {{-- Akses nomor telepon dari relasi user --}}
                                        <td>Rp {{ number_format($doctor->consultation_fee, 0, ',', '.') }}</td>
                                        <td class="text-center">
                                            {{-- Tombol Edit --}}
                                            <a class="btn btn-info btn-sm" href="{{ route('admin.doctors.edit', $doctor->id) }}">
                                                <i class="fas fa-pencil-alt"></i> Edit
                                            </a>
                                            {{-- Tombol Delete --}}
                                            <form action="{{ route('admin.doctors.destroy', $doctor->id) }}" method="POST" style="display:inline;">
                                                @csrf
                                                @method('DELETE') {{-- Method spoofing untuk DELETE request --}}
                                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus dokter ini? Tindakan ini tidak dapat dibatalkan.')">
                                                    <i class="fas fa-trash"></i> Hapus
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            {{-- Pagination --}}
                            <div class="mt-3">
                                {{ $doctors->links('pagination::bootstrap-4') }} {{-- Menggunakan pagination bawaan Laravel --}}
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
<script src="{{ asset('dist/js/adminlte.min.js') }}"></script> {{-- Hapus angka di belakang .min.js jika tidak ada --}}
<script src="{{ asset('dist/js/demo.js') }}"></script>
<script>
    $(function () {
        $("#example1").DataTable({
            "responsive": true,
            "lengthChange": false,
            "autoWidth": false,
            "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"],
            // Nonaktifkan ordering dan searching bawaan DataTable jika ingin menggunakan filter form Laravel
            "ordering": false,
            "searching": false,
            "paging": false, // Nonaktifkan paging bawaan jika menggunakan pagination Laravel
            "info": false // Nonaktifkan info bawaan jika menggunakan pagination Laravel
        }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');
        // Jika kamu hanya punya satu tabel, $('#example2').DataTable bisa dihapus
    });
</script>
@endsection