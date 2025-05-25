<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DoctorSchedule;
use App\Models\Doctor;
use Illuminate\Http\Request;

class DoctorScheduleController extends Controller
{
    /**
     * Display a listing of the doctor schedules.
     */
    public function index()
    {
        // Eager load relasi doctor dan user dari doctor untuk menampilkan nama dokter
        $schedules = DoctorSchedule::with('doctor.user')->paginate(10);
        return view('admin.doctor_schedules.index', compact('schedules'));
    }

    /**
     * Show the form for creating a new doctor schedule.
     */
    public function create()
    {
        $doctors = Doctor::with('user', 'specialty')->get(); // Ambil semua dokter
        return view('admin.doctor_schedules.create', compact('doctors'));
    }

    /**
     * Store a newly created doctor schedule in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'doctor_id'    => 'required|exists:doctors,id',
            'day_of_week'  => 'required|integer|min:1|max:7', // Validasi day_of_week
            'start_time'   => 'required|date_format:H:i',
            'end_time'     => 'required|date_format:H:i|after:start_time',
            'is_available' => 'boolean', // Validasi is_available
        ]);

        // Cek duplikasi jadwal untuk dokter yang sama pada hari dan waktu yang sama
        $existingSchedule = DoctorSchedule::where('doctor_id', $request->doctor_id)
                                          ->where('day_of_week', $request->day_of_week)
                                          ->where(function($query) use ($request) {
                                              // Memeriksa tumpang tindih waktu
                                              $query->where(function($q) use ($request) {
                                                  $q->where('start_time', '<', $request->end_time)
                                                    ->where('end_time', '>', $request->start_time);
                                              });
                                          })
                                          ->first();

        if ($existingSchedule) {
            return redirect()->back()->withInput()->withErrors(['time_overlap' => 'Jadwal dokter ini sudah ada atau tumpang tindih pada hari dan waktu yang ditentukan.']);
        }

        DoctorSchedule::create($request->all());

        return redirect()->route('admin.doctor_schedules.index')->with('success', 'Jadwal dokter berhasil ditambahkan!');
    }

    /**
     * Display the specified doctor schedule.
     * (Opsional: Jika Anda membutuhkan halaman detail)
     */
    public function show(DoctorSchedule $doctorSchedule)
    {
        $doctorSchedule->load('doctor.user');
        return view('admin.doctor_schedules.show', compact('doctorSchedule'));
    }

    /**
     * Show the form for editing the specified doctor schedule.
     */
    public function edit(DoctorSchedule $doctorSchedule) // Menggunakan Route Model Binding
    {
        $doctors = Doctor::with('user', 'specialty')->get();
        return view('admin.doctor_schedules.edit', compact('doctorSchedule', 'doctors'));
    }

    /**
     * Update the specified doctor schedule in storage.
     */
    public function update(Request $request, DoctorSchedule $doctorSchedule) // Menggunakan Route Model Binding
    {
        $request->validate([
            'doctor_id'    => 'required|exists:doctors,id',
            'day_of_week'  => 'required|integer|min:1|max:7', // Validasi day_of_week
            'start_time'   => 'required|date_format:H:i',
            'end_time'     => 'required|date_format:H:i|after:start_time',
            'is_available' => 'boolean', // Validasi is_available
        ]);

        // Cek duplikasi jadwal (kecuali jadwal yang sedang diedit itu sendiri)
        $existingSchedule = DoctorSchedule::where('doctor_id', $request->doctor_id)
                                          ->where('day_of_week', $request->day_of_week)
                                          ->where('id', '!=', $doctorSchedule->id) // Abaikan jadwal yang sedang diedit
                                          ->where(function($query) use ($request) {
                                              $query->where(function($q) use ($request) {
                                                  $q->where('start_time', '<', $request->end_time)
                                                    ->where('end_time', '>', $request->start_time);
                                              });
                                          })
                                          ->first();

        if ($existingSchedule) {
            return redirect()->back()->withInput()->withErrors(['time_overlap' => 'Jadwal dokter ini sudah ada atau tumpang tindih pada hari dan waktu yang ditentukan.']);
        }

        $doctorSchedule->update($request->all());

        return redirect()->route('admin.doctor_schedules.index')->with('success', 'Jadwal dokter berhasil diperbarui!');
    }

    /**
     * Remove the specified doctor schedule from storage.
     */
    public function destroy(DoctorSchedule $doctorSchedule) // Menggunakan Route Model Binding
    {
        try {
            $doctorSchedule->delete();
            return redirect()->route('admin.doctor_schedules.index')->with('success', 'Jadwal dokter berhasil dihapus!');
        } catch (\Exception $e) {
            return redirect()->route('admin.doctor_schedules.index')->with('error', 'Terjadi kesalahan saat menghapus jadwal dokter: ' . $e->getMessage());
        }
    }
}