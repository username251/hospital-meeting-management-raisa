<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use Hash;
use Illuminate\Http\Request;
use App\Models\Patient;
use App\Models\User; // Pastikan model User di-import
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class PatientProfileController extends Controller
{
    /**
     * Menampilkan formulir untuk membuat profil pasien baru.
     * Pasien akan diarahkan ke sini jika belum memiliki profil.
     */
    public function create()
    {
        $user = Auth::user();

        // Jika user sudah memiliki profil pasien, redirect ke halaman edit
        if ($user->patient) {
            return redirect()->route('patient.profile.edit');
        }

        return view('patient.profile.create');
    }

    /**
     * Menyimpan data profil pasien baru.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        // Mencegah pembuatan profil ganda jika sudah ada
        if ($user->patient) {
            return redirect()->route('patient.index')->with('error', 'Anda sudah memiliki profil pasien. Tidak dapat membuat yang baru.');
        }

        $request->validate([
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'required|date',
            'address' => 'required|string',
            'gender' => ['required', Rule::in(['Male', 'Female', 'Other'])],
            // 'phone_number' di tabel 'users' jika ingin disinkronkan dari form ini
            // Jika Anda hanya ingin mengisi 'phone' di tabel 'patients', ini bisa dihapus dari validasi request
            // 'phone_number' => 'nullable|string|max:255',
            'medical_history' => 'nullable|string',
            'allergies' => 'nullable|string',
            'current_medications' => 'nullable|string',
            'blood_type' => 'nullable|string|max:5',
        ]);

        // Buat profil pasien baru
        Patient::create([
            'user_id' => $user->id,
            'phone' => $request->phone,
            'date_of_birth' => $request->date_of_birth,
            'address' => $request->address,
            'gender' => $request->gender,
            'medical_history' => $request->medical_history,
            'allergies' => $request->allergies,
            'current_medications' => $request->current_medications,
            'blood_type' => $request->blood_type,
        ]);

        // Opsional: Sinkronkan phone_number ke tabel users jika ada di form
        // if ($request->has('phone_number')) {
        //     $user->update(['phone_number' => $request->phone_number]);
        // } else if ($request->has('phone')) {
        //     // Jika phone_number di tabel users juga diisi dari field 'phone' di tabel patients
        //     $user->update(['phone_number' => $request->phone]);
        // }

        // Arahkan ke dashboard setelah profil dibuat
        return redirect()->route('patient.index')->with('success', 'Profil berhasil dibuat!');
    }

    /**
     * Menampilkan formulir untuk mengedit profil pasien yang sudah ada.
     */
    public function edit()
    {
        $user = Auth::user();
        $patient = $user->patient;

        // Jika user belum memiliki profil pasien, arahkan ke halaman pembuatan profil
        if (!$patient) {
            return redirect()->route('patient.profile.create');
        }

        // Pastikan relasi user di-load agar bisa mengakses $patient->user->name dll.
        $patient->load('user');

        return view('patient.profile.edit', compact('patient'));
    }

    /**
     * Memperbarui data profil pasien.
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        $patient = $user->patient;

        // Jika user belum memiliki profil pasien, arahkan ke halaman pembuatan profil
        if (!$patient) {
            return redirect()->route('patient.profile.create')->with('error', 'Profil pasien tidak ditemukan. Silakan lengkapi profil Anda.');
        }

        $request->validate([
            'name' => 'required|string|max:255', // Nama user di tabel users
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($user->id), // Email unik di tabel users
            ],
            'password' => 'nullable|string|min:8|confirmed', // Password opsional saat update
            'phone' => 'nullable|string|max:20', // phone di tabel patients
            'date_of_birth' => 'required|date',
            'address' => 'required|string',
            'gender' => ['required', Rule::in(['Male', 'Female', 'Other'])],
            // 'phone_number' => 'nullable|string|max:255', // Ini untuk kolom di tabel users (jika ingin diupdate)
            'medical_history' => 'nullable|string',
            'allergies' => 'nullable|string',
            'current_medications' => 'nullable|string',
            'blood_type' => 'nullable|string|max:5',
        ]);

        // Update data User
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            // 'phone_number' => $request->phone_number, // Opsional: update phone_number di tabel users
            // Atau jika 'phone_number' di tabel 'users' sama dengan 'phone' di tabel 'patients'
            'phone_number' => $request->phone,
        ]);

        // Jika password diisi, update password
        if ($request->filled('password')) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        // Update data Patient
        $patient->update([
            'phone' => $request->phone,
            'date_of_birth' => $request->date_of_birth,
            'address' => $request->address,
            'gender' => $request->gender,
            'medical_history' => $request->medical_history,
            'allergies' => $request->allergies,
            'current_medications' => $request->current_medications,
            'blood_type' => $request->blood_type,
            // 'phone_number' => $request->phone_number, // Jika ada di tabel patients dan beda dari 'phone'
        ]);

        return redirect()->route('patient.index')->with('success', 'Profil berhasil diperbarui!');
    }
}