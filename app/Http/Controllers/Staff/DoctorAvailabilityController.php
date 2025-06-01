<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DoctorAvailability;
use App\Models\Doctor; // Pastikan model Doctor di-import
use Carbon\Carbon;
use Illuminate\Validation\Rule;

class DoctorAvailabilityController extends Controller
{
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

    public function index(Request $request)
    {
        $selectedDoctorId = $request->input('doctor_id');
        $doctors = Doctor::with('user')->get(); // Ambil semua dokter

        $query = DoctorAvailability::with('doctor.user');

        if ($selectedDoctorId) {
            $query->where('doctor_id', $selectedDoctorId);
        }

        $availabilities = $query->where('is_available', true) // Hanya tampilkan yang aktif di kalender
                                ->orderBy('doctor_id')
                                ->orderBy('day_of_week')
                                ->orderBy('start_time')
                                ->get();

        // Add 'day_name' to each availability object for display
        $availabilities->each(function ($availability) {
            $availability->day_name = $this->getDayName($availability->day_of_week);
        });

        // Transform data for FullCalendar
        $events = $availabilities->map(function ($availability) {
            return [
                'id' => $availability->id,
                'title' => 'Tersedia (' . Carbon::parse($availability->start_time)->format('H:i') . ' - ' . Carbon::parse($availability->end_time)->format('H:i') . ')',
                'startTime' => Carbon::parse($availability->start_time)->format('H:i:s'),
                'endTime' => Carbon::parse($availability->end_time)->format('H:i:s'),
                'daysOfWeek' => [(int)$availability->day_of_week],
                'color' => $availability->is_available ? '#28a745' : '#dc3545',
                'extendedProps' => [
                    'doctor_name' => $availability->doctor->user->name ?? 'N/A',
                    'slot_duration' => $availability->slot_duration,
                    'status' => $availability->is_available ? 'Aktif' : 'Nonaktif',
                    'edit_url' => route('staff.doctor_availabilities.edit', $availability->id),
                ]
            ];
        });

        // Mapping for create/edit form dropdowns
        $daysOfWeek = [
            0 => 'Sunday', 1 => 'Monday', 2 => 'Tuesday',
            3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday'
        ];

        return view('staff.doctor_availabilities.index', compact('availabilities', 'doctors', 'selectedDoctorId', 'daysOfWeek', 'events'));
    }

    public function create()
    {
        $doctors = Doctor::with('user')->get();
        $daysOfWeek = [
            0 => 'Sunday', 1 => 'Monday', 2 => 'Tuesday',
            3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday'
        ];
        return view('staff.doctor_availabilities.create', compact('doctors', 'daysOfWeek'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'day_of_week' => 'required|integer|between:0,6',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'slot_duration' => 'required|integer|min:10', // Added
        ]);

        // Check for time conflicts
        $conflict = DoctorAvailability::where('doctor_id', $request->doctor_id)
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
            'doctor_id' => $request->doctor_id,
            'day_of_week' => $request->day_of_week,
            'start_time' => Carbon::parse($request->start_time)->format('H:i:s'),
            'end_time' => Carbon::parse($request->end_time)->format('H:i:s'),
            'slot_duration' => $request->slot_duration, // Added
            'is_available' => true, // Default to true when created
        ]);

        return redirect()->route('staff.doctor_availabilities.index')->with('success', 'Ketersediaan dokter berhasil ditambahkan.');
    }

    public function edit(DoctorAvailability $doctorAvailability)
    {
        $doctors = Doctor::with('user')->get();
        $daysOfWeek = [
            0 => 'Sunday', 1 => 'Monday', 2 => 'Tuesday',
            3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday'
        ];
        return view('staff.doctor_availabilities.edit', compact('doctorAvailability', 'doctors', 'daysOfWeek'));
    }

    public function update(Request $request, DoctorAvailability $doctorAvailability)
    {
        $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'day_of_week' => 'required|integer|between:0,6',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'slot_duration' => 'required|integer|min:10', // Added
        ]);

        // Check for time conflicts, excluding the current availability being updated
        $conflict = DoctorAvailability::where('doctor_id', $request->doctor_id)
            ->where('day_of_week', $request->day_of_week)
            ->where('id', '!=', $doctorAvailability->id) // Exclude current record
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
            'doctor_id' => $request->doctor_id,
            'day_of_week' => $request->day_of_week,
            'start_time' => Carbon::parse($request->start_time)->format('H:i:s'),
            'end_time' => Carbon::parse($request->end_time)->format('H:i:s'),
            'slot_duration' => $request->slot_duration, // Added
        ]);

        return redirect()->route('staff.doctor_availabilities.index')->with('success', 'Ketersediaan dokter berhasil diperbarui.');
    }

    public function destroy(DoctorAvailability $doctorAvailability)
    {
        $doctorAvailability->delete();
        return redirect()->route('staff.doctor_availabilities.index')->with('success', 'Ketersediaan dokter berhasil dihapus.');
    }

    /**
     * Toggle the availability status of a doctor availability.
     */
    public function toggleAvailability(DoctorAvailability $doctorAvailability)
    {
        $doctorAvailability->update(['is_available' => !$doctorAvailability->is_available]);
        return back()->with('success', 'Status ketersediaan berhasil diubah.');
    }
}