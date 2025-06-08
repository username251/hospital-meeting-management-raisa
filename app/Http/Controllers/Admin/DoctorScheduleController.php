<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DoctorAvailability;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DoctorScheduleController extends Controller
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

    /**
     * Display a listing of the doctor schedules.
     */
    public function index()
    {
        // Eager load relasi doctor dan user dari doctor untuk menampilkan nama dokter
        $schedules = DoctorAvailability::with('doctor.user')
                                      ->orderByRaw("FIELD(day_of_week, 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday')")
                                      ->orderBy('start_time')
                                      ->paginate(10);

        // Tambahkan day_name ke setiap schedule
        $schedules->each(function ($schedule) {
            $schedule->day_name = $this->getDayNameForDisplay($schedule->day_of_week);
        });

        return view('admin.doctor_schedules.index', compact('schedules'));
    }

    /**
     * Show the form for creating a new doctor schedule.
     */
    public function create()
    {
        $doctors = Doctor::with('user', 'specialty')->get(); // Ambil semua dokter
        
        // Days of week mapping (0-6 untuk konsistensi dengan doctor)
        $daysOfWeek = [
            0 => 'Minggu', 1 => 'Senin', 2 => 'Selasa',
            3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu'
        ];

        return view('admin.doctor_schedules.create', compact('doctors', 'daysOfWeek'));
    }

    /**
     * Store a newly created doctor schedule in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'doctor_id'    => 'required|exists:doctors,id',
            'day_of_week'  => 'required|integer|between:0,6', // Input dari form adalah integer
            'start_time'   => 'required|date_format:H:i',
            'end_time'     => 'required|date_format:H:i|after:start_time',
            'is_available' => 'boolean',
            'slot_duration' => 'required|integer|min:5|max:120', // Sesuaikan dengan doctor controller
        ]);

        $dayStringMapping = $this->getDayStringMappingForStorage();
        $dayOfWeekInteger = (int) $request->day_of_week;
        $dayOfWeekString = $dayStringMapping[$dayOfWeekInteger] ?? null;

        if (!$dayOfWeekString) {
            return back()->withInput()->with('error', 'Hari yang dipilih tidak valid.');
        }

        // Cek konflik jadwal
        $existingSchedule = DoctorAvailability::where('doctor_id', $request->doctor_id)
            ->where('day_of_week', $dayOfWeekString) // Cek konflik dengan string hari
            ->where(function ($query) use ($request) {
                $query->whereTime('start_time', '<', $request->end_time)
                      ->whereTime('end_time', '>', $request->start_time);
            })
            ->exists();

        if ($existingSchedule) {
            return redirect()->back()->withInput()->withErrors(['time_overlap' => 'Jadwal dokter ini sudah ada atau tumpang tindih pada hari dan waktu yang ditentukan.']);
        }

        // Format waktu dengan Carbon untuk konsistensi
        $scheduleData = [
            'doctor_id' => $request->doctor_id,
            'day_of_week' => $dayOfWeekString, // Simpan string nama hari (misal: 'Monday')
            'start_time' => Carbon::parse($request->start_time)->format('H:i:s'),
            'end_time' => Carbon::parse($request->end_time)->format('H:i:s'),
            'is_available' => $request->has('is_available') ? (bool)$request->is_available : true,
            'slot_duration' => $request->slot_duration,
        ];

        DoctorAvailability::create($scheduleData);

        return redirect()->route('doctor_schedules.index')->with('success', 'Jadwal dokter berhasil ditambahkan!');
    }

    /**
     * Display the specified doctor schedule.
     */
    public function show(DoctorAvailability $doctorSchedule)
    {
        $doctorSchedule->load('doctor.user');
        $doctorSchedule->day_name = $this->getDayNameForDisplay($doctorSchedule->day_of_week);
        
        return view('admin.doctor_schedules.show', compact('doctorSchedule'));
    }

    /**
     * Show the form for editing the specified doctor schedule.
     */
    public function edit(DoctorAvailability $doctorSchedule)
    {
        $doctors = Doctor::with('user', 'specialty')->get();
        
        // Days of week mapping (0-6 untuk konsistensi dengan doctor)
        $daysOfWeek = [
            0 => 'Minggu', 1 => 'Senin', 2 => 'Selasa',
            3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu'
        ];

        // Tentukan hari yang dipilih saat ini untuk form edit
        $dayStringMapping = $this->getDayStringMappingForStorage();
        $selectedDayInteger = array_search($doctorSchedule->day_of_week, $dayStringMapping);
        if ($selectedDayInteger === false && is_numeric($doctorSchedule->day_of_week)) {
            $selectedDayInteger = (int)$doctorSchedule->day_of_week;
        }

        return view('admin.doctor_schedules.edit', compact('doctorSchedule', 'doctors', 'daysOfWeek', 'selectedDayInteger'));
    }

    /**
     * Update the specified doctor schedule in storage.
     */
    public function update(Request $request, DoctorAvailability $doctorSchedule)
    {
        $request->validate([
            'doctor_id'    => 'required|exists:doctors,id',
            'day_of_week'  => 'required|integer|between:0,6', // Input dari form adalah integer
            'start_time'   => 'required|date_format:H:i',
            'end_time'     => 'required|date_format:H:i|after:start_time',
            'is_available' => 'boolean',
            'slot_duration' => 'required|integer|min:5|max:120', // Sesuaikan dengan doctor controller
        ]);

        $dayStringMapping = $this->getDayStringMappingForStorage();
        $dayOfWeekInteger = (int) $request->day_of_week;
        $dayOfWeekString = $dayStringMapping[$dayOfWeekInteger] ?? null;

        if (!$dayOfWeekString) {
            return back()->withInput()->with('error', 'Hari yang dipilih tidak valid.');
        }

        // Cek duplikasi jadwal (kecuali jadwal yang sedang diedit itu sendiri)
        $existingSchedule = DoctorAvailability::where('doctor_id', $request->doctor_id)
                                          ->where('day_of_week', $dayOfWeekString)
                                          ->where('id', '!=', $doctorSchedule->id) // Abaikan jadwal yang sedang diedit
                                          ->where(function($query) use ($request) {
                                              $query->whereTime('start_time', '<', $request->end_time)
                                                    ->whereTime('end_time', '>', $request->start_time);
                                          })
                                          ->first();

        if ($existingSchedule) {
            return redirect()->back()->withInput()->withErrors(['time_overlap' => 'Jadwal dokter ini sudah ada atau tumpang tindih pada hari dan waktu yang ditentukan.']);
        }

        // Format data update dengan Carbon untuk konsistensi
        $updateData = [
            'doctor_id' => $request->doctor_id,
            'day_of_week' => $dayOfWeekString, // Simpan sebagai string nama hari
            'start_time' => Carbon::parse($request->start_time)->format('H:i:s'),
            'end_time' => Carbon::parse($request->end_time)->format('H:i:s'),
            'is_available' => $request->has('is_available') ? (bool)$request->is_available : true,
            'slot_duration' => $request->slot_duration,
        ];

        $doctorSchedule->update($updateData);

        return redirect()->route('doctor_schedules.index')->with('success', 'Jadwal dokter berhasil diperbarui!');
    }

    /**
     * Remove the specified doctor schedule from storage.
     */
    public function destroy(DoctorAvailability $doctorSchedule)
    {
        try {
            $doctorSchedule->delete();
            return redirect()->route('doctor_schedules.index')->with('success', 'Jadwal dokter berhasil dihapus!');
        } catch (\Exception $e) {
            return redirect()->route('doctor_schedules.index')->with('error', 'Terjadi kesalahan saat menghapus jadwal dokter: ' . $e->getMessage());
        }
    }

    /**
     * Toggle the availability status of a doctor schedule.
     */
    public function toggleAvailability(DoctorAvailability $doctorSchedule)
    {
        $doctorSchedule->update(['is_available' => !$doctorSchedule->is_available]);

        $status = $doctorSchedule->is_available ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()->back()->with('success', "Status jadwal dokter berhasil {$status}.");
    }

    /**
     * Get doctor schedules for calendar view (opsional untuk admin dashboard)
     */
    public function calendar()
    {
        $schedules = DoctorAvailability::with('doctor.user')
                                  ->where('is_available', true)
                                  ->orderByRaw("FIELD(day_of_week, 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday')")
                                  ->orderBy('start_time')
                                  ->get();

        // Transform data untuk FullCalendar
        $events = $schedules->map(function ($schedule) {
            $dayInt = $this->getDayIntegerForCalendar($schedule->day_of_week);
            if ($dayInt === null) return null;

            return [
                'id' => $schedule->id,
                'title' => $schedule->doctor->user->name . ' (' . 
                          Carbon::parse($schedule->start_time)->format('H:i') . ' - ' . 
                          Carbon::parse($schedule->end_time)->format('H:i') . ')',
                'startTime' => Carbon::parse($schedule->start_time)->format('H:i:s'),
                'endTime' => Carbon::parse($schedule->end_time)->format('H:i:s'),
                'daysOfWeek' => [$dayInt],
                'color' => $schedule->is_available ? '#28a745' : '#dc3545',
                'extendedProps' => [
                    'doctor_name' => $schedule->doctor->user->name,
                    'slot_duration' => $schedule->slot_duration,
                    'status' => $schedule->is_available ? 'Aktif' : 'Nonaktif',
                    'edit_url' => route('doctor_schedules.edit', $schedule->id),
                ]
            ];
        })->filter()->values();

        return view('admin.doctor_schedules.calendar', compact('events', 'schedules'));
    }
}