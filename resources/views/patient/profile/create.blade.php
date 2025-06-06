@extends('patient.layout') {{-- Pastikan path ke layout utama Anda benar --}}

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Lengkapi Profil Pasien Anda</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        {{-- Breadcrumb mungkin tidak perlu di sini jika ini mandatory page --}}
                        <li class="breadcrumb-item active">Lengkapi Profil</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Form Lengkapi Data Pasien</h3>
            </div>
            <div class="card-body">
                <div class="alert alert-warning alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <h5><i class="icon fas fa-exclamation-triangle"></i> Perhatian!</h5>
                    Anda perlu melengkapi profil Anda sebelum dapat mengakses dashboard.
                </div>

                <form action="{{ route('patient.profile.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    {{-- Profile Picture Section --}}
                    <div class="form-group">
                        <label for="profile_picture">Foto Profil</label>
                        <input type="file" name="profile_picture" id="profile_picture" class="form-control-file @error('profile_picture') is-invalid @enderror" accept="image/*">
                        <small class="form-text text-muted">Pilih foto profil Anda (JPG, PNG, GIF, maksimal 2MB)</small>
                        @error('profile_picture')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <hr>

                    {{-- Fields dari tabel `patients` --}}
                    <div class="form-group">
                        <label for="phone">Telepon <span class="text-danger">*</span></label>
                        <input type="text" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}" required>
                        @error('phone')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="date_of_birth">Tanggal Lahir <span class="text-danger">*</span></label>
                        <input type="date" name="date_of_birth" id="date_of_birth" class="form-control @error('date_of_birth') is-invalid @enderror" value="{{ old('date_of_birth') }}" required>
                        @error('date_of_birth')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="address">Alamat <span class="text-danger">*</span></label>
                        <textarea name="address" id="address" class="form-control @error('address') is-invalid @enderror" rows="3" required>{{ old('address') }}</textarea>
                        @error('address')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="gender">Jenis Kelamin <span class="text-danger">*</span></label>
                        <select name="gender" id="gender" class="form-control @error('gender') is-invalid @enderror" required>
                            <option value="">Pilih Jenis Kelamin</option>
                            <option value="Male" {{ old('gender') == 'Male' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="Female" {{ old('gender') == 'Female' ? 'selected' : '' }}>Perempuan</option>
                            <option value="Other" {{ old('gender') == 'Other' ? 'selected' : '' }}>Lainnya</option>
                        </select>
                        @error('gender')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    {{-- Fields opsional --}}
                    <div class="form-group">
                        <label for="blood_type">Golongan Darah</label>
                        <input type="text" name="blood_type" id="blood_type" class="form-control @error('blood_type') is-invalid @enderror" value="{{ old('blood_type') }}">
                        @error('blood_type')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="medical_history">Riwayat Medis</label>
                        <textarea name="medical_history" id="medical_history" class="form-control @error('medical_history') is-invalid @enderror" rows="3">{{ old('medical_history') }}</textarea>
                        @error('medical_history')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="allergies">Alergi</label>
                        <textarea name="allergies" id="allergies" class="form-control @error('allergies') is-invalid @enderror" rows="3">{{ old('allergies') }}</textarea>
                        @error('allergies')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="current_medications">Obat-obatan Saat Ini</label>
                        <textarea name="current_medications" id="current_medications" class="form-control @error('current_medications') is-invalid @enderror" rows="3">{{ old('current_medications') }}</textarea>
                        @error('current_medications')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary">Simpan Profil</button>
                    {{-- Tidak ada tombol batal jika ini mandatory, atau bisa arahkan ke logout --}}
                </form>
            </div>
        </div>
    </section>
</div>

{{-- JavaScript untuk preview gambar (opsional) --}}
<script>
document.getElementById('profile_picture').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        // Cek ukuran file (2MB = 2048KB)
        if (file.size > 2048 * 1024) {
            alert('Ukuran file terlalu besar. Maksimal 2MB.');
            this.value = '';
            return;
        }
        
        // Cek tipe file
        const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
        if (!allowedTypes.includes(file.type)) {
            alert('Tipe file tidak didukung. Gunakan JPG, PNG, atau GIF.');
            this.value = '';
            return;
        }
    }
});
</script>
@endsection