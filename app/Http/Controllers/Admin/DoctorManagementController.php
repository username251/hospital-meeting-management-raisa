<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User; // Import model User
use App\Models\Doctor; // Import model Doctor
use App\Models\Specialty; // Import model Specialty
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash; // Untuk hashing password
use Illuminate\Validation\Rule; // Untuk validasi unique

class DoctorManagementController extends Controller
{
    /**
     * Display a listing of the doctors.
     */
    public function index(Request $request)
    {
        $query = Doctor::with(['user', 'specialty']);

        if ($request->filled('specialty_id')) {
            $query->where('specialty_id', $request->specialty_id);
        }

        $doctors = $query->paginate(10);
        $specialties = Specialty::all(); // Pastikan specialties juga dipass untuk filter

        return view('admin.doctors.list', compact('doctors', 'specialties'));
    }

    /**
     * Show the form for creating a new doctor.
     */
    public function create()
    {
        $specialties = Specialty::all();
        // Pastikan Anda mengarahkan ke view yang benar, contoh: admin.doctors.create
        return view('admin.doctors.create', compact('specialties'));
    }

    /**
     * Store a newly created doctor in storage.
     */
    public function store(Request $request)
    {
        // 1. Validasi input
        $request->validate([
            'name'              => 'required|string|max:255', // UBAH: full_name menjadi name
            'email'             => 'required|string|email|max:255|unique:users,email',
            'password'          => 'required|string|min:8|confirmed',
            'phone_number'      => 'nullable|string|max:20',
            'specialty_id'      => 'required|exists:specialties,id',
            'license_number'    => 'required|string|max:50|unique:doctors,license_number',
            'bio'               => 'nullable|string',
            'consultation_fee'  => 'nullable|numeric|min:0',
        ]);

        // 2. Buat entri di tabel `users` dengan role 'doctor'
        $user = User::create([
            'name'          => $request->name, // UBAH: full_name menjadi name
            'email'         => $request->email,
            'password'      => Hash::make($request->password), 
            'phone_number'  => $request->phone_number,
            'role'          => 'doctor',
        ]);

        // 3. Buat entri di tabel `doctors` dan kaitkan dengan user yang baru dibuat
        Doctor::create([
            'user_id'          => $user->id,
            'specialty_id'     => $request->specialty_id,
            'license_number'   => $request->license_number,
            'bio'              => $request->bio,
            'consultation_fee' => $request->consultation_fee ?? 0,
        ]);

        // 4. Redirect dengan pesan sukses
        return redirect()->route('admin.doctors.create')->with('success', 'Dokter berhasil ditambahkan!'); // UBAH: admin.doctors.list menjadi admin.doctors.index
    }

    /**
     * Display the specified resource.
     * Menggunakan Route Model Binding jika rute Anda adalah /doctors/{doctor}
     * Jika rute Anda adalah /doctors/read, metode ini mungkin tidak relevan.
     */
      public function read()
        {
             // 1. Mengambil data dokter dari database
        //    `Doctor::with('user', 'specialty')` : Mengambil semua dokter dan secara eager load
        //                                          data dari relasi 'user' dan 'specialty'.
        //                                          Ini penting untuk menghindari N+1 query problem.
        //    `->paginate(10)`                   : Mengambil data dengan pagination, 10 item per halaman.
        //                                          Hasilnya adalah objek LengthAwarePaginator.
        $doctors = Doctor::with('user', 'specialty')->paginate(10);

        // 2. Mengambil semua data spesialisasi
        //    Ini dibutuhkan untuk dropdown filter di tampilan `admin.doctors.index`.
        $specialties = Specialty::all();

        // 3. Mengirim data ke view
        //    `compact('doctors', 'specialties')` : Membuat array ['doctors' => $doctors, 'specialties' => $specialties]
        //                                          dan meneruskannya ke view.
        //    `'admin.doctors.index'`           : Nama view Blade yang akan dirender.
        return view('admin.doctors.list', compact('doctors', 'specialties'));
    }

    /**
     * Show the form for editing the specified doctor.
     */
    public function edit($id) // Menerima $id karena rute Anda edit/{id}
    {
        $doctor = Doctor::with('user')->findOrFail($id); // Cari dokter dan eager load user
        $specialties = Specialty::all(); // Ambil semua spesialisasi untuk dropdown
        return view('admin.doctors.edit', compact('doctor', 'specialties'));
    }

    /**
     * Update the specified doctor in storage.
     */
    public function update(Request $request, $id) // Menerima $id karena rute Anda update/{id}
    {
        $doctor = Doctor::findOrFail($id); // Cari dokter
        $user = $doctor->user; // Dapatkan objek User yang terkait

        // Validasi input
        $request->validate(rules: [
            'name'              => 'required|string|max:255',
            'email'            => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id), // Email unik, kecuali untuk user ini sendiri
            ],
            'phone_number'      => 'nullable|string|max:20',
            'specialty_id'      => 'required|exists:specialties,id',
            'license_number'    => [
                'required',
                'string',
                'max:50',
                Rule::unique('doctors', 'license_number')->ignore($doctor->id), // Lisensi unik, kecuali untuk dokter ini sendiri
            ],
            'bio'               => 'nullable|string',
            'consultation_fee'  => 'nullable|numeric|min:0',
            'password'          => 'nullable|string|min:8|confirmed', // Password opsional saat update
        ]);

        // Perbarui data di tabel `users`
        $user->update([
            'name'          => $request->name, // UBAH: full_name menjadi name
            'email'         => $request->email,
            'phone_number'  => $request->phone_number,
        ]);

        // Perbarui password jika disediakan
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password); // UBAH: password_hash menjadi password
            $user->save();
        }

        // Perbarui data di tabel `doctors`
        $doctor->update([
            'specialty_id'     => $request->specialty_id,
            'license_number'   => $request->license_number,
            'bio'              => $request->bio,
            'consultation_fee' => $request->consultation_fee ?? 0,
        ]);

        return redirect()->route('admin.doctors.read')->with('success', 'Data dokter berhasil diperbarui!');
    }

    /**
     * Remove the specified doctor from storage.
     */
    public function destroy($id) // Menerima $id karena rute Anda delete/{id}
    {
        $doctor = Doctor::findOrFail($id); // Cari dokter
        $user = $doctor->user; // Dapatkan user terkait

        try {
            // Hapus dulu dokter, lalu user terkait
            $doctor->delete();
            if ($user) { // Pastikan user ada sebelum dihapus
                $user->delete();
            }
            return redirect()->route('admin.doctors.read')->with('success', 'Dokter berhasil dihapus!');
        } catch (\Illuminate\Database\QueryException $e) {
            // Tangani jika ada masalah lain (misal foreign key di tabel lain)
            return redirect()->with('error', 'Tidak dapat menghapus dokter ini. Mungkin ada data terkait yang menghalangi.');
        }
    }
}