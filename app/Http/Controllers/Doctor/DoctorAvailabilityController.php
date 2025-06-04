<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\DoctorAvailability;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log; // Pastikan Log di-import jika digunakan

class DoctorAvailabilityController extends Controller
{
    /**
     * Mapping integer hari (dari form) ke nama hari string bahasa Inggris (untuk disimpan).
     * 0 = Sunday, 1 = Monday, ..., 6 = Saturday (sesuai Carbon->dayOfWeek)
     */
    private function getDayStringMappingForStorage()
    {
        return [
            0 => 'Sunday', 1 => 'Monday', 2 => 'Tuesday',
            3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday'
        ];
    }

    /**
     * Mapping nama hari string bahasa Inggris (dari DB) ke integer (untuk FullCalendar).
     */
    private function getDayIntegerForCalendar($dayNameString)
    {
        $mapping = array_flip($this->getDayStringMappingForStorage());
        return $mapping[$dayNameString] ?? null;
    }

    /**
     * Konversi nilai hari (baik integer 0-6 atau string English 'Monday'-'Sunday')
     * ke nama hari bahasa Indonesia untuk ditampilkan di view.
     */
    private function getDayNameForDisplay($dayOfWeekValue)
    {
        $dayNamesIntegerKey = [
            0 => 'Minggu', 1 => 'Senin', 2 => 'Selasa',
            3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu',
        ];
        $dayNamesStringKey = [ // Ini untuk jika data di DB sudah string
            'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
        ];

        if (is_numeric($dayOfWeekValue) && isset($dayNamesIntegerKey[(int)$dayOfWeekValue])) {
            return $dayNamesIntegerKey[(int)$dayOfWeekValue];
        } elseif (is_string($dayOfWeekValue) && isset($dayNamesStringKey[$dayOfWeekValue])) {
            return $dayNamesStringKey[$dayOfWeekValue];
        }
        return 'Tidak Diketahui';
    }

    public function index()
    {
        $doctor = Auth::user()->doctor;
        if (!$doctor) {
            return redirect()->route('home')->with('error', 'Data dokter tidak ditemukan.'); // Asumsi ada route 'home'
        }

        $availabilities = $doctor->availabilities()
                                 //  ->where('is_available', true) // Anda mungkin ingin melihat semua jadwal di sini, aktif atau non-aktif
                                 ->orderByRaw("FIELD(day_of_week, 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday')")
                                 ->orderBy('start_time')
                                 ->get();

        $availabilities->each(function ($availability) {
            $availability->day_name_display = $this->getDayNameForDisplay($availability->day_of_week);
        });

        $events = $availabilities->map(function ($availability) {
            // Hanya buat event jika is_available true
            if (!$availability->is_available) return null;

            $dayInt = $this->getDayIntegerForCalendar($availability->day_of_week);
            if ($dayInt === null) return null;

            return [
                'id' => $availability->id,
                'title' => 'Tersedia (' . Carbon::parse($availability->start_time)->format('H:i') . ' - ' . Carbon::parse($availability->end_time)->format('H:i') . ')',
                'startTime' => Carbon::parse($availability->start_time)->format('H:i:s'),
                'endTime' => Carbon::parse($availability->end_time)->format('H:i:s'),
                'daysOfWeek' => [$dayInt],
                'color' => '#28a745', // Warna untuk yang tersedia
                'extendedProps' => [
                    'slot_duration' => $availability->slot_duration,
                    'status' => 'Aktif',
                    'edit_url' => route('doctor.availability.edit', $availability->id), // Pastikan nama rute ini ada
                    'toggle_url' => route('doctor.availability.toggle', $availability->id), // Rute untuk toggle
                ]
            ];
        })->filter()->values();

        // Untuk dropdown di form create/edit, value-nya integer (0-6), display-nya nama hari Indonesia
        $daysOfWeekForForm = [
            0 => 'Minggu', 1 => 'Senin', 2 => 'Selasa',
            3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu'
        ];

        return view('doctor.availability.index', compact('availabilities', 'daysOfWeekForForm', 'events'));
    }

    public function create()
    {
        $daysOfWeekForForm = [
            0 => 'Minggu', 1 => 'Senin', 2 => 'Selasa',
            3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu'
        ];
        return view('doctor.availability.create', compact('daysOfWeekForForm'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'day_of_week' => 'required|integer|between:0,6', // Input dari form adalah integer
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'slot_duration' => 'required|integer|min:5|max:120', // Contoh batasan durasi slot
        ]);

        $doctor = Auth::user()->doctor;
        if (!$doctor) {
            return back()->with('error', 'Data dokter tidak ditemukan.');
        }

        $dayStringMapping = $this->getDayStringMappingForStorage();
        $dayOfWeekInteger = (int) $request->day_of_week;
        $dayOfWeekString = $dayStringMapping[$dayOfWeekInteger] ?? null;

        if (!$dayOfWeekString) {
            return back()->withInput()->with('error', 'Hari yang dipilih tidak valid.');
        }

        // Cek konflik jadwal
        $conflict = DoctorAvailability::where('doctor_id', $doctor->id)
            ->where('day_of_week', $dayOfWeekString) // Cek konflik dengan string hari
            ->where(function ($query) use ($request) {
                $query->whereTime('start_time', '<', $request->end_time)
                      ->whereTime('end_time', '>', $request->start_time);
            })
            ->exists();

        if ($conflict) {
            return back()->withInput()->with('error', 'Ada konflik jadwal. Dokter sudah memiliki jadwal pada rentang waktu tersebut di hari yang sama.');
        }

        DoctorAvailability::create([
            'doctor_id' => $doctor->id,
            'day_of_week' => $dayOfWeekString, // Simpan string nama hari (misal: 'Monday')
            'start_time' => Carbon::parse($request->start_time)->format('H:i:s'),
            'end_time' => Carbon::parse($request->end_time)->format('H:i:s'),
            'slot_duration' => $request->slot_duration,
            'is_available' => true, // Default saat membuat baru adalah true
        ]);

        return redirect()->route('doctor.availability.index')->with('success', 'Jadwal ketersediaan berhasil ditambahkan.');
    }

    public function edit(DoctorAvailability $doctorAvailability)
    {
        $doctor = Auth::user()->doctor;
        if (!$doctor || $doctorAvailability->doctor_id !== $doctor->id) {
            abort(403, 'Anda tidak diizinkan mengubah jadwal ini.');
        }

        $daysOfWeekForForm = [
            0 => 'Minggu', 1 => 'Senin', 2 => 'Selasa',
            3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu'
        ];
        
        $dayStringMapping = $this->getDayStringMappingForStorage();
        $selectedDayInteger = array_search($doctorAvailability->day_of_week, $dayStringMapping);
        if ($selectedDayInteger === false && is_numeric($doctorAvailability->day_of_week)) {
            $selectedDayInteger = (int)$doctorAvailability->day_of_week;
        }

        return view('doctor.availability.edit', compact('doctorAvailability', 'daysOfWeekForForm', 'selectedDayInteger'));
    }

    public function update(Request $request, DoctorAvailability $doctorAvailability)
    {
        $doctor = Auth::user()->doctor;
        if (!$doctor || $doctorAvailability->doctor_id !== $doctor->id) {
            abort(403, 'Anda tidak diizinkan mengubah jadwal ini.');
        }

        $request->validate([
            'day_of_week' => 'required|integer|between:0,6',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'slot_duration' => 'required|integer|min:5|max:120',
        ]);

        $dayStringMapping = $this->getDayStringMappingForStorage();
        $dayOfWeekInteger = (int) $request->day_of_week;
        $dayOfWeekString = $dayStringMapping[$dayOfWeekInteger] ?? null;

        if (!$dayOfWeekString) {
            return back()->withInput()->with('error', 'Hari yang dipilih tidak valid.');
        }

        // Cek konflik jadwal (tidak termasuk jadwal yang sedang diedit)
        $conflict = DoctorAvailability::where('doctor_id', $doctor->id)
            ->where('day_of_week', $dayOfWeekString)
            ->where('id', '!=', $doctorAvailability->id) // Abaikan jadwal saat ini
            ->where(function ($query) use ($request) {
                $query->whereTime('start_time', '<', $request->end_time)
                      ->whereTime('end_time', '>', $request->start_time);
            })
            ->exists();

        if ($conflict) {
            return back()->withInput()->with('error', 'Ada konflik jadwal dengan jadwal lain pada rentang waktu tersebut di hari yang sama.');
        }

        $doctorAvailability->update([
            'day_of_week' => $dayOfWeekString, // Simpan string nama hari
            'start_time' => Carbon::parse($request->start_time)->format('H:i:s'),
            'end_time' => Carbon::parse($request->end_time)->format('H:i:s'),
            'slot_duration' => $request->slot_duration,
        ]);

        return redirect()->route('doctor.availability.index')->with('success', 'Jadwal ketersediaan berhasil diperbarui.');
    }

    public function destroy(DoctorAvailability $doctorAvailability)
    {
        $doctor = Auth::user()->doctor;
        if (!$doctor || $doctorAvailability->doctor_id !== $doctor->id) {
            abort(403, 'Anda tidak diizinkan menghapus jadwal ini.');
        }

        $doctorAvailability->delete();
        return redirect()->route('doctor.availability.index')->with('success', 'Jadwal ketersediaan berhasil dihapus.');
    }
    
    public function toggleAvailability(DoctorAvailability $doctorAvailability)
    {
        $doctor = Auth::user()->doctor;
        if (!$doctor || $doctorAvailability->doctor_id !== $doctor->id) {
            abort(403, 'Anda tidak diizinkan mengubah status jadwal ini.');
        }
        $doctorAvailability->update(['is_available' => !$doctorAvailability->is_available]);
        $status = $doctorAvailability->is_available ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Status ketersediaan berhasil {$status}.");
    }
}