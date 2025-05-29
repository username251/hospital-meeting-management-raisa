@extends('staff.layout')

@section('content')
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">Ketersediaan Dokter</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('staff.index') }}">Home</a></li>
                            <li class="breadcrumb-item active">Ketersediaan Dokter</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Daftar Ketersediaan Dokter</h3>
                                <div class="card-tools">
                                    <a href="{{ route('staff.doctor_availabilities.create') }}" class="btn btn-primary btn-sm">Tambah Ketersediaan Baru</a>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                @if (session('success'))
                                    <div class="alert alert-success">
                                        {{ session('success') }}
                                    </div>
                                @endif
                                @if (session('error'))
                                    <div class="alert alert-danger">
                                        {{ session('error') }}
                                    </div>
                                @endif
                                @if ($errors->any())
                                    <div class="alert alert-danger">
                                        <ul>
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Dokter</th>
                                            <th>Hari</th>
                                            <th>Waktu Mulai</th>
                                            <th>Waktu Selesai</th>
                                            <th>Catatan</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($availabilities as $availability)
                                            <tr>
                                                <td>{{ $availability->id }}</td>
                                                <td>{{ $availability->doctor->user->name ?? 'N/A' }}</td>
                                                <td>{{ $availability->day_of_week }}</td>
                                                <td>{{ \Carbon\Carbon::parse($availability->start_time)->format('H:i') }}</td>
                                                <td>{{ \Carbon\Carbon::parse($availability->end_time)->format('H:i') }}</td>
                                                <td>{{ $availability->notes ?? '-' }}</td>
                                                <td>
                                                    <a href="{{ route('staff.doctor_availabilities.edit', $availability->id) }}" class="btn btn-sm btn-warning">Edit</a>
                                                    <form action="{{ route('staff.doctor_availabilities.destroy', $availability->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus ketersediaan ini?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center">Tidak ada ketersediaan dokter yang ditemukan.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="card-footer clearfix">
                                {{ $availabilities->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection