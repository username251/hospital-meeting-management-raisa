<?php

    namespace App\Http\Controllers\Doctor;

    use App\Http\Controllers\Controller;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Validation\Rule;

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

            // Validasi input
            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
                'phone_number' => ['nullable', 'string', 'max:20'],
                'bio' => ['nullable', 'string', 'max:1000'],
                'profile_picture' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'], // Validasi untuk gambar
            ]);

            // Update data di tabel users
            $user->name = $request->name;
            $user->email = $request->email;
            $user->save();

            // Update data di tabel doctors
            $doctor->phone_number = $request->phone_number;
            $doctor->bio = $request->bio;

            // Proses upload foto profil jika ada
            if ($request->hasFile('profile_picture')) {
                // Hapus foto lama jika ada
                if ($doctor->profile_picture && Storage::disk('public')->exists($doctor->profile_picture)) {
                    Storage::disk('public')->delete($doctor->profile_picture);
                }

                // Simpan foto baru dan dapatkan path-nya
                $path = $request->file('profile_picture')->store('profile_pictures/doctors', 'public');
                $doctor->profile_picture = $path;
            }

            $doctor->save();

            return redirect()->route('doctor.profile.edit')->with('success', 'Profil berhasil diperbarui.');
        }
    }
    