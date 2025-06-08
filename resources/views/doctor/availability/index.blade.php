@extends('doctor.layout')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Jadwal Ketersediaan Saya</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('doctor.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Jadwal Saya</li>
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
                    <h3 class="card-title">Tampilan Kalender Jadwal</h3>
                    <div class="card-tools">
                        <a href="{{ route('doctor.availability.create') }}" class="btn btn-primary btn-sm">Tambah Jadwal Baru</a>
                    </div>
                </div>
                <div class="card-body">
                    @if(empty($availabilities))
                        <div class="alert alert-info text-center">Tidak ada jadwal ketersediaan yang ditemukan.</div>
                    @else
                        <div id='calendar'></div>
                    @endif
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title">Daftar Jadwal Mingguan</h3>
                </div>
                <div class="card-body p-0">
                    @if($availabilities->isEmpty())
                        <div class="alert alert-info m-3">Tidak ada jadwal ketersediaan dalam bentuk tabel.</div>
                    @else
                        <table class="table table-striped projects">
                            <thead>
                                <tr>
                                    <th style="width: 15%">Hari</th>
                                    <th style="width: 20%">Waktu Mulai</th>
                                    <th style="width: 20%">Waktu Selesai</th>
                                    <th style="width: 15%">Durasi Slot (Menit)</th>
                                    <th style="width: 10%">Status</th>
                                    <th style="width: 20%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($availabilities as $availability)
                                    <tr>
                                        <td>{{ $availability->day_name_display }}</td>
                                        <td>{{ \Carbon\Carbon::parse($availability->start_time)->format('H:i') }}</td>
                                        <td>{{ \Carbon\Carbon::parse($availability->end_time)->format('H:i') }}</td>
                                        <td>{{ $availability->slot_duration }}</td>
                                        <td>
                                            <form action="{{ route('doctor.availability.toggle', $availability->id) }}" method="POST" style="display: inline-block;">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm {{ $availability->is_available ? 'btn-success' : 'btn-secondary' }}">
                                                    {{ $availability->is_available ? 'Aktif' : 'Nonaktif' }}
                                                </button>
                                            </form>
                                        </td>
                                        <td class="project-actions">
                                            <a class="btn btn-info btn-sm" href="{{ route('doctor.availability.edit', $availability->id) }}">
                                                <i class="fas fa-pencil-alt"></i> Edit
                                            </a>
                                            <form action="{{ route('doctor.availability.destroy', $availability->id) }}" method="POST" style="display: inline-block;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus jadwal ini?');">
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
            </div>
        </div>
    </section>
</div>
@endsection

@push('styles')
<style>
    .card {
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        border: none;
        transition: transform 0.3s ease;
    }
    .card-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 10px 10px 0 0 !important;
        padding: 15px 20px;
    }
    .fc-day-sat {
        background-color: #f8f9fa; /* Light gray for Saturday */
    }
    .fc-day-sun {
        background-color: #fff3cd; /* Light yellow for Sunday */
    }
    .fc-event {
        border-radius: 5px;
        padding: 5px;
        font-size: 0.9em;
    }
    .fc-timegrid-slot-label {
        font-weight: bold;
    }
    @media (max-width: 768px) {
        .fc-header-toolbar {
            flex-direction: column;
            gap: 10px;
        }
        .fc-toolbar-chunk {
            display: flex;
            justify-content: center;
        }
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'timeGridWeek', // Tampilan awal: mingguan dengan slot waktu
            locale: 'id', // Menggunakan bahasa Indonesia
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            slotMinTime: '06:00:00', // Mulai dari 06:00
            slotMaxTime: '22:00:00', // Selesai pada 22:00
            height: 'auto', // Tinggi menyesuaikan konten
            allDaySlot: false, // Sembunyikan baris "All-day"
            dayHeaderFormat: { weekday: 'long' }, // Tampilkan nama hari lengkap
            slotLabelFormat: { hour: '2-digit', minute: '2-digit', hour12: false }, // Format waktu 24 jam
            events: @json($events), // Data events dari controller
            eventContent: function(arg) {
                return {
                    html: '<div style="color: white;">' + arg.event.title + '</div>'
                };
            },
            eventClick: function(info) {
                var event = info.event;
                var extendedProps = event.extendedProps;
                if (extendedProps.edit_url) {
                    if (confirm('Jadwal ini (' + event.title + '). Ingin mengedit jadwal ini?')) {
                        window.location.href = extendedProps.edit_url;
                    }
                } else {
                    alert('Detail Jadwal:\n' +
                          'Hari: ' + event.start.toLocaleString('id-ID', { weekday: 'long' }) + '\n' +
                          'Waktu: ' + event.start.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) + ' - ' + event.end.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) + '\n' +
                          'Durasi Slot: ' + extendedProps.slot_duration + ' menit\n' +
                          'Status: ' + extendedProps.status);
                }
            },
            dayCellDidMount: function(info) {
                if (info.date.getDay() === 0) { // Sunday
                    info.el.style.backgroundColor = '#fff3cd';
                } else if (info.date.getDay() === 6) { // Saturday
                    info.el.style.backgroundColor = '#f8f9fa';
                }
            }
        });
        calendar.render();
    });
</script>
@endpush