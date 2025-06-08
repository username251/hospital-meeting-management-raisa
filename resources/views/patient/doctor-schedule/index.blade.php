@extends('patient.layout')

@section('title', 'Jadwal Dokter')

@section('customCss')
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
    .schedule-container {
        max-height: 200px;
        overflow-y: auto;
        padding-right: 10px;
    }
    .schedule-day {
        border-left: 4px solid #007bff;
        padding-left: 15px;
        margin-bottom: 10px;
    }
    .badge {
        font-size: 0.8em;
        padding: 6px 10px;
        border-radius: 5px;
    }
    .form-control, .btn {
        border-radius: 8px;
    }
    .doctor-card {
        background: white;
        border-radius: 10px;
        padding: 20px;
        transition: transform 0.3s ease;
    }
    .doctor-card:hover {
        transform: translateY(-3px);
    }
    .profile-pic {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        object-fit: cover;
        margin: 0 auto 15px;
        border: 2px solid #fff;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }
    @media (max-width: 768px) {
        .form-inline .form-group, .form-inline .btn {
            margin-bottom: 10px;
            width: 100%;
        }
        .form-inline .form-control {
            width: 100%;
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
                        <h2 class="mb-2">Jadwal Dokter</h2>
                        <p class="mb-0">Temukan jadwal praktik dokter dan buat janji temu dengan mudah.</p>
                    </div>
                    <div class="col-md-4 text-right">
                        <div class="d-none d-md-block">
                            <i class="fas fa-calendar-alt" style="font-size: 4rem; opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-calendar-alt mr-2"></i>
                        Cari Dokter
                    </h3>
                </div>
                <div class="card-body">
                    <!-- Filter Section -->
                    <form method="GET" action="{{ route('patient.doctor-schedule.index') }}" class="form-inline mb-4">
                        <div class="form-group mr-3">
                            <input type="text" 
                                   class="form-control" 
                                   id="search" 
                                   name="search" 
                                   placeholder="Cari nama dokter..." 
                                   value="{{ request('search') }}">
                        </div>
                        <div class="form-group mr-3">
                            <select class="form-control" name="specialty_id">
                                <option value="">Semua Spesialisasi</option>
                                @foreach($specialties as $specialty)
                                    <option value="{{ $specialty->id }}" 
                                            {{ request('specialty_id') == $specialty->id ? 'selected' : '' }}>
                                        {{ $specialty->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary mr-2">
                            <i class="fas fa-search"></i> Cari
                        </button>
                        <a href="{{ route('patient.doctor-schedule.index') }}" class="btn btn-secondary">
                            <i class="fas fa-sync-alt"></i> Reset
                        </a>
                    </form>

                    <!-- Doctors List -->
                    @if($doctors->count() > 0)
                        <div class="row">
                            @foreach($doctors as $doctor)
                                <div class="col-lg-6 col-md-12 mb-4">
                                    <div class="doctor-card shadow-sm">
                                        <img class="profile-pic" 
                                             src="{{ $doctor->profile_picture ? asset('storage/' . $doctor->profile_picture) : asset('img/default-doctor.jpg') }}" 
                                             alt="Foto {{ $doctor->user ? $doctor->user->name : 'Dokter' }}">
                                        <h5 class="mb-2">
                                            {{ $doctor->user->name }}
                                        </h5>
                                        @if($doctor->specialty)
                                            <p class="text-muted mb-2">
                                                <i class="fas fa-stethoscope mr-1"></i>
                                                {{ $doctor->specialty->name }}
                                            </p>
                                        @endif
                                        @if($doctor->phone_number)
                                            <p class="mb-2">
                                                <i class="fas fa-phone text-success mr-2"></i>
                                                {{ $doctor->phone_number }}
                                            </p>
                                        @endif
                                        @if($doctor->bio)
                                            <p class="text-muted mb-3">
                                                <i class="fas fa-info-circle mr-2"></i>
                                                {{ Str::limit($doctor->bio, 100) }}
                                            </p>
                                        @endif
                                        <h6 class="text-primary mb-3">
                                            <i class="fas fa-clock mr-2"></i>
                                            Jadwal Praktik:
                                        </h6>
                                        @if($doctor->formatted_schedule->count() > 0)
                                            <div class="schedule-container">
                                                @foreach($doctor->formatted_schedule as $daySchedule)
                                                    <div class="schedule-day mb-2">
                                                        <div class="d-flex flex-wrap align-items-center">
                                                            <span class="badge badge-secondary mr-2 mb-1" style="min-width: 70px;">
                                                                {{ $daySchedule['day'] }}
                                                            </span>
                                                            <div class="schedule-times">
                                                                @foreach($daySchedule['times'] as $time)
                                                                    <span class="badge badge-success mr-1 mb-1">
                                                                        {{ $time['time_range'] }}
                                                                    </span>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="alert alert-warning mb-0">
                                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                                Dokter belum menentukan jadwal praktik.
                                            </div>
                                        @endif
                                        <div class="mt-3 text-center">
                                            <a href="{{ route('patient.doctor-schedule.show', $doctor->id) }}" 
                                               class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-calendar-alt mr-1"></i>
                                                Lihat Jadwal
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Pagination -->
                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex justify-content-center">
                                    {{ $doctors->appends(request()->query())->links() }}
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-user-md fa-3x text-muted mb-3"></i>
                            <h5>Tidak ada dokter ditemukan</h5>
                            <p class="text-muted">Silakan coba dengan kata kunci pencarian yang berbeda.</p>
                            <a href="{{ route('appointments.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus mr-1"></i> Buat Janji Temu
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
</div>
@endsection