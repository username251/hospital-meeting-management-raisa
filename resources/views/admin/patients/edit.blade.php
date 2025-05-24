@extends('admin.layout')

@section('content')

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Edit Data Pasien</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.index  ') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.patients.index') }}">Manajemen Pasien</a></li>
                        <li class="breadcrumb-item active">Edit Pasien</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Form Edit Pasien</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.patients.update', $patient->id) }}" method="POST">
                    @csrf
                    @method('POST') {{-- Karena rute update Anda adalah Route::post --}}
                    {{-- Jika Anda mengubah rute update menjadi PUT/PATCH, maka gunakan @method('PUT') atau @method('PATCH') --}}

                    <div class="form-group">
                        <label for="name">Nama Lengkap Pasien</label>
                        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $patient->user->name) }}" required>
                        @error('name')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $patient->user->email) }}" required>
                        @error('email')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password">Password Baru (opsional, isi jika ingin mengganti)</label>
                        <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror">
                        @error('password')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                        <small class="form-text text-muted">Biarkan kosong jika tidak ingin mengubah password.</small>
                    </div>

                    <div class="form-group">
                        <label for="password_confirmation">Konfirmasi Password Baru</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="phone_number">Nomor Telepon</label>
                        <input type="text" name="phone_number" id="phone_number" class="form-control @error('phone_number') is-invalid @enderror" value="{{ old('phone_number', $patient->user->phone_number) }}">
                        @error('phone_number')
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
                        <input type="date" name="date_of_birth" id="date_of_birth" class="form-control @error('date_of_birth') is-invalid @enderror" value="{{ old('date_of_birth', $patient->date_of_birth ? $patient->date_of_birth->format('Y-m-d') : '') }}">
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
                            <option value="male" {{ old('gender', $patient->gender) == 'male' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="female" {{ old('gender', $patient->gender) == 'female' ? 'selected' : '' }}>Perempuan</option>
                            <option value="other" {{ old('gender', $patient->gender) == 'other' ? 'selected' : '' }}>Lainnya</option>
                        </select>
                        @error('gender')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    <a href="{{ route('admin.patients.index') }}" class="btn btn-secondary">Batal</a>
                </form>
            </div>
        </div>
    </section>
</div>
@endsection