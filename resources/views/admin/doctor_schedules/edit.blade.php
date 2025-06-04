@extends('admin.layout')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Edit Jadwal Dokter</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('doctor_schedules.index') }}">Jadwal Dokter</a></li>
                        <li class="breadcrumb-item active">Edit Jadwal</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-8">
                    <div class="card card-warning">
                        <div class="card-header">
                            <h3 class="card-title">Form Edit Jadwal Dokter</h3>
                        </div>

                        <form method="POST" action="{{ route('doctor_schedules.update', $doctorSchedule->id) }}">
                            @csrf
                            @method('PUT')
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
                                                    {{ (old('doctor_id', $doctorSchedule->doctor_id) == $doctor->id) ? 'selected' : '' }}>
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
                                                    {{ (old('day_of_week', $doctorSchedule->day_of_week) == $value) ? 'selected' : '' }}>
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
                                           value="{{ old('start_time', \Carbon\Carbon::parse($doctorSchedule->start_time)->format('H:i')) }}" 
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
                                           value="{{ old('end_time', \Carbon\Carbon::parse($doctorSchedule->end_time)->format('H:i')) }}" 
                                           required>
                                    @error('end_time')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <!-- Durasi Slot -->
                                <div class="form-group">
                                    <label for="slot_duration">Durasi Slot (menit)</label>
                                    <select class="form-control @error('slot_duration') is-invalid @enderror" 
                                            id="slot_duration" name="slot_duration">
                                        <option value="15" {{ old('slot_duration', $doctorSchedule->slot_duration) == '15' ? 'selected' : '' }}>15 Menit</option>
                                        <option value="30" {{ old('slot_duration', $doctorSchedule->slot_duration ?? '30') == '30' ? 'selected' : '' }}>30 Menit</option>
                                        <option value="45" {{ old('slot_duration', $doctorSchedule->slot_duration) == '45' ? 'selected' : '' }}>45 Menit</option>
                                        <option value="60" {{ old('slot_duration', $doctorSchedule->slot_duration) == '60' ? 'selected' : '' }}>60 Menit</option>
                                    </select>
                                    @error('slot_duration')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                    <small class="form-text text-muted">Durasi setiap slot appointment</small>
                                </div>

                                <!-- Status Ketersediaan -->
                                <div class="form-group">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" 
                                               class="custom-control-input" 
                                               id="is_available" 
                                               name="is_available" 
                                               value="1" 
                                               {{ old('is_available', $doctorSchedule->is_available) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="is_available">
                                            Aktif (Tersedia untuk appointment)
                                        </label>
                                    </div>
                                    <small class="form-text text-muted">Centang jika jadwal ini aktif dan dapat digunakan untuk appointment</small>
                                </div>

                            </div>

                            <div class="card-footer">
                                <button type="submit" class="btn btn-warning">
                                    <i class="fas fa-save"></i> Update Jadwal
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
                            <h3 class="card-title">Informasi Jadwal</h3>
                        </div>
                        <div class="card-body">
                            <p><strong>Dokter:</strong> {{ $doctorSchedule->doctor->user->name }}</p>
                            <p><strong>Spesialisasi:</strong> {{ $doctorSchedule->doctor->specialty->name ?? 'Tidak Ada' }}</p>
                            <p><strong>Status Saat Ini:</strong> 
                                <span class="badge badge-{{ $doctorSchedule->is_available ? 'success' : 'danger' }}">
                                    {{ $doctorSchedule->is_available ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </p>
                            <hr>
                            <p><strong>Catatan:</strong></p>
                            <p class="text-muted small">
                                Pastikan tidak ada appointment yang bentrok saat mengubah jadwal ini.
                            </p>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="card card-secondary">
                        <div class="card-header">
                            <h3 class="card-title">Aksi Cepat</h3>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('doctor_schedules.toggle_availability', $doctorSchedule->id) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" 
                                        class="btn btn-{{ $doctorSchedule->is_available ? 'secondary' : 'success' }} btn-block"
                                        onclick="return confirm('Yakin ingin mengubah status jadwal ini?')">
                                    <i class="fas fa-{{ $doctorSchedule->is_available ? 'toggle-off' : 'toggle-on' }}"></i>
                                    {{ $doctorSchedule->is_available ? 'Nonaktifkan' : 'Aktifkan' }} Jadwal
                                </button>
                            </form>
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