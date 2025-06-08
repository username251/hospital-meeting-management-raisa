@extends('doctor.layout')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Edit Jadwal Ketersediaan</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('doctor.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('doctor.availability.index') }}">Jadwal Saya</a></li>
                        <li class="breadcrumb-item active">Edit Jadwal</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Ubah Jadwal</h3>
                </div>
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

                    <form action="{{ route('doctor.availability.update', $doctorAvailability->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label for="day_of_week">Hari</label>
                            <select name="day_of_week" id="day_of_week" class="form-control" required>
                                @foreach($daysOfWeekForForm as $key => $value)
                                    <option value="{{ $key }}" {{ $selectedDayInteger == $key ? 'selected' : '' }}>
                                        {{ $value }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="start_time">Waktu Mulai</label>
                            <input type="time" name="start_time" id="start_time" class="form-control" value="{{ \Carbon\Carbon::parse($doctorAvailability->start_time)->format('H:i') }}" required>
                        </div>

                        <div class="form-group">
                            <label for="end_time">Waktu Selesai</label>
                            <input type="time" name="end_time" id="end_time" class="form-control" value="{{ \Carbon\Carbon::parse($doctorAvailability->end_time)->format('H:i') }}" required>
                        </div>

                        <div class="form-group">
                            <label for="slot_duration">Durasi Slot (Menit)</label>
                            <input type="number" name="slot_duration" id="slot_duration" class="form-control" value="{{ $doctorAvailability->slot_duration }}" min="5" max="120" required>
                        </div>

                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                        <a href="{{ route('doctor.availability.index') }}" class="btn btn-secondary">Batal</a>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection