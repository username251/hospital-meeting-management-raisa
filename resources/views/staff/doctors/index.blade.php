@extends('staff.layout')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Manajemen Dokter</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('staff.index') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Manajemen Dokter</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Daftar Dokter</h3>
                <div class="card-tools">
                    <a href="{{ route('doctors.create') }}" class="btn btn-primary btn-sm">Tambah Dokter Baru</a>
                </div>
            </div>
            <div class="card-body p-0">
                @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif
                <table class="table table-striped projects">
                    <thead>
                        <tr>
                            <th style="width: 1%">#</th>
                            <th style="width: 20%">Nama Dokter</th>
                            <th style="width: 15%">Email</th>
                            <th style="width: 15%">Spesialisasi</th>
                            <th style="width: 10%">Telp</th> {{-- Tambah --}}
                            <th style="width: 15%">No. Lisensi</th> {{-- Tambah --}}
                            <th style="width: 10%">Biaya Konsultasi</th> {{-- Tambah --}}
                            <th style="width: 20%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($doctors as $doctor)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $doctor->user->name }}</td>
                                <td>{{ $doctor->user->email }}</td>
                                <td>{{ $doctor->specialty->name ?? 'N/A' }}</td>
                                <td>{{ $doctor->phone_number ?? '-' }}</td> {{-- Tampil --}}
                                <td>{{ $doctor->license_number ?? '-' }}</td> {{-- Tampil --}}
                                <td>{{ number_format($doctor->consultation_fee, 0, ',', '.') ?? '0' }}</td> {{-- Tampil --}}
                                <td class="project-actions">
                                    <a class="btn btn-info btn-sm" href="{{ route('doctors.edit', $doctor->id) }}">
                                        <i class="fas fa-pencil-alt"></i> Edit
                                    </a>
                                    <form action="{{ route('doctors.destroy', $doctor->id) }}" method="POST" style="display: inline-block;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus dokter ini? Ini juga akan menghapus akun penggunanya.');">
                                            <i class="fas fa-trash"></i> Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>
@endsection 