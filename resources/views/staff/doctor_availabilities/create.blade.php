@extends('staff.layout') {{-- Sesuaikan dengan layout Anda --}}

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
                        <li class="breadcrumb-item"><a href="{{ route('staff.index') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('staff.doctor_availabilities.index') }}">Jadwal Dokter</a></li>
                        <li class="breadcrumb-item active">Tambah</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Form Tambah Jadwal</h3>
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
                            <label for="doctor_id">Dokter</label>
                            <select name="doctor_id" id="doctor_id" class="form-control @error('doctor_id') is-invalid @enderror">
                                <option value="">Pilih Dokter</option>
                                @foreach ($doctors as $doctor)
                                    <option value="{{ $doctor->id }}" {{ old('doctor_id') == $doctor->id ? 'selected' : '' }}>
                                        {{ $doctor->user->name }}
                                    </option>
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
                            <select name="day_of_week" id="day_of_week" class="form-control @error('day_of_week') is-invalid @enderror">
                                @foreach ($daysOfWeek as $key => $day)
                                    <option value="{{ $key }}" {{ old('day_of_week') == $key ? 'selected' : '' }}>
                                        {{ __($day) }} {{-- Menggunakan helper __() untuk translasi jika ada --}}
                                    </option>
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
                            <input type="time" name="start_time" id="start_time" class="form-control @error('start_time') is-invalid @enderror" value="{{ old('start_time') }}">
                            @error('start_time')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="end_time">Waktu Selesai</label>
                            <input type="time" name="end_time" id="end_time" class="form-control @error('end_time') is-invalid @enderror" value="{{ old('end_time') }}">
                            @error('end_time')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="slot_duration">Durasi Slot (Menit)</label>
                            <input type="number" name="slot_duration" id="slot_duration" class="form-control @error('slot_duration') is-invalid @enderror" value="{{ old('slot_duration', 15) }}" min="10">
                            @error('slot_duration')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">Simpan</button>
                        <a href="{{ route('staff.doctor_availabilities.index') }}" class="btn btn-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>
@endsection