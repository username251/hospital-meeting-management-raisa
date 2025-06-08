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
                            </div>
                        </div>

                        <div class="card-body">
                            @if(session('success'))
                                <div class="alert alert-success alert-dismissible fade show">
                                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                                    <i class="icon fas fa-check"></i>
                                    {{ session('success') }}
                                </div>
                            @endif

                            @if(session('error'))
                                <div class="alert alert-danger alert-dismissible fade show">
                                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                                    <i class="icon fas fa-ban"></i>
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
                                                <td>
                                                    {{ $loop->iteration + ($schedules->currentPage() - 1) * $schedules->perPage() }}
                                                </td>
                                                <td>
                                                    <strong>{{ $schedule->doctor->user->name }}</strong>
                                                    @if($schedule->doctor->user->email)
                                                        <br><small class="text-muted">{{ $schedule->doctor->user->email }}</small>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if(isset($schedule->doctor->specialty))
                                                        <span class="badge badge-info">{{ $schedule->doctor->specialty->name }}</span>
                                                    @else
                                                        <span class="badge badge-secondary">Tidak Ada</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge badge-primary">{{ $schedule->day_name }}</span>
                                                </td>
                                                <td>
                                                    <i class="fas fa-clock text-muted"></i>
                                                    {{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }} - 
                                                    {{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}
                                                </td>
                                                <td>
                                                    {{ $schedule->slot_duration ?? 30 }} menit
                                                </td>
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
                                                        
                                                        <form method="POST" 
                                                              action="{{ route('doctor_schedules.toggle_availability', $schedule->id) }}" 
                                                              style="display: inline;">
                                                            @csrf
                                                            @method('PATCH')
                                                            <button type="submit" 
                                                                    class="btn btn-{{ $schedule->is_available ? 'secondary' : 'success' }} btn-sm" 
                                                                    title="{{ $schedule->is_available ? 'Nonaktifkan' : 'Aktifkan' }}"
                                                                    onclick="return confirm('Apakah Anda yakin ingin mengubah status ketersediaan jadwal ini?')">
                                                                <i class="fas fa-{{ $schedule->is_available ? 'toggle-off' : 'toggle-on' }}"></i>
                                                            </button>
                                                        </form>
                                                        
                                                        <form method="POST" 
                                                              action="{{ route('doctor_schedules.destroy', $schedule->id) }}" 
                                                              style="display: inline;">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-danger btn-sm" 
                                                                    title="Hapus"
                                                                    onclick="return confirm('Apakah Anda yakin ingin menghapus jadwal ini? Tindakan ini tidak dapat dibatalkan.')">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center py-4">
                                                    <div class="empty-state">
                                                        <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                                                        <h5 class="text-muted">Tidak ada jadwal dokter</h5>
                                                        <p class="text-muted">Belum ada jadwal dokter yang ditambahkan.</p>
                                                        <a href="{{ route('doctor_schedules.create') }}" class="btn btn-primary">
                                                            <i class="fas fa-plus"></i> Tambah Jadwal Pertama
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                         <div class="mt-3">
                            {{ $schedules->links('pagination::bootstrap-4') }}
                        </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

@push('styles')
<style>
.time-display {
    font-size: 0.9em;
    line-height: 1.2;
}

.empty-state {
    padding: 2rem;
}

.btn-group .btn {
    margin-right: 2px;
}

.btn-group .btn:last-child {
    margin-right: 0;
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Auto hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
});
</script>
@endpush
@endsection