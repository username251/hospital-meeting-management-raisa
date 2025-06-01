<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\DoctorAvailability;
use Carbon\Carbon;

class DoctorAvailabilityController extends Controller
{
    /**
     * Get days of week mapping
     */
    private function getDaysMapping()
    {
        // Mapping untuk integer (0-6)
        $integerDays = [
            0 => 'Sunday', 1 => 'Monday', 2 => 'Tuesday', 
            3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday'
        ];

        // Mapping untuk string bahasa Inggris
        $englishDays = [
            'Sunday' => 'Sunday', 'Monday' => 'Monday', 'Tuesday' => 'Tuesday',
            'Wednesday' => 'Wednesday', 'Thursday' => 'Thursday', 'Friday' => 'Friday', 'Saturday' => 'Saturday'
        ];

        // Mapping untuk string bahasa Indonesia
        $indonesianDays = [
            'Minggu' => 'Sunday', 'Senin' => 'Monday', 'Selasa' => 'Tuesday',
            'Rabu' => 'Wednesday', 'Kamis' => 'Thursday', 'Jumat' => 'Friday', 'Sabtu' => 'Saturday'
        ];

        return [
            'integer' => $integerDays,
            'english' => $englishDays,
            'indonesian' => $indonesianDays
        ];
    }

    /**
     * Convert day value to display name
     */
     private function getDayName($dayOfWeek)
    {
        // Fungsi helper untuk mendapatkan nama hari dari angka
        $dayNames = [
            0 => 'Minggu',
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
        ];
        return $dayNames[(int)$dayOfWeek] ?? 'Tidak Diketahui';
    }

    public function index()
    {
        $doctor = Auth::user()->doctor;

        if (!$doctor) {
            return redirect()->route('dashboard')->with('error', 'Data dokter tidak ditemukan.');
        }

        $availabilities = $doctor->availabilities()
                                 ->where('is_available', true) // Hanya tampilkan yang aktif di kalender
                                 ->orderBy('day_of_week')
                                 ->orderBy('start_time')
                                 ->get();

        // --- Perubahan DI SINI: Tambahkan 'day_name' ke setiap objek $availability ---
        $availabilities->each(function ($availability) {
        $availability->day_name = $this->getDayName($availability->day_of_week);
        });
        // --- Akhir Perubahan ---

        // Transformasi data untuk FullCalendar (sudah benar)
        $events = $availabilities->map(function ($availability) {
            return [
                'id' => $availability->id,
                'title' => 'Tersedia (' . Carbon::parse($availability->start_time)->format('H:i') . ' - ' . Carbon::parse($availability->end_time)->format('H:i') . ')', // Judul lebih informatif
                'startTime' => Carbon::parse($availability->start_time)->format('H:i:s'),
                'endTime' => Carbon::parse($availability->end_time)->format('H:i:s'),
                'daysOfWeek' => [(int)$availability->day_of_week],
                'color' => $availability->is_available ? '#28a745' : '#dc3545',
                'extendedProps' => [
                    'slot_duration' => $availability->slot_duration,
                    'status' => $availability->is_available ? 'Aktif' : 'Nonaktif',
                    'edit_url' => route('doctor.availability.edit', $availability->id),
                ]
            ];
        });

        // Mapping untuk form create/edit (ini masih perlu untuk dropdown di form)
        $daysOfWeek = [
            0 => 'Sunday', 1 => 'Monday', 2 => 'Tuesday',
            3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday'
        ];

        return view('doctor.availability.index', compact('availabilities', 'daysOfWeek', 'events'));
    }

    /**
     * Show the form for creating a new doctor availability.
     */
    public function create()
    {
        $daysOfWeek = [
            0 => 'Sunday', 1 => 'Monday', 2 => 'Tuesday', 
            3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday'
        ];
        return view('doctor.availability.create', compact('daysOfWeek'));
    }

    /**
     * Store a newly created doctor availability in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'day_of_week' => 'required|integer|between:0,6',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'slot_duration' => 'required|integer|min:10',
        ]);

        $doctor = Auth::user()->doctor;

        if (!$doctor) {
            return back()->with('error', 'Data dokter tidak ditemukan.');
        }

        // Cek konflik jadwal
        $conflict = DoctorAvailability::where('doctor_id', $doctor->id)
            ->where('day_of_week', $request->day_of_week)
            ->where(function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->whereTime('start_time', '<', $request->end_time)
                      ->whereTime('end_time', '>', $request->start_time);
                });
            })
            ->exists();

        if ($conflict) {
            return back()->withInput()->with('error', 'Ada konflik jadwal. Dokter sudah tersedia pada waktu tersebut.');
        }

        DoctorAvailability::create([
            'doctor_id' => $doctor->id,
            'day_of_week' => $request->day_of_week,
            'start_time' => Carbon::parse($request->start_time)->format('H:i:s'),
            'end_time' => Carbon::parse($request->end_time)->format('H:i:s'),
            'slot_duration' => $request->slot_duration,
            'is_available' => true,
        ]);

        return redirect()->route('doctor.availability.index')->with('success', 'Jadwal ketersediaan berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified doctor availability.
     */
    public function edit(DoctorAvailability $doctorAvailability)
    {
        $doctor = Auth::user()->doctor;
        
        if (!$doctor || $doctorAvailability->doctor_id !== $doctor->id) {
            abort(403, 'Unauthorized action.');
        }

        $daysOfWeek = [
            0 => 'Sunday', 1 => 'Monday', 2 => 'Tuesday', 
            3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday'
        ];
        
        return view('doctor.availability.edit', compact('doctorAvailability', 'daysOfWeek'));
    }

    /**
     * Update the specified doctor availability in storage.
     */
    public function update(Request $request, DoctorAvailability $doctorAvailability)
    {
        $doctor = Auth::user()->doctor;
        
        if (!$doctor || $doctorAvailability->doctor_id !== $doctor->id) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'day_of_week' => 'required|integer|between:0,6',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'slot_duration' => 'required|integer|min:10',
        ]);

        // Cek konflik jadwal
        $conflict = DoctorAvailability::where('doctor_id', $doctorAvailability->doctor_id)
            ->where('day_of_week', $request->day_of_week)
            ->where('id', '!=', $doctorAvailability->id)
            ->where(function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->whereTime('start_time', '<', $request->end_time)
                      ->whereTime('end_time', '>', $request->start_time);
                });
            })
            ->exists();

        if ($conflict) {
            return back()->withInput()->with('error', 'Ada konflik jadwal. Dokter sudah tersedia pada waktu tersebut.');
        }

        $doctorAvailability->update([
            'day_of_week' => $request->day_of_week,
            'start_time' => Carbon::parse($request->start_time)->format('H:i:s'),
            'end_time' => Carbon::parse($request->end_time)->format('H:i:s'),
            'slot_duration' => $request->slot_duration,
        ]);

        return redirect()->route('doctor.availability.index')->with('success', 'Jadwal ketersediaan berhasil diperbarui.');
    }

    /**
     * Remove the specified doctor availability from storage.
     */
    public function destroy(DoctorAvailability $doctorAvailability)
    {
        $doctor = Auth::user()->doctor;
        
        if (!$doctor || $doctorAvailability->doctor_id !== $doctor->id) {
            abort(403, 'Unauthorized action.');
        }

        $doctorAvailability->delete();
        return redirect()->route('doctor.availability.index')->with('success', 'Jadwal ketersediaan berhasil dihapus.');
    }

    /**
     * Toggle the availability status of a doctor availability.
     */
    public function toggleAvailability(DoctorAvailability $doctorAvailability)
    {
        $doctor = Auth::user()->doctor;
        
        if (!$doctor || $doctorAvailability->doctor_id !== $doctor->id) {
            abort(403, 'Unauthorized action.');
        }

        $doctorAvailability->update(['is_available' => !$doctorAvailability->is_available]);

        return back()->with('success', 'Status ketersediaan berhasil diubah.');
    }
}