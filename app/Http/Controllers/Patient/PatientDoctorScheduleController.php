<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Doctor;
use App\Models\DoctorAvailability;
use Carbon\Carbon;

class PatientDoctorScheduleController extends Controller
{
    /**
     * Konversi nilai hari ke nama hari bahasa Indonesia untuk ditampilkan.
     */
    private function getDayNameForDisplay($dayOfWeekValue)
    {
        $dayNamesIntegerKey = [
            0 => 'Minggu', 1 => 'Senin', 2 => 'Selasa',
            3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu',
        ];
        $dayNamesStringKey = [
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
     * Mapping nama hari string bahasa Inggris ke integer untuk urutan.
     */
    private function getDayOrder($dayName)
    {
        $order = [
            'Sunday' => 0, 'Monday' => 1, 'Tuesday' => 2,
            'Wednesday' => 3, 'Thursday' => 4, 'Friday' => 5, 'Saturday' => 6
        ];
        return $order[$dayName] ?? 7;
    }

    /**
     * Tampilkan daftar semua dokter dengan jadwal mereka
     */
    public function index(Request $request)
    {
        $query = Doctor::with(['user', 'specialty', 'availabilities' => function($q) {
            $q->where('is_available', true)
              ->orderByRaw("FIELD(day_of_week, 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday')")
              ->orderBy('start_time');
        }]);

        // Filter berdasarkan pencarian nama dokter atau bio
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%');
            })->orWhere('bio', 'like', '%' . $search . '%');
        }

        // Filter berdasarkan spesialisasi
        if ($request->filled('specialty_id')) {
            $query->where('specialty_id', $request->specialty_id);
        }

        $doctors = $query->paginate(10);

        // Ambil daftar spesialisasi untuk filter dari tabel specialties
        $specialties = \App\Models\Specialty::orderBy('name')->get();

        // Format data jadwal untuk setiap dokter
        $doctors->getCollection()->transform(function ($doctor) {
            // Group jadwal berdasarkan hari
            $scheduleByDay = $doctor->availabilities->groupBy('day_of_week');
            
            $doctor->formatted_schedule = $scheduleByDay->map(function ($schedules, $day) {
                return [
                    'day' => $this->getDayNameForDisplay($day),
                    'day_order' => $this->getDayOrder($day),
                    'times' => $schedules->map(function ($schedule) {
                        return [
                            'start_time' => Carbon::parse($schedule->start_time)->format('H:i'),
                            'end_time' => Carbon::parse($schedule->end_time)->format('H:i'),
                            'slot_duration' => $schedule->slot_duration,
                            'time_range' => Carbon::parse($schedule->start_time)->format('H:i') . ' - ' . Carbon::parse($schedule->end_time)->format('H:i')
                        ];
                    })
                ];
            })->sortBy('day_order');

            return $doctor;
        });

        return view('patient.doctor-schedule.index', compact('doctors', 'specialties'));
    }

    /**
     * Tampilkan detail jadwal dokter tertentu
     */
    public function show(Doctor $doctor)
    {
        $doctor->load(['user', 'specialty', 'availabilities' => function($q) {
            $q->where('is_available', true)
              ->orderByRaw("FIELD(day_of_week, 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday')")
              ->orderBy('start_time');
        }]);

        // Format jadwal untuk calendar view
        $events = $doctor->availabilities->map(function ($availability) {
            $dayOrder = $this->getDayOrder($availability->day_of_week);
            
            return [
                'id' => $availability->id,
                'title' => 'Tersedia',
                'startTime' => Carbon::parse($availability->start_time)->format('H:i:s'),
                'endTime' => Carbon::parse($availability->end_time)->format('H:i:s'),
                'daysOfWeek' => [$dayOrder],
                'color' => '#28a745',
                'extendedProps' => [
                    'slot_duration' => $availability->slot_duration,
                    'time_range' => Carbon::parse($availability->start_time)->format('H:i') . ' - ' . Carbon::parse($availability->end_time)->format('H:i'),
                    'day_name' => $this->getDayNameForDisplay($availability->day_of_week)
                ]
            ];
        });

        // Group jadwal berdasarkan hari untuk tampilan list
        $scheduleByDay = $doctor->availabilities->groupBy('day_of_week');
        $formattedSchedule = $scheduleByDay->map(function ($schedules, $day) {
            return [
                'day' => $this->getDayNameForDisplay($day),
                'day_order' => $this->getDayOrder($day),
                'schedules' => $schedules->map(function ($schedule) {
                    return [
                        'start_time' => Carbon::parse($schedule->start_time)->format('H:i'),
                        'end_time' => Carbon::parse($schedule->end_time)->format('H:i'),
                        'slot_duration' => $schedule->slot_duration,
                        'time_range' => Carbon::parse($schedule->start_time)->format('H:i') . ' - ' . Carbon::parse($schedule->end_time)->format('H:i')
                    ];
                })
            ];
        })->sortBy('day_order');

        return view('patient.doctor-schedule.show', compact('doctor', 'events', 'formattedSchedule'));
    }
}