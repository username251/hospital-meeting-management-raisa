@extends('staff.layout') {{-- Pastikan path ini benar --}}

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Edit Ketersediaan Dokter</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('staff.index') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('staff.doctor_availabilities.index') }}">Manajemen Ketersediaan Dokter</a></li>
                        <li class="breadcrumb-item active">Edit</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Form Edit Ketersediaan Dokter</h3>
            </div>
            <div class="card-body">
                {{-- Menampilkan semua error validasi --}}
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
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                @endif

                <form action="{{ route('staff.doctor_availabilities.update', $doctorAvailability->id) }}" method="POST">
                    @csrf
                    @method('PUT') {{-- Gunakan PUT method untuk update --}}

                    <div class="form-group">
                        <label for="doctor_id">Dokter</label>
                        <select name="doctor_id" id="doctor_id" class="form-control @error('doctor_id') is-invalid @enderror" required>
                            <option value="">Pilih Dokter</option>
                            @foreach($doctors as $doctor)
                                <option value="{{ $doctor->id }}" {{ old('doctor_id', $doctorAvailability->doctor_id) == $doctor->id ? 'selected' : '' }}>{{ $doctor->user->name }} ({{ $doctor->specialty->name ?? 'N/A' }})</option>
                            @endforeach
                        </select>
                        @error('doctor_id')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="day_of_week">Hari Dalam Seminggu</label>
                        <select name="day_of_week" id="day_of_week" class="form-control @error('day_of_week') is-invalid @enderror" required>
                            <option value="">Pilih Hari</option>
                            @foreach($daysOfWeek as $day)
                                <option value="{{ $day }}" {{ old('day_of_week', $doctorAvailability->day_of_week) == $day ? 'selected' : '' }}>{{ $day }}</option>
                            @endforeach
                        </select>
                        @error('day_of_week')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="start_time">Waktu Mulai</label>
                        <input type="time" name="start_time" id="start_time" class="form-control @error('start_time') is-invalid @enderror" value="{{ old('start_time', \Carbon\Carbon::parse($doctorAvailability->start_time)->format('H:i')) }}" required>
                        @error('start_time')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="end_time">Waktu Selesai</label>
                        <input type="time" name="end_time" id="end_time" class="form-control @error('end_time') is-invalid @enderror" value="{{ old('end_time', \Carbon\Carbon::parse($doctorAvailability->end_time)->format('H:i')) }}" required>
                        @error('end_time')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    {{-- Pesan error khusus untuk tumpang tindih waktu --}}
                    @error('time_overlap')
                        <div class="alert alert-danger mt-3">
                            <strong>{{ $message }}</strong>
                        </div>
                    @enderror

                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    <a href="{{ route('staff.doctor_availabilities.index') }}" class="btn btn-secondary">Batal</a>
                </form>
            </div>
        </div>
    </section>
</div>
@endsection