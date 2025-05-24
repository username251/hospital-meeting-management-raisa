@extends('admin.layout')

@section('content')

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Edit Janji Temu</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.appointments.index') }}">Manajemen Janji Temu</a></li>
                        <li class="breadcrumb-item active">Edit Janji Temu</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Form Edit Janji Temu</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.appointments.update', $appointment->id) }}" method="POST">
                    @csrf
                    @method('POST') {{-- Tetap POST jika rute Anda didefinisikan POST untuk update --}}

                    <div class="form-group">
                        <label for="patient_id">Pasien</label>
                        <select name="patient_id" id="patient_id" class="form-control @error('patient_id') is-invalid @enderror" required>
                            <option value="">Pilih Pasien</option>
                            @foreach ($patients as $patient)
                                <option value="{{ $patient->id }}"
                                    {{ old('patient_id', $appointment->patient_id) == $patient->id ? 'selected' : '' }}>
                                    {{ $patient->user->name ?? 'N/A' }}
                                </option>
                            @endforeach
                        </select>
                        @error('patient_id')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="doctor_id">Dokter</label>
                        <select name="doctor_id" id="doctor_id" class="form-control @error('doctor_id') is-invalid @enderror" required>
                            <option value="">Pilih Dokter</option>
                            @foreach ($doctors as $doctor)
                                <option value="{{ $doctor->id }}"
                                    {{ old('doctor_id', $appointment->doctor_id) == $doctor->id ? 'selected' : '' }}>
                                    {{ $doctor->user->name ?? 'N/A' }}
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
                        <label for="specialty_id">Spesialisasi (Opsional)</label>
                        <select name="specialty_id" id="specialty_id" class="form-control @error('specialty_id') is-invalid @enderror">
                            <option value="">Pilih Spesialisasi</option>
                            @foreach ($specialties as $specialty)
                                <option value="{{ $specialty->id }}"
                                    {{ old('specialty_id', $appointment->specialty_id) == $specialty->id ? 'selected' : '' }}>
                                    {{ $specialty->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('specialty_id')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    {{-- Bagian form lainnya tetap sama --}}
                    <div class="form-group">
                        <label for="appointment_date">Tanggal Janji Temu</label>
                        <input type="date" name="appointment_date" id="appointment_date" class="form-control @error('appointment_date') is-invalid @enderror" value="{{ old('appointment_date', \Carbon\Carbon::parse($appointment->appointment_date)->format('Y-m-d')) }}" required>
                        @error('appointment_date')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="start_time">Waktu Mulai</label>
                        <input type="time" name="start_time" id="start_time" class="form-control @error('start_time') is-invalid @enderror" value="{{ old('start_time', \Carbon\Carbon::parse($appointment->start_time)->format('H:i')) }}" required>
                        @error('start_time')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="end_time">Waktu Selesai</label>
                        <input type="time" name="end_time" id="end_time" class="form-control @error('end_time') is-invalid @enderror" value="{{ old('end_time', \Carbon\Carbon::parse($appointment->end_time)->format('H:i')) }}" required>
                        @error('end_time')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="reason">Alasan Janji Temu (Opsional)</label>
                        <textarea name="reason" id="reason" class="form-control @error('reason') is-invalid @enderror" rows="3">{{ old('reason', $appointment->reason) }}</textarea>
                        @error('reason')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="status">Status</label>
                        <select name="status" id="status" class="form-control @error('status') is-invalid @enderror" required>
                            <option value="pending" {{ old('status', $appointment->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="confirmed" {{ old('status', $appointment->status) == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                            <option value="completed" {{ old('status', $appointment->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ old('status', $appointment->status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            <option value="rescheduled" {{ old('status', $appointment->status) == 'rescheduled' ? 'selected' : '' }}>Rescheduled</option>
                        </select>
                        @error('status')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="notes">Catatan (Opsional)</label>
                        <textarea name="notes" id="notes" class="form-control @error('notes') is-invalid @enderror" rows="3">{{ old('notes', $appointment->notes) }}</textarea>
                        @error('notes')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary">Perbarui Janji Temu</button>
                    <a href="{{ route('admin.appointments.index') }}" class="btn btn-secondary">Batal</a>
                </form>
            </div>
        </div>
    </section>
</div>
@endsection