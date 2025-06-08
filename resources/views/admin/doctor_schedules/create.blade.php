@extends('admin.layout')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Tambah Jadwal Dokter</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('doctor_schedules.index') }}">Jadwal Dokter</a></li>
                        <li class="breadcrumb-item active">Tambah Jadwal</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-8">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Form Tambah Jadwal Dokter</h3>
                        </div>

                        <form method="POST" action="{{ route('doctor_schedules.store') }}">
                            @csrf
                            <div class="card-body">
                                
                                @if($errors->any())
                                    <div class="alert alert-danger">
                                        <ul class="mb-0">
                                            @foreach($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                <!-- Pilih Dokter -->
                                <div class="form-group">
                                    <label for="doctor_id">Pilih Dokter <span class="text-danger">*</span></label>
                                    <select class="form-control @error('doctor_id') is-invalid @enderror" 
                                            id="doctor_id" name="doctor_id" required>
                                        <option value="">-- Pilih Dokter --</option>
                                        @foreach($doctors as $doctor)
                                            <option value="{{ $doctor->id }}" 
                                                    {{ old('doctor_id') == $doctor->id ? 'selected' : '' }}>
                                                {{ $doctor->user->name }} 
                                                @if($doctor->specialty)
                                                    - {{ $doctor->specialty->name }}
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('doctor_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <!-- Hari -->
                                <div class="form-group">
                                    <label for="day_of_week">Hari <span class="text-danger">*</span></label>
                                    <select class="form-control @error('day_of_week') is-invalid @enderror" 
                                            id="day_of_week" name="day_of_week" required>
                                        <option value="">-- Pilih Hari --</option>
                                        @foreach($daysOfWeek as $value => $day)
                                            <option value="{{ $value }}" 
                                                    {{ old('day_of_week') == $value ? 'selected' : '' }}>
                                                {{ $day }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('day_of_week')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <!-- Waktu Mulai -->
                                <div class="form-group">
                                    <label for="start_time">Waktu Mulai <span class="text-danger">*</span></label>
                                    <input type="time" 
                                           class="form-control @error('start_time') is-invalid @enderror" 
                                           id="start_time" 
                                           name="start_time" 
                                           value="{{ old('start_time') }}" 
                                           required>
                                    @error('start_time')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <!-- Waktu Selesai -->
                                <div class="form-group">
                                    <label for="end_time">Waktu Selesai <span class="text-danger">*</span></label>
                                    <input type="time" 
                                           class="form-control @error('end_time') is-invalid @enderror" 
                                           id="end_time" 
                                           name="end_time" 
                                           value="{{ old('end_time') }}" 
                                           required>
                                    @error('end_time')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <!-- Status Ketersediaan -->
                                <div class="form-group">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" 
                                               class="custom-control-input" 
                                               id="is_available" 
                                               name="is_available" 
                                               value="1" 
                                               {{ old('is_available', true) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="is_available">
                                            Aktif (Tersedia untuk appointment)
                                        </label>
                                    </div>
                                    <small class="form-text text-muted">Centang jika jadwal ini aktif dan dapat digunakan untuk appointment</small>
                                </div>

                            </div>

                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Simpan Jadwal
                                </button>
                                <a href="{{ route('doctor_schedules.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Batal
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Info Panel -->
                <div class="col-md-4">
                    <div class="card card-info">
                        <div class="card-header">
                            <h3 class="card-title">Informasi</h3>
                        </div>
                        <div class="card-body">
                            <p><strong>Petunjuk:</strong></p>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-success"></i> Pilih dokter terlebih dahulu</li>
                                <li><i class="fas fa-check text-success"></i> Tentukan hari dan waktu praktik</li>
                                <li><i class="fas fa-check text-success"></i> Pastikan tidak ada konflik jadwal</li>
                            </ul>
                            <hr>
                            <p><strong>Catatan:</strong></p>
                            <p class="text-muted small">
                                Sistem akan otomatis mengecek konflik jadwal untuk mencegah 
                                tumpang tindih waktu pada dokter yang sama. Data akan disimpan
                                ke tabel doctor_availabilities agar dapat digunakan untuk appointment.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@section('customJs')
<script>
$(document).ready(function() {
    // Validation untuk waktu
    $('#start_time, #end_time').on('change', function() {
        var startTime = $('#start_time').val();
        var endTime = $('#end_time').val();
        
        if (startTime && endTime && startTime >= endTime) {
            alert('Waktu selesai harus lebih besar dari waktu mulai!');
            $('#end_time').val('');
        }
    });
});
</script>
@endsection