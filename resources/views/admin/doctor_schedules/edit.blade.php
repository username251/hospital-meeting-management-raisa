@extends('admin.layout')

@section('content')

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Edit Jadwal Dokter</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.doctor_schedules.index') }}">Manajemen Jadwal Dokter</a></li>
                        <li class="breadcrumb-item active">Edit Jadwal</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Form Edit Jadwal Dokter</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.doctor_schedules.update', $doctorSchedule->id) }}" method="POST">
                    @csrf
                    @method('PUT') {{-- PENTING: Gunakan PUT method untuk UPDATE --}}

                    <div class="form-group">
                        <label for="doctor_id">Dokter</label>
                        <select name="doctor_id" id="doctor_id" class="form-control @error('doctor_id') is-invalid @enderror" required>
                            <option value="">Pilih Dokter</option>
                            @foreach($doctors as $doctor)
                                <option value="{{ $doctor->id }}" {{ old('doctor_id', $doctorSchedule->doctor_id) == $doctor->id ? 'selected' : '' }}>{{ $doctor->user->name }} ({{ $doctor->specialty->name }})</option>
                            @endforeach
                        </select>
                        @error('doctor_id')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="day_of_week">Hari</label>
                        <select name="day_of_week" id="day_of_week" class="form-control @error('day_of_week') is-invalid @enderror" required>
                            <option value="">Pilih Hari</option>
                            <option value="1" {{ old('day_of_week', $doctorSchedule->day_of_week) == 1 ? 'selected' : '' }}>Senin</option>
                            <option value="2" {{ old('day_of_week', $doctorSchedule->day_of_week) == 2 ? 'selected' : '' }}>Selasa</option>
                            <option value="3" {{ old('day_of_week', $doctorSchedule->day_of_week) == 3 ? 'selected' : '' }}>Rabu</option>
                            <option value="4" {{ old('day_of_week', $doctorSchedule->day_of_week) == 4 ? 'selected' : '' }}>Kamis</option>
                            <option value="5" {{ old('day_of_week', $doctorSchedule->day_of_week) == 5 ? 'selected' : '' }}>Jumat</option>
                            <option value="6" {{ old('day_of_week', $doctorSchedule->day_of_week) == 6 ? 'selected' : '' }}>Sabtu</option>
                            <option value="7" {{ old('day_of_week', $doctorSchedule->day_of_week) == 7 ? 'selected' : '' }}>Minggu</option>
                        </select>
                        @error('day_of_week')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="start_time">Waktu Mulai</label>
                        <input type="time" name="start_time" id="start_time" class="form-control @error('start_time') is-invalid @enderror" value="{{ old('start_time', \Carbon\Carbon::parse($doctorSchedule->start_time)->format('H:i')) }}" required>
                        @error('start_time')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="end_time">Waktu Selesai</label>
                        <input type="time" name="end_time" id="end_time" class="form-control @error('end_time') is-invalid @enderror" value="{{ old('end_time', \Carbon\Carbon::parse($doctorSchedule->end_time)->format('H:i')) }}" required>
                        @error('end_time')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="is_available">Tersedia</label>
                        <select name="is_available" id="is_available" class="form-control @error('is_available') is-invalid @enderror">
                            <option value="1" {{ old('is_available', $doctorSchedule->is_available) == true ? 'selected' : '' }}>Ya</option>
                            <option value="0" {{ old('is_available', $doctorSchedule->is_available) == false ? 'selected' : '' }}>Tidak</option>
                        </select>
                        @error('is_available')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    @error('time_overlap')
                        <div class="alert alert-danger mt-3">
                            {{ $message }}
                        </div>
                    @enderror

                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    <a href="{{ route('admin.doctor_schedules.index') }}" class="btn btn-secondary">Batal</a>
                </form>
            </div>
        </div>
    </section>
</div>
@endsection