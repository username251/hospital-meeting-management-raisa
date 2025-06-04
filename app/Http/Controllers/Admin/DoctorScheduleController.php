<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DoctorSchedule;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DoctorScheduleController extends Controller
{
    /**
     * Get day name from day_of_week number (0-6)
     */
    private function getDayName($dayOfWeek)
    {
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

    /**
     * Display a listing of the doctor schedules.
     */
    public function index()
    {
        // Eager load relasi doctor dan user dari doctor untuk menampilkan nama dokter
        $schedules = DoctorSchedule::with('doctor.user')
                                  ->orderBy('day_of_week')
                                  ->orderBy('start_time')
                                  ->paginate(10);

        // Tambahkan day_name ke setiap schedule
        $schedules->each(function ($schedule) {
            $schedule->day_name = $this->getDayName($schedule->day_of_week);
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
            'day_of_week'  => 'required|integer|between:0,6', // Ubah dari min:1|max:7 ke between:0,6
            'start_time'   => 'required|date_format:H:i',
            'end_time'     => 'required|date_format:H:i|after:start_time',
            'is_available' => 'boolean',
            'slot_duration' => 'nullable|integer|min:10', // Tambahkan slot_duration jika diperlukan
        ]);

        // Cek duplikasi jadwal untuk dokter yang sama pada hari dan waktu yang sama
        $existingSchedule = DoctorSchedule::where('doctor_id', $request->doctor_id)
                                          ->where('day_of_week', $request->day_of_week)
                                          ->where(function($query) use ($request) {
                                              // Memeriksa tumpang tindih waktu
                                              $query->where(function($q) use ($request) {
                                                  $q->whereTime('start_time', '<', $request->end_time)
                                                    ->whereTime('end_time', '>', $request->start_time);
                                              });
                                          })
                                          ->first();

        if ($existingSchedule) {
            return redirect()->back()->withInput()->withErrors(['time_overlap' => 'Jadwal dokter ini sudah ada atau tumpang tindih pada hari dan waktu yang ditentukan.']);
        }

        // Format waktu dengan Carbon untuk konsistensi
        $scheduleData = [
            'doctor_id' => $request->doctor_id,
            'day_of_week' => $request->day_of_week,
            'start_time' => Carbon::parse($request->start_time)->format('H:i:s'),
            'end_time' => Carbon::parse($request->end_time)->format('H:i:s'),
            'is_available' => $request->has('is_available') ? (bool)$request->is_available : true,
        ];

        // Tambahkan slot_duration jika ada
        if ($request->filled('slot_duration')) {
            $scheduleData['slot_duration'] = $request->slot_duration;
        }

        DoctorSchedule::create($scheduleData);

        return redirect()->route('doctor_schedules.index')->with('success', 'Jadwal dokter berhasil ditambahkan!');
    }

    /**
     * Display the specified doctor schedule.
     */
    public function show(DoctorSchedule $doctorSchedule)
    {
        $doctorSchedule->load('doctor.user');
        $doctorSchedule->day_name = $this->getDayName($doctorSchedule->day_of_week);
        
        return view('admin.doctor_schedules.show', compact('doctorSchedule'));
    }

    /**
     * Show the form for editing the specified doctor schedule.
     */
    public function edit(DoctorSchedule $doctorSchedule)
    {
        $doctors = Doctor::with('user', 'specialty')->get();
        
        // Days of week mapping (0-6 untuk konsistensi dengan doctor)
        $daysOfWeek = [
            0 => 'Minggu', 1 => 'Senin', 2 => 'Selasa',
            3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu'
        ];

        return view('admin.doctor_schedules.edit', compact('doctorSchedule', 'doctors', 'daysOfWeek'));
    }

    /**
     * Update the specified doctor schedule in storage.
     */
    public function update(Request $request, DoctorSchedule $doctorSchedule)
    {
        $request->validate([
            'doctor_id'    => 'required|exists:doctors,id',
            'day_of_week'  => 'required|integer|between:0,6', // Ubah dari min:1|max:7 ke between:0,6
            'start_time'   => 'required|date_format:H:i',
            'end_time'     => 'required|date_format:H:i|after:start_time',
            'is_available' => 'boolean',
            'slot_duration' => 'nullable|integer|min:10', // Tambahkan slot_duration jika diperlukan
        ]);

        // Cek duplikasi jadwal (kecuali jadwal yang sedang diedit itu sendiri)
        $existingSchedule = DoctorSchedule::where('doctor_id', $request->doctor_id)
                                          ->where('day_of_week', $request->day_of_week)
                                          ->where('id', '!=', $doctorSchedule->id) // Abaikan jadwal yang sedang diedit
                                          ->where(function($query) use ($request) {
                                              $query->where(function($q) use ($request) {
                                                  $q->whereTime('start_time', '<', $request->end_time)
                                                    ->whereTime('end_time', '>', $request->start_time);
                                              });
                                          })
                                          ->first();

        if ($existingSchedule) {
            return redirect()->back()->withInput()->withErrors(['time_overlap' => 'Jadwal dokter ini sudah ada atau tumpang tindih pada hari dan waktu yang ditentukan.']);
        }

        // Format data update dengan Carbon untuk konsistensi
        $updateData = [
            'doctor_id' => $request->doctor_id,
            'day_of_week' => $request->day_of_week,
            'start_time' => Carbon::parse($request->start_time)->format('H:i:s'),
            'end_time' => Carbon::parse($request->end_time)->format('H:i:s'),
            'is_available' => $request->has('is_available') ? (bool)$request->is_available : true,
        ];

        // Update slot_duration jika ada
        if ($request->filled('slot_duration')) {
            $updateData['slot_duration'] = $request->slot_duration;
        }

        $doctorSchedule->update($updateData);

        return redirect()->route('doctor_schedules.index')->with('success', 'Jadwal dokter berhasil diperbarui!');
    }

    /**
     * Remove the specified doctor schedule from storage.
     */
    public function destroy(DoctorSchedule $doctorSchedule)
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
    public function toggleAvailability(DoctorSchedule $doctorSchedule)
    {
        $doctorSchedule->update(['is_available' => !$doctorSchedule->is_available]);

        $status = $doctorSchedule->is_available ? 'aktif' : 'nonaktif';
        return redirect()->back()->with('success', "Status jadwal dokter berhasil diubah menjadi {$status}.");
    }

    /**
     * Get doctor schedules for calendar view (opsional untuk admin dashboard)
     */
    public function calendar()
    {
        $schedules = DoctorSchedule::with('doctor.user')
                                  ->where('is_available', true)
                                  ->orderBy('day_of_week')
                                  ->orderBy('start_time')
                                  ->get();

        // Transform data untuk FullCalendar
        $events = $schedules->map(function ($schedule) {
            return [
                'id' => $schedule->id,
                'title' => $schedule->doctor->user->name . ' (' . 
                          Carbon::parse($schedule->start_time)->format('H:i') . ' - ' . 
                          Carbon::parse($schedule->end_time)->format('H:i') . ')',
                'startTime' => Carbon::parse($schedule->start_time)->format('H:i:s'),
                'endTime' => Carbon::parse($schedule->end_time)->format('H:i:s'),
                'daysOfWeek' => [(int)$schedule->day_of_week],
                'color' => $schedule->is_available ? '#28a745' : '#dc3545',
                'extendedProps' => [
                    'doctor_name' => $schedule->doctor->user->name,
                    'slot_duration' => $schedule->slot_duration ?? 30,
                    'status' => $schedule->is_available ? 'Aktif' : 'Nonaktif',
                    'edit_url' => route('doctor_schedules.edit', $schedule->id),
                ]
            ];
        });

        return view('admin.doctor_schedules.calendar', compact('events', 'schedules'));
    }
}