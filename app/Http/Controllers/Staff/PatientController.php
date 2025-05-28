<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\User; // Untuk mengelola data user yang terkait dengan pasien
use App\Models\Appointment; // Untuk riwayat janji temu
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Carbon\Carbon; // Pastikan Carbon di-import jika digunakan


class PatientController extends Controller
{
    /**
     * Menampilkan daftar semua pasien terdaftar.
     */
    public function index()
    {
        $patients = Patient::with('user')
                            ->join('users', 'patients.user_id', '=', 'users.id') // Bergabung dengan tabel users
                            ->orderBy('users.name', 'asc') // Urutkan berdasarkan nama user
                            ->select('patients.*') // Penting: Pilih kembali kolom dari tabel patients untuk menghindari konflik
                            ->paginate(10);

        return view('staff.patients.index', compact('patients'));
    }

    /**
     * Menampilkan formulir untuk mendaftarkan pasien baru.
     */
    public function create()
    {
        return view('staff.patients.create');
    }

    /**
     * Menyimpan pasien baru ke sistem.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            // Data spesifik pasien
            'medical_history' => 'nullable|string',
            'allergies' => 'nullable|string',
            'current_medications' => 'nullable|string',
            'blood_type' => 'nullable|string|max:5',
        ]);

        // Buat user baru terlebih dahulu
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make('password'), // Atur password default, atau minta staf mengaturnya
            'role' => 'patient', // Tetapkan role sebagai patient
        ]);

        // Buat pasien baru dan kaitkan dengan user
        $user->patient()->create([
            'phone' => $request->phone,
            'address' => $request->address,
            'date_of_birth' => $request->date_of_birth,
            'gender' => $request->gender,
            'medical_history' => $request->medical_history,
            'allergies' => $request->allergies,
            'current_medications' => $request->current_medications,
            'blood_type' => $request->blood_type,
        ]);

        return redirect()->route('staff.patients.index')->with('success', 'Pasien berhasil ditambahkan!');
    }

    /**
     * Menampilkan detail pasien dan riwayat janji temunya.
     */
    public function show(Patient $patient)
    {
        $patient->load('user'); // Load data user terkait
        // Muat riwayat janji temu, urutkan dari yang terbaru
        $appointments = Appointment::where('patient_id', $patient->id)
                                    ->with(['doctor.user', 'specialty'])
                                    ->orderBy('appointment_date', 'desc')
                                    ->orderBy('start_time', 'desc')
                                    ->paginate(10); // Paginasi riwayat janji temu

        return view('staff.patients.show', compact('patient', 'appointments'));
    }

    /**
     * Menampilkan formulir untuk mengedit informasi pasien.
     */
    public function edit(Patient $patient)
    {
        $patient->load('user'); // Load data user terkait
        return view('staff.patients.edit', compact('patient'));
    }

    /**
     * Memperbarui detail kontak dan informasi dasar pasien.
     */
    public function update(Request $request, Patient $patient)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            // Email harus unik kecuali email pasien yang sedang diedit
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($patient->user->id)],
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            // Data spesifik pasien
            'medical_history' => 'nullable|string',
            'allergies' => 'nullable|string',
            'current_medications' => 'nullable|string',
            'blood_type' => 'nullable|string|max:5',
        ]);

        // Perbarui data user terkait
        $patient->user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        // Perbarui data pasien
        $patient->update([
            'phone' => $request->phone,
            'address' => $request->address,
            'date_of_birth' => $request->date_of_birth,
            'gender' => $request->gender,
            'medical_history' => $request->medical_history,
            'allergies' => $request->allergies,
            'current_medications' => $request->current_medications,
            'blood_type' => $request->blood_type,
        ]);

        return redirect()->route('staff.patients.index')->with('success', 'Informasi pasien berhasil diperbarui!');
    }

    /**
     * Menghapus pasien dari sistem.
     */
    public function destroy(Patient $patient)
    {
        try {
            // Hapus juga user yang terkait dengan pasien ini
            // Pastikan tidak ada appointment yang terkait sebelum menghapus pasien.
            // Anda mungkin ingin mengimplementasikan soft delete atau penanganan kasus jika ada appointment.
            // Untuk sederhana, kita asumsikan semua appointment sudah ditangani atau dihapus.
            if ($patient->user) {
                $patient->user->delete(); // Ini akan juga menghapus pasien karena relasi onDelete('cascade')
            } else {
                $patient->delete(); // Jika user tidak ada (kasus aneh), hapus pasien saja
            }
            return redirect()->route('staff.patients.index')->with('success', 'Pasien berhasil dihapus!');
        } catch (\Exception $e) {
            return redirect()->route('staff.patients.index')->with('error', 'Terjadi kesalahan saat menghapus pasien: ' . $e->getMessage());
        }
    }
}