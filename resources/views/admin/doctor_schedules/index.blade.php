@extends('admin.layout')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Jadwal Dokter</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Jadwal Dokter</li>
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
                            <h3 class="card-title">Daftar Jadwal Dokter</h3>
                            <div class="card-tools">
                                <a href="{{ route('doctor_schedules.create') }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus"></i> Tambah Jadwal
                                </a>
                                <a href="{{ route('doctor_schedules.calendar') }}" class="btn btn-info btn-sm">
                                    <i class="fas fa-calendar"></i> Lihat Kalender
                                </a>
                            </div>
                        </div>

                        <div class="card-body">
                            @if(session('success'))
                                <div class="alert alert-success alert-dismissible">
                                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                                    {{ session('success') }}
                                </div>
                            @endif

                            @if(session('error'))
                                <div class="alert alert-danger alert-dismissible">
                                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                                    {{ session('error') }}
                                </div>
                            @endif

                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Dokter</th>
                                            <th>Spesialisasi</th>
                                            <th>Hari</th>
                                            <th>Waktu</th>
                                            <th>Durasi Slot</th>
                                            <th>Status</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($schedules as $schedule)
                                            <tr>
                                                <td>{{ $loop->iteration + ($schedules->currentPage() - 1) * $schedules->perPage() }}</td>
                                                <td>
                                                    <strong>{{ $schedule->doctor->user->name }}</strong>
                                                </td>
                                                <td>{{ $schedule->doctor->specialty->name ?? 'Tidak Ada' }}</td>
                                                <td>
                                                    <span class="badge badge-secondary">{{ $schedule->day_name }}</span>
                                                </td>
                                                <td>
                                                    {{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }} - 
                                                    {{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}
                                                </td>
                                                <td>{{ $schedule->slot_duration ?? 30 }} menit</td>
                                                <td>
                                                    @if($schedule->is_available)
                                                        <span class="badge badge-success">Aktif</span>
                                                    @else
                                                        <span class="badge badge-danger">Nonaktif</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                       
                                                        <a href="{{ route('doctor_schedules.edit', $schedule->id) }}" 
                                                           class="btn btn-warning btn-sm" title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        
                                                        <!-- Toggle Availability -->
                                                        <form method="POST" 
                                                              action="{{ route('doctor_schedules.toggle_availability', $schedule->id) }}" 
                                                              style="display: inline;">
                                                            @csrf
                                                            @method('PATCH')
                                                            <button type="submit" 
                                                                    class="btn btn-{{ $schedule->is_available ? 'secondary' : 'success' }} btn-sm" 
                                                                    title="{{ $schedule->is_available ? 'Nonaktifkan' : 'Aktifkan' }}"
                                                                    onclick="return confirm('Yakin ingin mengubah status jadwal ini?')">
                                                                <i class="fas fa-{{ $schedule->is_available ? 'toggle-off' : 'toggle-on' }}"></i>
                                                            </button>
                                                        </form>
                                                        
                                                        <!-- Delete -->
                                                        <form method="POST" 
                                                              action="{{ route('doctor_schedules.destroy', $schedule->id) }}" 
                                                              style="display: inline;">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-danger btn-sm" 
                                                                    title="Hapus"
                                                                    onclick="return confirm('Yakin ingin menghapus jadwal ini?')">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center">Tidak ada jadwal dokter yang ditemukan.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <div class="d-flex justify-content-center">
                                {{ $schedules->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection