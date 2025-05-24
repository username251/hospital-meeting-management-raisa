<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\User; // Penting: Import model User
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash; // Untuk hashing password
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB; // Untuk transaksi database

class PatientController extends Controller
{
    /**
     * Display a listing of the patients.
     */
    public function index()
    {
        // Ambil pasien dan eager load data user terkait
        $patients = Patient::with('user')->paginate(10);
        return view('admin.patients.index', compact('patients'));
    }

    /**
     * Show the form for creating a new patient.
     */
    public function create()
    {
        return view('admin.patients.create');
    }

    /**
     * Store a newly created patient in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'              => 'required|string|max:255',
            'email'             => 'required|string|email|max:255|unique:users,email',
            'password'          => 'required|string|min:8|confirmed',
            'phone_number'      => 'nullable|string|max:20',
            'address'           => 'nullable|string|max:255',
            'date_of_birth'     => 'nullable|date',
            'gender'            => 'nullable|in:male,female,other',
        ]);

        DB::beginTransaction(); // Mulai transaksi database

        try {
            // 1. Buat entri di tabel `users` dengan role 'patient'
            $user = User::create([
                'name'          => $request->name,
                'email'         => $request->email,
                'password'      => Hash::make($request->password),
                'phone_number'  => $request->phone_number,
                'role'          => 'patient', // Tetapkan peran sebagai 'patient'
            ]);

            // 2. Buat entri di tabel `patients` dan kaitkan dengan user yang baru dibuat
            Patient::create([
                'user_id'       => $user->id,
                'address'       => $request->address,
                'date_of_birth' => $request->date_of_birth,
                'gender'        => $request->gender,
            ]);

            DB::commit(); // Komit transaksi jika berhasil

            return redirect()->route('admin.patients.index')->with('success', 'Pasien berhasil ditambahkan!');

        } catch (\Exception $e) {
            DB::rollBack(); // Rollback transaksi jika terjadi kesalahan
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan saat menambahkan pasien: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     * (Opsional, Anda bisa mengabaikannya jika tidak ada halaman detail pasien terpisah)
     */
    public function show(Patient $patient)
    {
        return redirect()->route('admin.patients.index'); // Redirect kembali ke index
    }

    /**
     * Show the form for editing the specified patient.
     */
    public function edit($id) // Menerima $id sesuai rute
    {
        // Temukan pasien dan eager load data user terkait
        $patient = Patient::with('user')->findOrFail($id);
        return view('admin.patients.edit', compact('patient'));
    }

    /**
     * Update the specified patient in storage.
     */
    public function update(Request $request, $id) // Menerima $id sesuai rute
    {
        $patient = Patient::with('user')->findOrFail($id); // Temukan pasien
        $user = $patient->user; // Dapatkan objek User yang terkait

        $request->validate([
            'name'              => 'required|string|max:255',
            'email'             => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id), // Email unik, kecuali untuk user ini sendiri
            ],
            'password'          => 'nullable|string|min:8|confirmed', // Password opsional saat update
            'phone_number'      => 'nullable|string|max:20',
            'address'           => 'nullable|string|max:255',
            'date_of_birth'     => 'nullable|date',
            'gender'            => 'nullable|in:male,female,other',
        ]);

        DB::beginTransaction(); // Mulai transaksi database

        try {
            // Perbarui data di tabel `users`
            $user->update([
                'name'          => $request->name,
                'email'         => $request->email,
                'phone_number'  => $request->phone_number,
            ]);

            // Perbarui password jika disediakan
            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
                $user->save();
            }

            // Perbarui data di tabel `patients`
            $patient->update([
                'address'       => $request->address,
                'date_of_birth' => $request->date_of_birth,
                'gender'        => $request->gender,
            ]);

            DB::commit(); // Komit transaksi jika berhasil

            return redirect()->route('admin.patients.index')->with('success', 'Data pasien berhasil diperbarui!');

        } catch (\Exception $e) {
            DB::rollBack(); // Rollback transaksi jika terjadi kesalahan
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan saat memperbarui pasien: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified patient from storage.
     */
    public function destroy($id) // Menerima $id sesuai rute
    {
        $patient = Patient::with('user')->findOrFail($id); // Temukan pasien
        $user = $patient->user; // Dapatkan user terkait

        DB::beginTransaction(); // Mulai transaksi database

        try {
            // Hapus dulu entri pasien, yang akan menghapus user_id-nya
            $patient->delete();
            // Kemudian hapus user yang terkait (jika ada)
            if ($user) {
                $user->delete();
            }

            DB::commit(); // Komit transaksi jika berhasil

            return redirect()->route('admin.patients.index')->with('success', 'Pasien berhasil dihapus!');
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack(); // Rollback transaksi jika terjadi kesalahan
            // Tangani jika ada masalah lain (misal foreign key di tabel lain)
            return redirect()->route('admin.patients.index')->with('error', 'Tidak dapat menghapus pasien ini. Mungkin ada data terkait yang menghalangi atau masalah database.');
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback transaksi jika terjadi kesalahan
            return redirect()->route('admin.patients.index')->with('error', 'Terjadi kesalahan saat menghapus pasien: ' . $e->getMessage());
        }
    }
}