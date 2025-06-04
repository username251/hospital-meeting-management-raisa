@extends('staff.layout') {{-- Sesuaikan dengan layout utama staff Anda --}}

@section('customCss')
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
    <style>
        .fc-event { cursor: pointer; }
        .table th, .table td { vertical-align: middle; }
        .filter-form .form-group { margin-bottom: 0.5rem; margin-right: 1rem; }
        .filter-form .btn { margin-top: 31px; } /* Sejajarkan tombol dengan input select */
        @media (max-width: 767.98px) { /* Atur ulang margin untuk mobile */
            .filter-form .form-group { margin-right: 0; margin-bottom: 1rem; }
            .filter-form .btn { margin-top: 0; width: 100%; }
            .filter-form .btn.btn-secondary { margin-left: 0 !important; margin-top: 0.5rem; }
        }
    </style>
@endsection

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Manajemen Jadwal Ketersediaan Dokter</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('staff.index') }}">Dashboard Staff</a></li>
                        <li class="breadcrumb-item active">Jadwal Dokter</li>
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

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Filter Jadwal</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('staff.doctor_availabilities.index') }}" method="GET" class="filter-form form-inline">
                        <div class="form-group">
                            <label for="doctor_id_filter" class="mr-2">Pilih Dokter:</label>
                            <select name="doctor_id" id="doctor_id_filter" class="form-control">
                                <option value="">Semua Dokter</option>
                                @foreach ($doctors as $doctor)
                                    <option value="{{ $doctor->id }}" {{ $selectedDoctorId == $doctor->id ? 'selected' : '' }}>
                                        {{ $doctor->user->name ?? 'N/A' }} ({{ $doctor->specialty->name ?? 'Umum'}})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>
                        @if($selectedDoctorId) {{-- Tampilkan tombol reset hanya jika filter dokter aktif --}}
                            <a href="{{ route('staff.doctor_availabilities.index') }}" class="btn btn-secondary ml-2"><i class="fas fa-times"></i> Reset Filter</a>
                        @endif
                    </form>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title">Tampilan Kalender Jadwal Ketersediaan Aktif</h3>
                    <div class="card-tools">
                        <a href="{{ route('staff.doctor_availabilities.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Tambah Jadwal Baru
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if($events->isEmpty())
                        <div class="alert alert-info text-center">
                            @if($selectedDoctorId && $availabilities->where('is_available', true)->isEmpty())
                                Tidak ada jadwal ketersediaan aktif untuk dokter yang dipilih.
                            @elseif(!$selectedDoctorId && $availabilities->where('is_available', true)->isEmpty())
                                Belum ada jadwal ketersediaan dokter yang aktif. Silakan tambahkan jadwal baru.
                            @else
                                Tidak ada jadwal aktif yang dapat ditampilkan di kalender untuk filter saat ini.
                            @endif
                        </div>
                    @else
                        <div id='calendar'></div>
                    @endif
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title">Daftar Detail Semua Jadwal Ketersediaan (Termasuk Nonaktif)</h3>
                </div>
                <div class="card-body p-0 table-responsive">
                    @if($availabilities->isEmpty())
                        <div class="alert alert-info m-3 text-center">
                            @if($selectedDoctorId)
                                Tidak ada jadwal ketersediaan untuk dokter yang dipilih.
                            @else
                                Tidak ada jadwal ketersediaan yang ditemukan.
                            @endif
                        </div>
                    @else
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th style="width: 20%">Dokter</th>
                                    <th style="width: 15%">Hari</th>
                                    <th style="width: 15%">Waktu Mulai</th>
                                    <th style="width: 15%">Waktu Selesai</th>
                                    <th style="width: 10%">Durasi Slot</th>
                                    <th style="width: 10%">Status</th>
                                    <th style="width: 15%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($availabilities as $availability)
                                    <tr>
                                        <td>{{ $availability->doctor->user->name ?? 'N/A' }}</td>
                                        <td>{{ $availability->day_of_week }}</td> {{-- Menggunakan properti yang sudah disiapkan --}}
                                        <td>{{ \Carbon\Carbon::parse($availability->start_time)->format('H:i') }}</td>
                                        <td>{{ \Carbon\Carbon::parse($availability->end_time)->format('H:i') }}</td>
                                        <td>{{ $availability->slot_duration }} menit</td>
                                        <td>
                                            <form action="{{ route('staff.doctor_availabilities.toggle', $availability->id) }}" method="POST" style="display: inline-block;">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-xs {{ $availability->is_available ? 'btn-success' : 'btn-secondary' }}" data-toggle="tooltip" title="{{ $availability->is_available ? 'Klik untuk Nonaktifkan' : 'Klik untuk Aktifkan' }}">
                                                    {{ $availability->is_available ? 'Aktif' : 'Nonaktif' }}
                                                </button>
                                            </form>
                                        </td>
                                        <td class="project-actions text-right">
                                            <a class="btn btn-info btn-xs" href="{{ route('staff.doctor_availabilities.edit', $availability->id) }}">
                                                <i class="fas fa-pencil-alt"></i> Edit
                                            </a>
                                            <form action="{{ route('staff.doctor_availabilities.destroy', $availability->id) }}" method="POST" style="display: inline-block;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus jadwal ini? Ini tidak bisa dibatalkan.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-xs">
                                                    <i class="fas fa-trash"></i> Hapus
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
                @if($availabilities instanceof \Illuminate\Pagination\LengthAwarePaginator && $availabilities->isNotEmpty())
                <div class="card-footer clearfix">
                    {{ $availabilities->appends(request()->query())->links() }}
                </div>
                @endif
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/id.js'></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var eventsData = @json($events);

    if (calendarEl && typeof FullCalendar !== 'undefined') {
        // Hanya render kalender jika ada event, atau jika tidak ada filter dokter dan ada availability aktif
        if (eventsData.length > 0 || (!@json($selectedDoctorId) && !@json($availabilities->where('is_available', true)->isEmpty()) ) ) {
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'timeGridWeek',
                locale: 'id',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay listWeek'
                },
                slotMinTime: '06:00:00',
                slotMaxTime: '23:00:00',
                height: 'auto',
                allDaySlot: false,
                events: eventsData,
                eventContent: function(arg) {
                    let eventTitleContainer = document.createElement('div');
                    let titleHtml = '';
                    if (@json($selectedDoctorId) == null && arg.event.extendedProps.doctor_name) {
                        titleHtml = '<b>' + arg.event.extendedProps.doctor_name + '</b><br><i>' + arg.event.title + '</i>';
                    } else {
                        titleHtml = '<i>' + arg.event.title + '</i>';
                    }
                    eventTitleContainer.innerHTML = titleHtml;
                    return { domNodes: [eventTitleContainer] };
                },
                eventClick: function(info) {
                    var event = info.event;
                    var extendedProps = event.extendedProps;
                    if (extendedProps.edit_url) {
                        let doctorName = extendedProps.doctor_name ? extendedProps.doctor_name + ' - ' : '';
                        if (confirm('Jadwal: ' + doctorName + event.title + '\n\nApakah Anda ingin mengedit jadwal ini?')) {
                            window.location.href = extendedProps.edit_url;
                        }
                    }
                },
            });
            calendar.render();
        } else if (calendarEl) { // Jika elemen ada tapi tidak ada event yang sesuai
             calendarEl.innerHTML = '<div class="alert alert-info text-center">Tidak ada jadwal aktif untuk ditampilkan di kalender berdasarkan filter saat ini.</div>';
        }
    }
});
</script>
@endpush
