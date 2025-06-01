@extends('patient.layout') {{-- Sesuaikan path ini ke layout utama pasien Anda --}}

@section('customCss')
    {{-- Jika Anda menggunakan Datepicker/Timepicker atau library CSS lainnya, sertakan di sini --}}
    {{-- Contoh: Datepicker dari AdminLTE atau bootstrap-datepicker --}}
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
                            <label for="appointment_time">Waktu Janji Temu</label>
                            <select name="appointment_time" id="appointment_time" class="form-control @error('appointment_time') is-invalid @enderror" required disabled>
                                <option value="">-- Pilih Dokter dan Tanggal Dulu --</option>
                            </select>
                            @error('appointment_time')
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

                        {{-- Input status default: pasien tidak bisa memilih status --}}
                        <input type="hidden" name="status" value="pending">
                        {{-- Atau 'scheduled' jika Anda ingin janji temu langsung dijadwalkan, bukan menunggu persetujuan dokter --}}

                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">Jadwalkan Janji Temu</button>
                        <a href="{{ route('appointments.index') }}" class="btn btn-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>
@endsection

@section('customJs')
    <script src="plugins/jquery/jquery.min.js"></script>
    <script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    {{-- Pastikan Anda punya AdminLTE JS, atau sesuaikan --}}
    <script src="dist/js/adminlte.min.js"></script>
    <script src="dist/js/demo.js"></script>
    {{-- Jika menggunakan Datepicker/Timepicker lain, sertakan di sini --}}
    <script src="plugins/moment/moment.min.js"></script>
    <script src="plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>

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
                var timeSelect = $('#appointment_time');
                var loadingMessage = $('#slot_loading_message');

                timeSelect.empty().append('<option value="">-- Memuat slot... --</option>').prop('disabled', true);
                loadingMessage.text('Memuat slot waktu yang tersedia...');

                if (doctorId && date) {
                    $.ajax({
                        url: "{{ route('appointments.get-available-slots') }}", // Rute AJAX
                        method: 'GET',
                        data: {
                            doctor_id: doctorId,
                            date: date
                        },
                        success: function(response) {
                            timeSelect.empty();
                            if (response.length > 0) {
                                timeSelect.append('<option value="">-- Pilih Waktu --</option>');
                                $.each(response, function(index, slot) {
                                    var isSelected = (slot.time == "{{ old('appointment_time') }}");
                                    timeSelect.append('<option value="' + slot.time + '" ' + (isSelected ? 'selected' : '') + '>' + slot.display + '</option>');
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
                            timeSelect.empty().append('<option value="">Gagal memuat slot.</option>').prop('disabled', true);
                            loadingMessage.text('Gagal memuat slot waktu. Silakan coba lagi.').removeClass('text-success').addClass('text-danger');
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
                 // Hanya tampilkan spesialisasi jika dokter terpilih, walau tanggal belum
                var selectedOption = $('#doctor_id').find('option:selected');
                var specialty = selectedOption.data('specialty');
                $('#doctor_specialty_display').text('Spesialisasi: ' + (specialty || '-'));
            }
        });
    </script>
@endsection