@extends('patient.layout') {{-- Pastikan path ini sesuai dengan layout utama pasien Anda --}}

@section('customCss')
{{-- Tambahkan CSS kustom Anda di sini jika ada --}}
<style>
    .star-rating {
        display: flex;
        flex-direction: row-reverse; /* Membuat bintang dari kanan ke kiri */
        justify-content: flex-end; /* Rata kanan */
        font-size: 2.5rem; /* Ukuran bintang */
        color: #d3d3d3; /* Warna bintang default (abu-abu) */
    }

    .star-rating input[type="radio"] {
        display: none; /* Sembunyikan radio button asli */
    }

    .star-rating label {
        cursor: pointer;
        padding: 0 0.1em;
        transition: color 0.2s; /* Transisi warna saat hover/checked */
    }

    /* Saat radio button di-hover atau di-check, warnai bintang itu dan bintang sebelumnya */
    .star-rating input[type="radio"]:checked ~ label,
    .star-rating label:hover ~ label,
    .star-rating label:hover {
        color: #ffc107; /* Warna bintang kuning saat di-hover atau dipilih */
    }

    /* Untuk memastikan bintang yang sudah di-check tetap berwarna kuning meskipun tidak di-hover */
    .star-rating input[type="radio"]:checked + label {
        color: #ffc107;
    }

    .card-title {
        font-size: 1.25rem;
    }
    .form-control-lg {
        font-size: 1rem;
        padding: .75rem 1.25rem;
    }
    .btn-lg {
        padding: .75rem 1.25rem;
        font-size: 1rem;
    }
</style>
@endsection

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Beri Feedback Janji Temu</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('patient.index') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('appointments.index') }}">Janji Temu Saya</a></li>
                        <li class="breadcrumb-item active">Beri Feedback</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-8 offset-md-2">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Feedback untuk Janji Temu dengan dr. {{ $appointment->doctor->user->name ?? 'N/A' }}</h3>
                        </div>
                        <form method="POST" action="{{ route('feedback.store', $appointment->id) }}">
                            @csrf
                            <div class="card-body">
                                <div class="form-group mb-4">
                                    <p class="mb-1"><strong>Dokter:</strong> {{ $appointment->doctor->user->name ?? 'N/A' }}</p>
                                    <p class="mb-1"><strong>Spesialisasi:</strong> {{ $appointment->doctor->specialty->name ?? ($appointment->specialty->name ?? 'Umum') }}</p>
                                    <p class="mb-0"><strong>Tanggal Janji Temu:</strong> {{ \Carbon\Carbon::parse($appointment->appointment_date)->translatedFormat('d F Y') }} pukul {{ \Carbon\Carbon::parse($appointment->start_time)->format('H:i') }}</p>
                                </div>
                                <hr>

                                <div class="form-group mt-4">
                                    <label for="rating" class="mb-2 d-block">Rating Anda (1-5 Bintang):</label>
                                    <div class="star-rating">
                                        {{-- Input radio dari 5 ke 1 agar CSS flex-direction: row-reverse berfungsi dengan benar --}}
                                        <input type="radio" id="star5" name="rating" value="5" {{ old('rating') == 5 ? 'checked' : '' }} required/><label for="star5" title="5 bintang">★</label>
                                        <input type="radio" id="star4" name="rating" value="4" {{ old('rating') == 4 ? 'checked' : '' }}/><label for="star4" title="4 bintang">★</label>
                                        <input type="radio" id="star3" name="rating" value="3" {{ old('rating') == 3 ? 'checked' : '' }}/><label for="star3" title="3 bintang">★</label>
                                        <input type="radio" id="star2" name="rating" value="2" {{ old('rating') == 2 ? 'checked' : '' }}/><label for="star2" title="2 bintang">★</label>
                                        <input type="radio" id="star1" name="rating" value="1" {{ old('rating') == 1 ? 'checked' : '' }}/><label for="star1" title="1 bintang">★</label>
                                    </div>
                                    @error('rating')
                                        <span class="invalid-feedback d-block" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <div class="form-group mt-4">
                                    <label for="comment">Komentar (Opsional):</label>
                                    <textarea id="comment" name="comment" class="form-control form-control-lg @error('comment') is-invalid @enderror" rows="5" placeholder="Tuliskan komentar Anda mengenai pelayanan dokter dan rumah sakit...">{{ old('comment') }}</textarea>
                                    @error('comment')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-paper-plane"></i> Kirim Feedback
                                </button>
                                <a href="{{ route('appointments.index') }}" class="btn btn-secondary btn-lg float-right">
                                    <i class="fas fa-times"></i> Batal
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@section('customJs')
<script>
    // Script tambahan jika diperlukan, misalnya untuk interaksi bintang yang lebih kompleks
    // Untuk saat ini, CSS sudah cukup menangani hover dan checked state.
</script>
@endsection
