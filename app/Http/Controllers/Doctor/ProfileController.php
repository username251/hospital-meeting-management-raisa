<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log; // Import Log untuk debugging

class ProfileController extends Controller
{
    /**
     * Menampilkan form untuk mengedit profil dokter.
     */
    public function edit()
    {
        $doctor = Auth::user()->doctor; // Mengambil data dokter dari user yang sedang login
        return view('doctor.profile.edit', compact('doctor'));
    }

    /**
     * Mengupdate profil dokter di database.
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        $doctor = $user->doctor;

        try {
            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
                'phone_number' => ['nullable', 'string', 'max:20'],
                'date_of_birth' => ['nullable', 'date'], // Added as optional, similar to patient
                'bio' => ['nullable', 'string', 'max:1000'],
                'profile_picture' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'], // Validasi untuk gambar
            ]);

            // Log the request data for debugging
            Log::info('Update Request Data:', $request->all());

            // Update data di tabel users
            $userData = [
                'name' => $request->name,
                'email' => $request->email,
            ];
            if ($request->filled('phone_number')) {
                $userData['phone_number'] = $request->phone_number;
            }
            $user->update($userData);

            // Update data di tabel doctors
            $doctorData = [];
            if ($request->filled('phone_number')) {
                $doctorData['phone_number'] = $request->phone_number;
            }
            if ($request->filled('date_of_birth')) {
                $doctorData['date_of_birth'] = $request->date_of_birth;
            }
            if ($request->filled('bio')) {
                $doctorData['bio'] = $request->bio;
            }

            // Proses upload foto profil jika ada
            if ($request->hasFile('profile_picture')) {
                // Hapus foto lama jika ada
                if ($doctor->profile_picture && Storage::disk('public')->exists($doctor->profile_picture)) {
                    Storage::disk('public')->delete($doctor->profile_picture);
                }

                // Simpan foto baru dan dapatkan path-nya
                $path = $request->file('profile_picture')->store('profile_pictures/doctors', 'public');
                $doctorData['profile_picture'] = $path;
            }

            $doctor->update($doctorData);

            return redirect()->route('doctor.dashboard')->with('success', 'Profil berhasil diperbarui.');
        } catch (\ValidationException $e) {
            // Log validation errors
            Log::error('Validation Failed: ' . json_encode($e->errors()));
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            // Log other exceptions
            Log::error('Profile Update Failed: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat memperbarui profil. Silakan coba lagi atau hubungi dukungan.');
        }
    }
}