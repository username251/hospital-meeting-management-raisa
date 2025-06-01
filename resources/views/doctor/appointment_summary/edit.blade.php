@extends('doctor.layout')

@section('content')
    <div class="content-wrapper">
        <div class="content-header"> {{-- Mengikuti struktur content-header dari create.blade --}}
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">Edit Janji Temu</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('doctor.appointments.index') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('doctor.appointments.index') }}">Janji Temu</a></li>
                            <li class="breadcrumb-item active">Edit</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-8"> {{-- Mengikuti lebar kolom dari create.blade --}}
                        <div class="card card-primary"> {{-- Mengikuti gaya card dari create.blade --}}
                            <div class="card-header">
                                <h3 class="card-title">Form Edit Janji Temu</h3>
                            </div>
                            <form action="{{ route('doctor.appointments.update', $appointment->id) }}" method="POST">
                                @csrf
                                @method('PUT') {{-- Gunakan PUT method untuk update --}}
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

                                    <div class="form-group">
                                        <label for="patient_id">Pasien</label>
                                        <select name="patient_id" id="patient_id" class="form-control @error('patient_id') is-invalid @enderror" required>
                                            <option value="">-- Pilih Pasien --</option>
                                            @foreach($patients as $patient)
                                                <option value="{{ $patient->id }}" {{ old('patient_id', $appointment->patient_id) == $patient->id ? 'selected' : '' }}>
                                                    {{ $patient->user?->name ?? 'N/A' }} ({{ $patient->medical_record_number ?? 'N/A' }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('patient_id') <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span> @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="doctor_id">Dokter</label>
                                        <select name="doctor_id" id="doctor_id" class="form-control @error('doctor_id') is-invalid @enderror" required>
                                            <option value="">-- Pilih Dokter --</option>
                                            @foreach($doctors as $doctor)
                                                <option value="{{ $doctor->id }}" {{ old('doctor_id', $appointment->doctor_id) == $doctor->id ? 'selected' : '' }}>
                                                    {{ $doctor->user?->name ?? 'N/A' }} ({{ $doctor->specialty?->name ?? 'N/A' }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('doctor_id') <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span> @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="appointment_date">Tanggal Janji Temu</label>
                                        <input type="date" name="appointment_date" id="appointment_date" class="form-control @error('appointment_date') is-invalid @enderror" value="{{ old('appointment_date', \Carbon\Carbon::parse($appointment->appointment_date)->format('Y-m-d')) }}" required>
                                        @error('appointment_date') <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span> @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="appointment_time">Waktu Janji Temu (Mulai)</label>
                                        {{-- Nama input disamakan dengan create.blade.php: appointment_time --}}
                                        <select name="appointment_time" id="appointment_time" class="form-control @error('appointment_time') is-invalid @enderror" required disabled>
                                            {{-- Opsi akan dimuat secara dinamis oleh JavaScript --}}
                                            {{-- Tetap tampilkan waktu saat ini sebagai opsi default jika ada --}}
                                            @if(old('appointment_time', \Carbon\Carbon::parse($appointment->start_time)->format('H:i')))
                                                <option value="{{ old('appointment_time', \Carbon\Carbon::parse($appointment->start_time)->format('H:i')) }}" selected>
                                                    {{ old('appointment_time', \Carbon\Carbon::parse($appointment->start_time)->format('H:i')) }}
                                                </option>
                                            @else
                                                <option value="">-- Pilih Dokter dan Tanggal Dulu --</option>
                                            @endif
                                        </select>
                                        @error('appointment_time') <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span> @enderror
                                    </div>

                                    {{-- Kolom Waktu Selesai (end_time) dihapus dari form, akan dihitung di controller --}}

                                    <div class="form-group">
                                        <label for="reason">Alasan Janji Temu (Opsional)</label>
                                        <textarea name="reason" id="reason" class="form-control @error('reason') is-invalid @enderror">{{ old('reason', $appointment->reason) }}</textarea>
                                        @error('reason') <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span> @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="status">Status</label>
                                        <select name="status" id="status" class="form-control @error('status') is-invalid @enderror" required>
                                            <option value="scheduled" {{ old('status', $appointment->status) == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                                            <option value="pending" {{ old('status', $appointment->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                                            <option value="confirmed" {{ old('status', $appointment->status) == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                            <option value="completed" {{ old('status', $appointment->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                                            <option value="cancelled" {{ old('status', $appointment->status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                            <option value="rescheduled" {{ old('status', $appointment->status) == 'rescheduled' ? 'selected' : '' }}>Rescheduled</option>
                                            <option value="check-in" {{ old('status', $appointment->status) == 'check-in' ? 'selected' : '' }}>Check-in</option>
                                            <option value="waiting" {{ old('status', $appointment->status) == 'waiting' ? 'selected' : '' }}>Waiting</option>
                                            <option value="in-consultation" {{ old('status', $appointment->status) == 'in-consultation' ? 'selected' : '' }}>In Consultation</option>
                                            <option value="no-show" {{ old('status', $appointment->status) == 'no-show' ? 'selected' : '' }}>No Show</option>
                                        </select>
                                        @error('status') <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span> @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="notes">Catatan (Opsional)</label>
                                        <textarea name="notes" id="notes" class="form-control @error('notes') is-invalid @enderror" rows="3">{{ old('notes', $appointment->notes) }}</textarea>
                                        @error('notes') <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span> @enderror
                                    </div>

                                    @error('time_overlap')
                                        <div class="alert alert-danger mt-3">
                                            <strong>{{ $message }}</strong>
                                        </div>
                                    @enderror

                                </div>
                                <div class="card-footer"> {{-- Mengikuti gaya card-footer dari create.blade --}}
                                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                    <a href="{{ route('doctor.appointments.index') }}" class="btn btn-secondary">Batal</a>
                                </div>
                            </form>
                        </div>
                    </div>
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
                var appointmentTimeSelect = $('#appointment_time'); // Nama ID disamakan
                var currentAppointmentStartTime = '{{ \Carbon\Carbon::parse($appointment->start_time)->format('H:i') }}';

                appointmentTimeSelect.html('<option value="">Memuat slot...</option>');
                appointmentTimeSelect.prop('disabled', true);

                if (doctorId && appointmentDate) {
                    $.ajax({
                        url: '{{ route("doctor.appointments.getAvailableSlots") }}',
                        method: 'GET',
                        data: {
                            doctor_id: doctorId,
                            date: appointmentDate
                        },
                        success: function(response) {
                            appointmentTimeSelect.empty();
                            if (response.length > 0) {
                                var selectedFound = false;
                                $.each(response, function(index, slot) {
                                    var isSelected = (slot.time === currentAppointmentStartTime);
                                    if (isSelected) {
                                        selectedFound = true;
                                    }
                                    appointmentTimeSelect.append($('<option>', {
                                        value: slot.time,
                                        text: slot.display,
                                        selected: isSelected
                                    }));
                                });

                                // Jika waktu janji temu saat ini tidak ada di slot yang tersedia (misal sudah diisi orang lain)
                                // Maka tambahkan opsi waktu saat ini agar tidak kosong
                                if (!selectedFound && currentAppointmentStartTime) {
                                    appointmentTimeSelect.prepend($('<option>', {
                                        value: currentAppointmentStartTime,
                                        text: currentAppointmentStartTime + ' (Saat Ini)',
                                        selected: true
                                    }));
                                }

                                appointmentTimeSelect.prop('disabled', false);

                                // Pilih kembali waktu yang lama jika ada (dari old input atau dari appointment yang diedit)
                                var oldAppointmentTime = '{{ old('appointment_time') }}'; // Mengambil dari old input jika ada
                                if (oldAppointmentTime) {
                                    appointmentTimeSelect.val(oldAppointmentTime);
                                } else if (currentAppointmentStartTime) {
                                    appointmentTimeSelect.val(currentAppointmentStartTime);
                                }

                            } else {
                                appointmentTimeSelect.html('<option value="">Tidak ada slot tersedia untuk tanggal ini</option>');
                                appointmentTimeSelect.prop('disabled', true);
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error("Error loading available slots:", error);
                            appointmentTimeSelect.html('<option value="">Gagal memuat slot</option>');
                            appointmentTimeSelect.prop('disabled', true);
                        }
                    });
                } else {
                    appointmentTimeSelect.html('<option value="">-- Pilih Dokter dan Tanggal Dulu --</option>');
                    appointmentTimeSelect.prop('disabled', true);
                }
            }

            // Panggil saat dokter atau tanggal berubah
            $('#doctor_id, #appointment_date').on('change', loadAvailableSlots);

            // Panggil saat halaman pertama kali dimuat untuk mengisi slot awal
            // Ini akan memuat slot berdasarkan data appointment yang sudah ada
            loadAvailableSlots();

            // Jika ada old input saat validasi gagal, coba load slot lagi
            // Ini tetap penting untuk kasus validasi gagal
            @if (old('doctor_id') && old('appointment_date'))
                loadAvailableSlots();
                var oldTime = '{{ old('appointment_time') }}';
                if (oldTime) {
                    $('#appointment_time').val(oldTime);
                }
            @endif
        });
    </script>
    @endpush
@endsection
