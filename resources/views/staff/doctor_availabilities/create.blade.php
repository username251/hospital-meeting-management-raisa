@extends('staff.layout') {{-- Sesuaikan dengan layout utama staff Anda --}}

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Tambah Jadwal Ketersediaan Dokter</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('staff.index') }}">Dashboard Staff</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('staff.doctor_availabilities.index') }}">Manajemen Jadwal</a></li>
                        <li class="breadcrumb-item active">Tambah Jadwal</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Form Tambah Jadwal Ketersediaan</h3>
                </div>
                <form action="{{ route('staff.doctor_availabilities.store') }}" method="POST">
                    @csrf
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

                        <div class="form-group">
                            <label for="doctor_id">Pilih Dokter <span class="text-danger">*</span></label>
                            <select name="doctor_id" id="doctor_id" class="form-control @error('doctor_id') is-invalid @enderror" required>
                                <option value="">-- Pilih Dokter --</option>
                                @foreach ($doctors as $doctor)
                                    <option value="{{ $doctor->id }}" {{ old('doctor_id') == $doctor->id ? 'selected' : '' }}>
                                        {{ $doctor->user->name ?? 'N/A' }} (Spesialis: {{ $doctor->specialty->name ?? 'Umum' }})
                                    </option>
                                @endforeach
                            </select>
                            @error('doctor_id')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="day_of_week">Hari <span class="text-danger">*</span></label>
                            <select name="day_of_week" id="day_of_week" class="form-control @error('day_of_week') is-invalid @enderror" required>
                                <option value="">-- Pilih Hari --</option>
                                {{-- $daysOfWeekForForm dikirim dari controller --}}
                                {{-- Key adalah integer (0-6), Value adalah nama hari Indonesia --}}
                                @foreach($daysOfWeekForForm as $key => $day)
                                    <option value="{{ $key }}" {{ old('day_of_week') == $key ? 'selected' : '' }}>
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
                                    <input type="time" name="start_time" id="start_time" class="form-control @error('start_time') is-invalid @enderror" value="{{ old('start_time') }}" required>
                                    @error('start_time')
                                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="end_time">Waktu Selesai <span class="text-danger">*</span></label>
                                    <input type="time" name="end_time" id="end_time" class="form-control @error('end_time') is-invalid @enderror" value="{{ old('end_time') }}" required>
                                    @error('end_time')
                                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="slot_duration">Durasi Slot (Menit) <span class="text-danger">*</span></label>
                            <input type="number" name="slot_duration" id="slot_duration" class="form-control @error('slot_duration') is-invalid @enderror" value="{{ old('slot_duration', 30) }}" min="5" max="120" required>
                             <small class="form-text text-muted">Contoh: 30 (untuk 30 menit)</small>
                            @error('slot_duration')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">Simpan Jadwal</button>
                        <a href="{{ route('staff.doctor_availabilities.index') }}" class="btn btn-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>
@endsection