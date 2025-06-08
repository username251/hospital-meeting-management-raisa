@extends('patient.layout')

@section('title', 'Jadwal ' . $doctor->user->name)

@section('customCss')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
<style>
    .card {
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        border: none;
        transition: transform 0.3s ease;
    }
    .card:hover {
        transform: translateY(-5px);
    }
    .card-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 10px 10px 0 0 !important;
        padding: 15px 20px;
    }
    .schedule-item {
        border-left: 4px solid #007bff;
        padding-left: 15px;
        margin-bottom: 15px;
    }
    .schedule-time-item {
        border-left: 2px solid #28a745;
        padding-left: 10px;
        margin-left: 15px;
    }
    .fc-event {
        cursor: pointer;
        border-radius: 5px;
    }
    .fc-daygrid-event {
        border-radius: 5px;
    }
    .fc-header-toolbar {
        margin-bottom: 1.5rem;
    }
    .badge {
        font-size: 0.8em;
        padding: 6px 10px;
        border-radius: 5px;
    }
    .profile-pic {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        object-fit: cover;
        margin: 0 auto 15px;
        border: 3px solid #fff;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
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
@endsection

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="welcome-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 15px; padding: 30px; margin-bottom: 30px;">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h2 class="mb-2">Jadwal {{ $doctor->user->name }}</h2>
                        <p class="mb-0">Lihat jadwal praktik dan buat janji temu dengan dokter.</p>
                    </div>
                    <div class="col-md-4 text-right">
                        <div class="d-none d-md-block">
                            <i class="fas fa-user-md" style="font-size: 4rem; opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- Doctor Info Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0">
                                <i class="fas fa-user-md mr-2"></i>
                                {{ $doctor->user->name }}
                            </h4>
                            @if($doctor->specialty)
                                <p class="mb-0 text-light">
                                    <i class="fas fa-stethoscope mr-2"></i>
                                    {{ $doctor->specialty->name }}
                                </p>
                            @endif
                        </div>
                        <a href="{{ route('patient.doctor-schedule.index') }}" class="btn btn-light">
                            <i class="fas fa-arrow-left mr-2"></i> Kembali
                        </a>
                    </div>
                </div>
                <div class="card-body text-center">
                    <img class="profile-pic" 
                         src="{{ $doctor->profile_picture ? asset('storage/' . $doctor->profile_picture) : asset('img/default-doctor.jpg') }}" 
                         alt="Foto {{ $doctor->user ? $doctor->user->name : 'Dokter' }}">
                    <div class="row mt-3">
                        <div class="col-md-6">
                            @if($doctor->phone_number)
                                <p class="mb-2">
                                    <i class="fas fa-phone text-success mr-2"></i>
                                    <strong>Telepon:</strong> {{ $doctor->phone_number }}
                                </p>
                            @endif
                            @if($doctor->user->email)
                                <p class="mb-2">
                                    <i class="fas fa-envelope text-primary mr-2"></i>
                                    <strong>Email:</strong> {{ $doctor->user->email }}
                                </p>
                            @endif
                            @if($doctor->license_number)
                                <p class="mb-2">
                                    <i class="fas fa-id-card text-info mr-2"></i>
                                    <strong>No. Lisensi:</strong> {{ $doctor->license_number }}
                                </p>
                            @endif
                        </div>
                        <div class="col-md-6">
                            @if($doctor->bio)
                                <p class="mb-2">
                                    <i class="fas fa-info-circle text-warning mr-2"></i>
                                    <strong>Bio:</strong> {{ $doctor->bio }}
                                </p>
                            @endif
                            @if($doctor->consultation_fee)
                                <p class="mb-2">
                                    <i class="fas fa-money-bill-wave text-success mr-2"></i>
                                    <strong>Biaya Konsultasi:</strong> Rp {{ number_format($doctor->consultation_fee, 0, ',', '.') }}
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Schedule Content -->
            <div class="row">
                <!-- Calendar View -->
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-calendar-alt mr-2"></i>
                                Jadwal Mingguan
                            </h5>
                        </div>
                        <div class="card-body">
                            <div id="calendar"></div>
                        </div>
                    </div>
                </div>

                <!-- Schedule List -->
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-list-ul mr-2"></i>
                                Daftar Jadwal
                            </h5>
                        </div>
                        <div class="card-body">
                            @if($formattedSchedule->count() > 0)
                                @foreach($formattedSchedule as $daySchedule)
                                    <div class="schedule-item mb-3">
                                        <div class="d-flex align-items-center mb-2">
                                            <span class="badge badge-primary mr-2" style="min-width: 80px;">
                                                {{ $daySchedule['day'] }}
                                            </span>
                                        </div>
                                        @foreach($daySchedule['schedules'] as $schedule)
                                            <div class="schedule-time-item mb-2">
                                                <div class="d-flex justify-content-between align-items-center p-2 bg-light rounded">
                                                    <div>
                                                        <i class="fas fa-clock text-success mr-2"></i>
                                                        <strong>{{ $schedule['time_range'] }}</strong>
                                                    </div>
                                                    <small class="text-muted">
                                                        {{ $schedule['slot_duration'] }} menit/sesi
                                                    </small>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    @if(!$loop->last)
                                        <hr>
                                    @endif
                                @endforeach
                            @else
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle mr-2"></i>
                                    Dokter belum menentukan jadwal praktik.
                                </div>
                            @endif
                        </div>
                        @if($formattedSchedule->count() > 0)
                            <div class="card-footer text-center">
                                <small class="text-muted">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Total {{ $doctor->availabilities->count() }} slot jadwal tersedia
                                </small>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var events = @json($events);

    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'timeGridWeek',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'timeGridWeek,timeGridDay'
        },
        locale: 'id',
        slotMinTime: '06:00:00',
        slotMaxTime: '22:00:00',
        height: 'auto',
        events: events,
        eventClick: function(info) {
            var event = info.event;
            var extendedProps = event.extendedProps;
            
            alert(
                'Jadwal: ' + extendedProps.day_name + '\n' +
                'Waktu: ' + extendedProps.time_range + '\n' +
                'Durasi Slot: ' + extendedProps.slot_duration + ' menit'
            );
        },
        eventMouseEnter: function(info) {
            info.el.style.transform = 'scale(1.05)';
            info.el.style.transition = 'transform 0.2s';
        },
        eventMouseLeave: function(info) {
            info.el.style.transform = 'scale(1)';
        },
        dayHeaderFormat: {
            weekday: 'long'
        },
        slotLabelFormat: {
            hour: '2-digit',
            minute: '2-digit',
            hour12: false
        },
        eventTimeFormat: {
            hour: '2-digit',
            minute: '2-digit',
            hour12: false
        }
    });

    calendar.render();
});
</script>
@endpush