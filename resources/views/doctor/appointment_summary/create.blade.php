@extends('doctor.layout')

@section('content')
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">Buat Janji Temu Baru</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('doctor.appointments.index') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('doctor.appointments.index') }}">Janji Temu</a></li>
                            <li class="breadcrumb-item active">Buat Baru</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-8">
                        <div class="card card-primary">
                            <div class="card-header">
                                <h3 class="card-title">Form Janji Temu</h3>
                            </div>
                            <form action="{{ route('doctor.appointments.store') }}" method="POST">
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
                                        <div class="alert alert-danger">
                                            {{ session('error') }}
                                        </div>
                                    @endif

                                    <div class="form-group">
                                        <label for="patient_id">Pasien</label>
                                        <select name="patient_id" id="patient_id" class="form-control @error('patient_id') is-invalid @enderror" required>
                                            <option value="">-- Pilih Pasien --</option>
                                            @foreach ($patients as $patient)
                                                <option value="{{ $patient->id }}" {{ old('patient_id') == $patient->id ? 'selected' : '' }}>
                                                    {{ $patient->user->name ?? 'N/A' }} ({{ $patient->medical_record_number ?? 'N/A' }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('patient_id') <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span> @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="doctor_id">Dokter</label>
                                        <select name="doctor_id" id="doctor_id" class="form-control @error('doctor_id') is-invalid @enderror" required>
                                            <option value="">-- Pilih Dokter --</option>
                                            @foreach ($doctors as $doctor)
                                                <option value="{{ $doctor->id }}" {{ old('doctor_id') == $doctor->id ? 'selected' : '' }}>
                                                    {{ $doctor->user->name ?? 'N/A' }} ({{ $doctor->specialty->name ?? 'N/A' }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('doctor_id') <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span> @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="appointment_date">Tanggal Janji Temu</label>
                                        <input type="date" name="appointment_date" id="appointment_date" class="form-control @error('appointment_date') is-invalid @enderror" value="{{ old('appointment_date') }}" required>
                                        @error('appointment_date') <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span> @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="appointment_time">Waktu Janji Temu (Mulai)</label>
                                        {{-- Nama input tetap appointment_time sesuai request validation --}}
                                        <select name="appointment_time" id="appointment_time" class="form-control @error('appointment_time') is-invalid @enderror" required disabled>
                                            <option value="">-- Pilih Dokter dan Tanggal Dulu --</option>
                                        </select>
                                        @error('appointment_time') <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span> @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="reason">Alasan Janji Temu (Opsional)</label>
                                        <textarea name="reason" id="reason" class="form-control @error('reason') is-invalid @enderror">{{ old('reason') }}</textarea>
                                        @error('reason') <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span> @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="status">Status</label>
                                        <select name="status" id="status" class="form-control @error('status') is-invalid @enderror" required>
                                            <option value="scheduled" {{ old('status') == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                                            <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                            <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                            <option value="cancelled" {{ old('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                        </select>
                                        @error('status') <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span> @enderror
                                    </div>

                                </div>
                                <div class="card-footer">
                                    <button type="submit" class="btn btn-primary">Buat Janji Temu</button>
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
                var appointmentTimeSelect = $('#appointment_time');

                appointmentTimeSelect.html('<option value="">Memuat slot...</option>');
                appointmentTimeSelect.prop('disabled', true);

                if (doctorId && appointmentDate) {
                    $.ajax({
                        url: '{{ route("doctor.appointments.getAvailableSlots") }}', // Gunakan rute API yang baru
                        method: 'GET',
                        data: {
                            doctor_id: doctorId,
                            date: appointmentDate
                        },
                        success: function(response) {
                            appointmentTimeSelect.empty();
                            if (response.length > 0) {
                                $.each(response, function(index, slot) {
                                    appointmentTimeSelect.append($('<option>', {
                                        value: slot.time,
                                        text: slot.display
                                    }));
                                });
                                appointmentTimeSelect.prop('disabled', false);
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
                }
            }

            // Panggil saat dokter atau tanggal berubah
            $('#doctor_id, #appointment_date').on('change', loadAvailableSlots);

            // Jika ada old input saat validasi gagal, coba load slot lagi
            @if (old('doctor_id') && old('appointment_date'))
                loadAvailableSlots();
                // Pilih kembali old time jika ada
                var oldTime = '{{ old('appointment_time') }}';
                if (oldTime) {
                    $('#appointment_time').val(oldTime);
                }
            @endif
        });
    </script>
    @endpush
@endsection