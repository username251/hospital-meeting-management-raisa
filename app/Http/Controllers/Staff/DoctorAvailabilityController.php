<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\DoctorAvailability;
use App\Models\Doctor; // Pastikan model Doctor di-import
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DoctorAvailabilityController extends Controller
{
    /**
     * Menampilkan daftar ketersediaan dokter.
     */
    public function index(Request $request)
    {
        $availabilities = DoctorAvailability::with('doctor.user')
                                            ->orderBy('doctor_id')
                                            ->orderByRaw("FIELD(day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')")
                                            ->orderBy('start_time')
                                            ->paginate(10);

        return view('staff.doctor_availabilities.index', compact('availabilities'));
    }

    /**
     * Menampilkan formulir untuk membuat ketersediaan baru.
     */
    public function create()
    {
        $doctors = Doctor::all();
        $daysOfWeek = [
            'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'
        ];
        return view('staff.doctor_availabilities.create', compact('doctors', 'daysOfWeek'));
    }

    /**
     * Menyimpan ketersediaan dokter yang baru dibuat.
     */
    public function store(Request $request)
    {
       $request->validate([
        'doctor_id' => 'required|exists:doctors,id',
        'day_of_week' => 'required|string|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
        'start_time' => 'required|date_format:H:i', // Format HH:mm
        'end_time' => 'required|date_format:H:i|after:start_time', // Format HH:mm dan harus setelah start_time
        ]);

        // Opsional: Tambahkan logika validasi tumpang tindih waktu di sini
        // Misalnya, pastikan tidak ada ketersediaan lain untuk dokter yang sama pada hari dan waktu yang sama
        $existingAvailability = DoctorAvailability::where('doctor_id', $request->doctor_id)
                                                    ->where('day_of_week', $request->day_of_week)
                                                    ->where(function ($query) use ($request) {
                                                        $query->where(function ($q) use ($request) {
                                                            $q->where('start_time', '<', $request->end_time)
                                                              ->where('end_time', '>', $request->start_time);
                                                        });
                                                    })
                                                    ->exists();

        if ($existingAvailability) {
            return redirect()->back()->withInput()->withErrors(['time_conflict' => 'Jadwal yang Anda masukkan bertabrakan dengan jadwal lain yang sudah ada untuk dokter ini pada hari yang sama.']);
        }


        DoctorAvailability::create([
        'doctor_id' => $request->doctor_id,
        'day_of_week' => $request->day_of_week,
        'start_time' => $request->start_time, // Pastikan ini langsung disimpan
        'end_time' => $request->end_time,     // Pastikan ini langsung disimpan
        ]);

        return redirect()->route('staff.doctor_availabilities.index')->with('success', 'Ketersediaan dokter berhasil ditambahkan.');
    }

    /**
     * Menampilkan formulir untuk mengedit ketersediaan.
     */
    public function edit(DoctorAvailability $doctorAvailability)
    {
        $doctors = Doctor::all();
        $daysOfWeek = [
            'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'
        ];
        return view('staff.doctor_availabilities.edit', compact('doctorAvailability', 'doctors', 'daysOfWeek'));
    }

    /**
     * Memperbarui ketersediaan dokter.
     */
    public function update(Request $request, DoctorAvailability $doctorAvailability)
    {
        $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'day_of_week' => ['required', 'string', Rule::in(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'])],
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        // Opsional: Tambahkan logika validasi tumpang tindih waktu saat update
        $existingAvailability = DoctorAvailability::where('doctor_id', $request->doctor_id)
                                                    ->where('day_of_week', $request->day_of_week)
                                                    ->where('id', '!=', $doctorAvailability->id) // Abaikan availability yang sedang diedit
                                                    ->where(function ($query) use ($request) {
                                                        $query->where(function ($q) use ($request) {
                                                            $q->where('start_time', '<', $request->end_time)
                                                              ->where('end_time', '>', $request->start_time);
                                                        });
                                                    })
                                                    ->exists();

        if ($existingAvailability) {
            return redirect()->back()->withInput()->withErrors(['time_conflict' => 'Jadwal yang Anda masukkan bertabrakan dengan jadwal lain yang sudah ada untuk dokter ini pada hari yang sama.']);
        }

        $doctorAvailability->update($request->all());

        return redirect()->route('staff.doctor_availabilities.index')->with('success', 'Ketersediaan dokter berhasil diperbarui.');
    }

    /**
     * Menghapus ketersediaan dokter.
     */
    public function destroy(DoctorAvailability $doctorAvailability)
    {
        $doctorAvailability->delete();
        return redirect()->route('staff.doctor_availabilities.index')->with('success', 'Ketersediaan dokter berhasil dihapus.');
    }
}   