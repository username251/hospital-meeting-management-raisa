@extends('patient.layout') {{-- Sesuaikan path ini ke layout utama pasien Anda --}}

@section('customCss')
    {{-- Jika Anda menggunakan Datepicker/Timepicker atau library CSS lainnya, sertakan di sini --}}
    <link rel="stylesheet" href="{{ asset('plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css') }}">
@endsection

@section('content')

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Jadwalkan Janji Temu Baru</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('patient.index') }}">Dashboard</a></li>
                        {{-- Pastikan nama rute 'appointments.index' benar atau ganti ke 'patient.appointments.index' --}}
                        <li class="breadcrumb-item"><a href="{{ route('appointments.index') }}">Janji Temu Saya</a></li>
                        <li class="breadcrumb-item active">Jadwalkan Baru</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Ada beberapa masalah dengan input Anda:</strong>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Form Janji Temu</h3>
                </div>
                {{-- Pastikan nama rute 'appointments.store' benar atau ganti ke 'patient.appointments.store' --}}
                <form action="{{ route('appointments.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="form-group">
                            <label for="doctor_id">Pilih Dokter</label>
                            <select name="doctor_id" id="doctor_id" class="form-control @error('doctor_id') is-invalid @enderror" required>
                                <option value="">-- Pilih Dokter --</option>
                                @foreach ($doctors as $doctor)
                                    <option value="{{ $doctor->id }}"
                                        data-specialty="{{ $doctor->specialty->name ?? 'Tidak Ada Spesialisasi' }}"
                                        {{ old('doctor_id') == $doctor->id ? 'selected' : '' }}>
                                        {{ $doctor->user->name ?? 'N/A' }} ({{ $doctor->specialty->name ?? 'Tidak Ada Spesialisasi' }})
                                    </option>
                                @endforeach
                            </select>
                            @error('doctor_id')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                            <small class="form-text text-muted" id="doctor_specialty_display">Spesialisasi: -</small>
                        </div>

                        <div class="form-group">
                            <label for="appointment_date">Tanggal Janji Temu</label>
                            <input type="date" name="appointment_date" id="appointment_date"
                                class="form-control @error('appointment_date') is-invalid @enderror"
                                value="{{ old('appointment_date', date('Y-m-d')) }}" required min="{{ date('Y-m-d') }}">
                            @error('appointment_date')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group">
                            {{-- UBAH name dan id di sini --}}
                            <label for="start_time_slot">Waktu Janji Temu</label>
                            <select name="start_time_slot" id="start_time_slot" class="form-control @error('start_time_slot') is-invalid @enderror" required disabled>
                                <option value="">-- Pilih Dokter dan Tanggal Dulu --</option>
                            </select>
                            @error('start_time_slot')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                            <small class="form-text text-muted" id="slot_loading_message">Pilih dokter dan tanggal untuk melihat slot waktu yang tersedia.</small>
                        </div>

                        <div class="form-group">
                            <label for="reason">Alasan Janji Temu (Opsional)</label>
                            <textarea name="reason" id="reason" class="form-control @error('reason') is-invalid @enderror" rows="3">{{ old('reason') }}</textarea>
                            @error('reason')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <input type="hidden" name="status" value="pending">

                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">Jadwalkan Janji Temu</button>
                        {{-- Pastikan nama rute 'appointments.index' benar atau ganti ke 'patient.appointments.index' --}}
                        <a href="{{ route('appointments.index') }}" class="btn btn-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>
@endsection

@section('customJs')
    {{-- Pastikan path asset ini benar relatif terhadap folder public --}}
    <script src="{{ asset('plugins/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('dist/js/adminlte.min.js') }}"></script>
    {{-- <script src="{{ asset('dist/js/demo.js') }}"></script> --}} {{-- Demo.js biasanya tidak diperlukan untuk produksi --}}
    <script src="{{ asset('plugins/moment/moment.min.js') }}"></script>
    <script src="{{ asset('plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js') }}"></script>

    <script>
        $(document).ready(function() {
            // Display specialty when doctor is selected
            $('#doctor_id').change(function() {
                var selectedOption = $(this).find('option:selected');
                var specialty = selectedOption.data('specialty');
                $('#doctor_specialty_display').text('Spesialisasi: ' + (specialty || '-'));
                loadAvailableSlots(); // Load slots when doctor changes
            });

            // Load available slots when doctor or date changes
            $('#doctor_id, #appointment_date').change(function() {
                loadAvailableSlots();
            });

            function loadAvailableSlots() {
                var doctorId = $('#doctor_id').val();
                var date = $('#appointment_date').val();
                // UBAH selector di sini
                var timeSelect = $('#start_time_slot');
                var loadingMessage = $('#slot_loading_message');

                timeSelect.empty().append('<option value="">-- Memuat slot... --</option>').prop('disabled', true);
                loadingMessage.text('Memuat slot waktu yang tersedia...');

                if (doctorId && date) {
                    $.ajax({
                        url: "{{ route('api.patient.available-slots') }}", // Pastikan nama rute ini benar
                        method: 'GET',
                        data: {
                            doctor_id: doctorId,
                            date: date
                        },
                        success: function(response) {
                            timeSelect.empty();
                            if (response && Array.isArray(response) && response.length > 0) {
                                timeSelect.append('<option value="">-- Pilih Waktu --</option>');
                                $.each(response, function(index, slot) {
                                    // Pastikan respons memiliki 'start' dan 'display'
                                    // 'slot.start' akan menjadi value (H:i:s)
                                    // 'slot.display' akan menjadi teks yang terlihat (H:i - H:i)
                                    var isSelected = (slot.start == "{{ old('start_time_slot') }}"); // UBAH old() di sini
                                    timeSelect.append('<option value="' + slot.start + '" ' + (isSelected ? 'selected' : '') + '>' + slot.display + '</option>');
                                });
                                timeSelect.prop('disabled', false);
                                loadingMessage.text('Slot waktu tersedia.').removeClass('text-danger').addClass('text-success');
                            } else {
                                timeSelect.append('<option value="">Tidak ada slot tersedia untuk tanggal dan dokter ini.</option>');
                                timeSelect.prop('disabled', true);
                                loadingMessage.text('Tidak ada slot tersedia.').removeClass('text-success').addClass('text-danger');
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error("Error fetching available slots:", error);
                            console.error("Response:", xhr.responseText);
                            timeSelect.empty().append('<option value="">Gagal memuat slot.</option>').prop('disabled', true);
                            loadingMessage.text('Gagal memuat slot waktu. Silakan coba lagi.').removeClass('text-success').addClass('text-danger');
                            try {
                                var errorResponse = JSON.parse(xhr.responseText);
                                if (errorResponse && errorResponse.message) {
                                    // Anda bisa menampilkan ini di tempat yang lebih baik
                                    alert('Error Server: ' + errorResponse.message);
                                }
                            } catch (e) {
                                // Abaikan jika bukan JSON
                            }
                        }
                    });
                } else {
                    timeSelect.empty().append('<option value="">-- Pilih Dokter dan Tanggal Dulu --</option>').prop('disabled', true);
                    loadingMessage.text('Pilih dokter dan tanggal untuk melihat slot waktu yang tersedia.');
                }
            }

            // Inisialisasi awal jika ada nilai old input
            if ($('#doctor_id').val() && $('#appointment_date').val()) {
                loadAvailableSlots();
            } else if ($('#doctor_id').val()) {
                var selectedOption = $('#doctor_id').find('option:selected');
                var specialty = selectedOption.data('specialty');
                $('#doctor_specialty_display').text('Spesialisasi: ' + (specialty || '-'));
            }
        });
    </script>
@endsection
