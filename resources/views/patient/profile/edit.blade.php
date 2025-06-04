@extends('patient.layout') {{-- Asumsi layout utama Anda ada di resources/views/layouts/app.blade.php --}}

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Edit Profil Saya</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('patient.index') }}">Dashboard Pasien</a></li>
                        <li class="breadcrumb-item active">Edit Profil</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Form Edit Profil Pasien</h3>
            </div>
            <div class="card-body">
                {{-- Pastikan ada data $patient yang di-passing dari controller --}}
                <form action="{{ route('patient.profile.update') }}" method="POST">
                    @csrf
                    @method('PUT') {{-- Gunakan method PUT untuk update --}}

                    <div class="alert alert-info alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <h5><i class="icon fas fa-info"></i> Info!</h5>
                        Informasi di bawah ini adalah detail profil Anda. Harap isi dengan benar.
                    </div>

                    {{-- Informasi Akun User (Name & Email) --}}
                    <div class="form-group">
                        <label for="name">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $patient->user->name) }}" required>
                        @error('name')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="email">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $patient->user->email) }}" required>
                        @error('email')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    {{-- Tidak perlu password di form edit jika tidak ada kebutuhan untuk mengubahnya setiap saat.
                         Jika ingin menambahkan, tambahkan validasi "nullable" dan hanya update jika field tidak kosong. --}}
                    <div class="form-group">
                        <label for="password">Password Baru (Biarkan kosong jika tidak ingin mengubah)</label>
                        <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror">
                        @error('password')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password_confirmation">Konfirmasi Password Baru</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control">
                    </div>

                    <hr>
                    <h5>Detail Medis & Kontak</h5>

                    <div class="form-group">
                        <label for="phone">Telepon</label>
                        <input type="text" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $patient->phone) }}">
                        @error('phone')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="address">Alamat</label>
                        <textarea name="address" id="address" class="form-control @error('address') is-invalid @enderror" rows="3">{{ old('address', $patient->address) }}</textarea>
                        @error('address')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="date_of_birth">Tanggal Lahir</label>
                        <input type="date" name="date_of_birth" id="date_of_birth" class="form-control @error('date_of_birth') is-invalid @enderror" value="{{ old('date_of_birth', $patient->date_of_birth) }}">
                        @error('date_of_birth')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="gender">Jenis Kelamin</label>
                        <select name="gender" id="gender" class="form-control @error('gender') is-invalid @enderror">
                            <option value="">Pilih Jenis Kelamin</option>
                            <option value="Male" {{ old('gender', $patient->gender) == 'Male' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="Female" {{ old('gender', $patient->gender) == 'Female' ? 'selected' : '' }}>Perempuan</option>
                            <option value="Other" {{ old('gender', $patient->gender) == 'Other' ? 'selected' : '' }}>Lainnya</option>
                        </select>
                        @error('gender')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="blood_type">Golongan Darah</label>
                        <input type="text" name="blood_type" id="blood_type" class="form-control @error('blood_type') is-invalid @enderror" value="{{ old('blood_type', $patient->blood_type) }}">
                        @error('blood_type')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="medical_history">Riwayat Medis</label>
                        <textarea name="medical_history" id="medical_history" class="form-control @error('medical_history') is-invalid @enderror" rows="3">{{ old('medical_history', $patient->medical_history) }}</textarea>
                        @error('medical_history')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="allergies">Alergi</label>
                        <textarea name="allergies" id="allergies" class="form-control @error('allergies') is-invalid @enderror" rows="3">{{ old('allergies', $patient->allergies) }}</textarea>
                        @error('allergies')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="current_medications">Obat-obatan Saat Ini</label>
                        <textarea name="current_medications" id="current_medications" class="form-control @error('current_medications') is-invalid @enderror" rows="3">{{ old('current_medications', $patient->current_medications) }}</textarea>
                        @error('current_medications')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary">Update Profil</button>
                    <a href="{{ route('patient.index') }}" class="btn btn-secondary">Batal</a>
                </form>
            </div>
        </div>
    </section>
</div>
@endsection