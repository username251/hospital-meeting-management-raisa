@extends('staff.layout') {{-- Sesuaikan dengan layout utama staff Anda --}}

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Edit Jadwal Ketersediaan Dokter</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('staff.index') }}">Dashboard Staff</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('staff.doctor_availabilities.index') }}">Manajemen Jadwal</a></li>
                        <li class="breadcrumb-item active">Edit Jadwal</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Form Edit Jadwal Ketersediaan untuk {{ $selectedDoctor->user->name ?? 'Dokter' }}</h3>
                </div>
                <form action="{{ route('staff.doctor_availabilities.update', $doctorAvailability->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                         @if (session('error'))
                            <div class="alert alert-danger">{{ session('error') }}</div>
                        @endif

                        {{-- Staff mungkin tidak boleh mengubah dokter untuk jadwal yang sudah ada, --}}
                        {{-- atau jika boleh, tambahkan dropdown dokter di sini --}}
                        {{-- Untuk saat ini, kita asumsikan dokter tidak diubah saat edit availability --}}
                        <input type="hidden" name="doctor_id" value="{{ $doctorAvailability->doctor_id }}">
                        <p><strong>Dokter:</strong> {{ $selectedDoctor->user->name ?? 'N/A' }}</p>


                        <div class="form-group">
                            <label for="day_of_week">Hari <span class="text-danger">*</span></label>
                            <select name="day_of_week" id="day_of_week" class="form-control @error('day_of_week') is-invalid @enderror" required>
                                <option value="">-- Pilih Hari --</option>
                                @foreach($daysOfWeekForForm as $key => $day)
                                    <option value="{{ $key }}" 
                                        {{ (old('day_of_week', $selectedDayInteger) == $key) ? 'selected' : '' }}>
                                        {{ $day }}
                                    </option>
                                @endforeach
                            </select>
                            @error('day_of_week')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="start_time">Waktu Mulai <span class="text-danger">*</span></label>
                                    <input type="time" name="start_time" id="start_time" class="form-control @error('start_time') is-invalid @enderror" 
                                           value="{{ old('start_time', \Carbon\Carbon::parse($doctorAvailability->start_time)->format('H:i')) }}" required>
                                    @error('start_time')
                                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="end_time">Waktu Selesai <span class="text-danger">*</span></label>
                                    <input type="time" name="end_time" id="end_time" class="form-control @error('end_time') is-invalid @enderror" 
                                           value="{{ old('end_time', \Carbon\Carbon::parse($doctorAvailability->end_time)->format('H:i')) }}" required>
                                    @error('end_time')
                                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="slot_duration">Durasi Slot (Menit) <span class="text-danger">*</span></label>
                            <input type="number" name="slot_duration" id="slot_duration" class="form-control @error('slot_duration') is-invalid @enderror" 
                                   value="{{ old('slot_duration', $doctorAvailability->slot_duration) }}" min="5" max="120" required>
                            <small class="form-text text-muted">Contoh: 30 (untuk 30 menit)</small>
                            @error('slot_duration')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                        
                        {{-- Staff bisa mengubah status ketersediaan langsung di sini atau melalui tombol toggle di index --}}
                        {{-- <div class="form-group">
                            <label for="is_available">Status Ketersediaan</label>
                            <select name="is_available" id="is_available" class="form-control">
                                <option value="1" {{ old('is_available', $doctorAvailability->is_available) == 1 ? 'selected' : '' }}>Aktif</option>
                                <option value="0" {{ old('is_available', $doctorAvailability->is_available) == 0 ? 'selected' : '' }}>Nonaktif</option>
                            </select>
                        </div> --}}

                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">Update Jadwal</button>
                        <a href="{{ route('staff.doctor_availabilities.index') }}" class="btn btn-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>
@endsection
