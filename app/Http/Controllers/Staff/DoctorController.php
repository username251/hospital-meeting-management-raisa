<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\User;
use App\Models\Specialty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class DoctorController extends Controller
{
    /**
     * Display a listing of the doctors.
     */
    public function index()
    {
        $doctors = Doctor::with(['user', 'specialty'])->get();
        return view('staff.doctors.index', compact('doctors'));
    }

    /**
     * Show the form for creating a new doctor.
     */
    public function create()
    {
        $specialties = Specialty::all();
        return view('staff.doctors.create', compact('specialties'));
    }

    /**
     * Store a newly created doctor in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'specialty_id' => 'required|exists:specialties,id',
            'phone_number' => 'nullable|string|max:20', // Validasi Nomor Telepon
            'license_number' => 'nullable|string|max:255|unique:doctors,license_number', // Validasi Nomor Lisensi
            'bio' => 'nullable|string|max:1000', // Validasi Bio Dokter
            'consultation_fee' => 'required|numeric|min:0', // Validasi Biaya Konsultasi
        ]);

        // Buat user baru dengan role 'doctor'
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'doctor', // Otomatis set role menjadi 'doctor'
        ]);

        // Buat entri dokter dan asosiasikan dengan user yang baru dibuat
        Doctor::create([
            'user_id' => $user->id,
            'specialty_id' => $request->specialty_id,
            'phone_number' => $request->phone_number,
            'license_number' => $request->license_number,
            'bio' => $request->bio,
            'consultation_fee' => $request->consultation_fee,
        ]);

        return redirect()->route('doctors.index')->with('success', 'Dokter berhasil ditambahkan.');
    }

    /**
     * Display the specified doctor.
     */
    public function show(Doctor $doctor)
    {
        // Biasanya tidak ada view show terpisah untuk CRUD AdminLTE,
        // tapi ini bisa berguna untuk menampilkan detail lengkap.
        return view('staff.doctors.show', compact('doctor'));
    }

    /**
     * Show the form for editing the specified doctor.
     */
    public function edit(Doctor $doctor)
    {
        $specialties = Specialty::all();
        return view('staff.doctors.edit', compact('doctor', 'specialties'));
    }

    /**
     * Update the specified doctor in storage.
     */
    public function update(Request $request, Doctor $doctor)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($doctor->user->id),
            ],
            'password' => 'nullable|string|min:8|confirmed',
            'specialty_id' => 'required|exists:specialties,id',
            'phone_number' => 'nullable|string|max:20',
            'license_number' => ['nullable', 'string', 'max:255', Rule::unique('doctors')->ignore($doctor->id)],
            'bio' => 'nullable|string|max:1000',
            'consultation_fee' => 'required|numeric|min:0',
        ]);

        // Update user data
        $doctor->user->update([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password ? Hash::make($request->password) : $doctor->user->password,
        ]);

        // Update doctor data
        $doctor->update([
            'specialty_id' => $request->specialty_id,
            'phone_number' => $request->phone_number,
            'license_number' => $request->license_number,
            'bio' => $request->bio,
            'consultation_fee' => $request->consultation_fee,
        ]);

        return redirect()->route('doctors.index')->with('success', 'Informasi dokter berhasil diperbarui.');
    }

    /**
     * Remove the specified doctor from storage.
     */
    public function destroy(Doctor $doctor)
    {
        $doctor->user->delete(); // Ini akan menghapus user dan kemudian dokter terkait secara otomatis
        return redirect()->route('doctors.index')->with('success', 'Dokter berhasil dihapus.');
    }
}