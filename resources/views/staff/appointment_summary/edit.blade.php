@extends('staff.layout') {{-- Pastikan path ini benar --}}

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
                        <li class="breadcrumb-item"><a href="{{ route('staff.index') }}">Dashboard</a></li> {{-- PERBAIKAN: Nama rute dashboard --}}
                        <li class="breadcrumb-item"><a href="{{ route('staff.appointments.index') }}">Manajemen Janji Temu</a></li>
                        <li class="breadcrumb-item active">Edit</li>
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

                <form action="{{ route('staff.appointments.update', $appointment->id) }}" method="POST">
                    @csrf
                    @method('PUT') {{-- Gunakan PUT method untuk update --}}

                    <div class="form-group">
                        <label for="patient_id">Pasien</label>
                        <select name="patient_id" id="patient_id" class="form-control @error('patient_id') is-invalid @enderror" required>
                            <option value="">Pilih Pasien</option>
                            @foreach($patients as $patient)
                                <option value="{{ $patient->id }}" {{ old('patient_id', $appointment->patient_id) == $patient->id ? 'selected' : '' }}>{{ $patient->user->name }}</option>
                            @endforeach
                        </select>
                        @error('patient_id')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    {{-- Spesialisasi (Tidak perlu diubah jika sudah benar) --}}
                    <div class="form-group">
                        <label for="specialty_id">Spesialisasi</label>
                        <select name="specialty_id" id="specialty_id" class="form-control @error('specialty_id') is-invalid @enderror" required>
                            <option value="">Pilih Spesialisasi</option>
                            @foreach($specialties as $specialty)
                                <option value="{{ $specialty->id }}" {{ old('specialty_id', $appointment->specialty_id) == $specialty->id ? 'selected' : '' }}>{{ $specialty->name }}</option>
                            @endforeach
                        </select>
                        @error('specialty_id')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="doctor_id">Dokter</label>
                        <select name="doctor_id" id="doctor_id" class="form-control @error('doctor_id') is-invalid @enderror" required>
                            <option value="">Pilih Dokter</option>
                            @foreach($doctors as $doctor)
                                <option value="{{ $doctor->id }}" {{ old('doctor_id', $appointment->doctor_id) == $doctor->id ? 'selected' : '' }}>{{ $doctor->user->name }} ({{ $doctor->specialty->name }})</option>
                            @endforeach
                        </select>
                        @error('doctor_id')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="appointment_date">Tanggal Janji Temu</label>
                        <input type="date" name="appointment_date" id="appointment_date" class="form-control @error('appointment_date') is-invalid @enderror" value="{{ old('appointment_date', \Carbon\Carbon::parse($appointment->appointment_date)->format('Y-m-d')) }}" required>
                        @error('appointment_date')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    {{-- PERBAIKAN: Menggunakan dropdown dinamis untuk waktu mulai --}}
                    <div class="form-group">
                        <label for="start_time">Waktu Mulai</label>
                        <select name="start_time" id="start_time" class="form-control @error('start_time') is-invalid @enderror" required>
                            {{-- Opsi akan dimuat secara dinamis oleh JavaScript --}}
                            <option value="{{ old('start_time', \Carbon\Carbon::parse($appointment->start_time)->format('H:i')) }}" selected>
                                {{ old('start_time', \Carbon\Carbon::parse($appointment->start_time)->format('H:i')) }}
                            </option>
                        </select>
                        @error('start_time')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    {{-- Waktu Selesai (Bisa dihilangkan jika selalu 30 menit setelah waktu mulai) --}}
                    {{-- Jika Anda ingin staf bisa mengedit waktu selesai secara manual, biarkan ini.
                         Jika tidak, Anda bisa menghapusnya dari form dan controller update.
                         Untuk konsistensi dengan create, saya sarankan menghapusnya dari form edit juga
                         dan biarkan controller menghitungnya. Tapi untuk saat ini, saya biarkan
                         karena sudah ada di kode Anda. --}}
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
                        <label for="status">Status</label>
                        <select name="status" id="status" class="form-control @error('status') is-invalid @enderror">
                            {{-- PERBAIKAN: Tambahkan 'scheduled' jika sudah ada di ENUM database --}}
                            <option value="scheduled" {{ old('status', $appointment->status) == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                            <option value="pending" {{ old('status', $appointment->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="confirmed" {{ old('status', $appointment->status) == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                            <option value="completed" {{ old('status', $appointment->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ old('status', $appointment->status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            <option value="rescheduled" {{ old('status', $appointment->status) == 'rescheduled' ? 'selected' : '' }}>Rescheduled</option>
                            {{-- Tambahkan status lain yang mungkin Anda gunakan di Queue, misal: 'check-in', 'waiting', 'in-consultation' --}}
                            <option value="check-in" {{ old('status', $appointment->status) == 'check-in' ? 'selected' : '' }}>Check-in</option>
                            <option value="waiting" {{ old('status', $appointment->status) == 'waiting' ? 'selected' : '' }}>Waiting</option>
                            <option value="in-consultation" {{ old('status', $appointment->status) == 'in-consultation' ? 'selected' : '' }}>In Consultation</option>
                            <option value="no-show" {{ old('status', $appointment->status) == 'no-show' ? 'selected' : '' }}>No Show</option>
                        </select>
                        @error('status')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="reason">Alasan Janji Temu (Opsional)</label>
                        <input type="text" name="reason" id="reason" class="form-control @error('reason') is-invalid @enderror" value="{{ old('reason', $appointment->reason) }}">
                        @error('reason')
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

                    @error('time_overlap')
                        <div class="alert alert-danger mt-3">
                            <strong>{{ $message }}</strong>
                        </div>
                    @enderror

                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    <a href="{{ route('staff.appointments.index') }}" class="btn btn-secondary">Batal</a>
                </form>
            </div>
        </div>
    </section>
</div>

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        function loadAvailableSlots() {
            var doctorId = $('#doctor_id').val();
            var appointmentDate = $('#appointment_date').val();
            var startTimeSelect = $('#start_time'); // Menggunakan id start_time

            startTimeSelect.html('<option value="">Memuat slot...</option>');
            startTimeSelect.prop('disabled', true);

            if (doctorId && appointmentDate) {
                $.ajax({
                    url: '{{ route("staff.appointments.getAvailableSlots") }}',
                    method: 'GET',
                    data: {
                        doctor_id: doctorId,
                        date: appointmentDate
                    },
                    success: function(response) {
                        startTimeSelect.empty();
                        if (response.length > 0) {
                            $.each(response, function(index, slot) {
                                startTimeSelect.append($('<option>', {
                                    value: slot.time,
                                    text: slot.display
                                }));
                            });
                            startTimeSelect.prop('disabled', false);

                            // Pilih kembali waktu yang lama jika ada (dari old input atau dari appointment yang diedit)
                            var oldStartTime = '{{ old('start_time', \Carbon\Carbon::parse($appointment->start_time)->format('H:i')) }}';
                            if (oldStartTime) {
                                startTimeSelect.val(oldStartTime);
                            }
                        } else {
                            startTimeSelect.html('<option value="">Tidak ada slot tersedia untuk tanggal ini</option>');
                            startTimeSelect.prop('disabled', true);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("Error loading available slots:", error);
                        startTimeSelect.html('<option value="">Gagal memuat slot</option>');
                        startTimeSelect.prop('disabled', true);
                    }
                });
            } else {
                startTimeSelect.html('<option value="">-- Pilih Dokter dan Tanggal Dulu --</option>');
            }
        }

        // Panggil saat dokter atau tanggal berubah
        $('#doctor_id, #appointment_date').on('change', loadAvailableSlots);

        // Panggil saat halaman pertama kali dimuat untuk mengisi slot awal
        loadAvailableSlots();
    });
</script>
@endpush