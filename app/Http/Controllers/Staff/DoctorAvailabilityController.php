<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DoctorAvailability;
use App\Models\Doctor;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class DoctorAvailabilityController extends Controller
{
    private function getDayStringMappingForStorage()
    {
        return [
            0 => 'Sunday', 1 => 'Monday', 2 => 'Tuesday',
            3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday'
        ];
    }

    private function getDayIntegerForCalendar($dayNameString)
    {
        $mapping = array_flip($this->getDayStringMappingForStorage());
        if (is_string($dayNameString) && isset($mapping[$dayNameString])) {
            return $mapping[$dayNameString];
        }
        Log::warning("Staff\DoctorAvailabilityController: Gagal konversi hari '{$dayNameString}' ke integer untuk kalender.");
        return null;
    }

   private function getDayNameForDisplay($dayOfWeekValue)
{
    Log::info("getDayNameForDisplay - Input value: ", ['value' => $dayOfWeekValue, 'type' => gettype($dayOfWeekValue)]);

    $dayNamesIntegerKey = [0 => 'Minggu', 1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu'];
    $dayNamesStringKey = ['Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'];

    if (is_string($dayOfWeekValue) && array_key_exists($dayOfWeekValue, $dayNamesStringKey)) {
        Log::info("getDayNameForDisplay - Matched string key: " . $dayOfWeekValue . " -> " . $dayNamesStringKey[$dayOfWeekValue]);
        return $dayNamesStringKey[$dayOfWeekValue];
    } elseif (is_numeric($dayOfWeekValue) && array_key_exists((int)$dayOfWeekValue, $dayNamesIntegerKey)) {
        Log::info("getDayNameForDisplay - Matched integer key: " . (int)$dayOfWeekValue . " -> " . $dayNamesIntegerKey[(int)$dayOfWeekValue]);
        return $dayNamesIntegerKey[(int)$dayOfWeekValue];
    }

    Log::warning("getDayNameForDisplay - No match found for value: '{$dayOfWeekValue}'. Returning 'Tidak Diketahui'.");
    return 'Tidak Diketahui';
}

    public function index(Request $request)
    {
        $query = DoctorAvailability::with('doctor.user');

        if ($request->has('doctor_id') && $request->doctor_id != '') {
            $query->where('doctor_id', $request->doctor_id);
        }

        $availabilities = $query->orderByRaw("FIELD(day_of_week, 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday')")
                                 ->orderBy('doctor_id')
                                 ->orderBy('start_time')
                                 ->get();

        $availabilities->each(function ($availability) {
            $availability->day_name_display = $this->getDayNameForDisplay($availability->day_of_week);
        });

        $events = $availabilities->map(function ($availability) {
            if (!$availability->is_available) return null;
            $dayInt = $this->getDayIntegerForCalendar($availability->day_of_week);
            if ($dayInt === null) return null;

            return [
                'id' => $availability->id,
                'title' => ($availability->doctor->user->name ?? 'Dokter') . ' Tersedia (' . Carbon::parse($availability->start_time)->format('H:i') . ' - ' . Carbon::parse($availability->end_time)->format('H:i') . ')',
                'startTime' => Carbon::parse($availability->start_time)->format('H:i:s'),
                'endTime' => Carbon::parse($availability->end_time)->format('H:i:s'),
                'daysOfWeek' => [$dayInt],
                'color' => '#28a745',
                'extendedProps' => [
                    'doctor_name' => $availability->doctor->user->name ?? 'N/A',
                    'slot_duration' => $availability->slot_duration,
                    'status_text' => $availability->is_available ? 'Aktif' : 'Nonaktif',
                    'edit_url' => route('staff.doctor_availabilities.edit', $availability->id),
                    'toggle_url' => route('staff.doctor_availabilities.toggle', $availability->id),
                    'is_active' => $availability->is_available
                ]
            ];
        })->filter()->values();

        $doctors = Doctor::with('user')->get();
        $daysOfWeekForForm = [
            0 => 'Minggu', 1 => 'Senin', 2 => 'Selasa',
            3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu'
        ];
        // Di dalam Staff\DoctorAvailabilityController.php -> method index()
$selectedDoctorId = $request->input('doctor_id', null); // Ambil dari request
// ...

        return view('staff.doctor_availabilities.index', compact('availabilities', 'doctors', 'daysOfWeekForForm', 'events', 'selectedDoctorId'));
    }

    public function create()
    {
        $doctors = Doctor::with('user')->get();
        $daysOfWeekForForm = [
            0 => 'Minggu', 1 => 'Senin', 2 => 'Selasa',
            3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu'
        ];
        return view('staff.doctor_availabilities.create', compact('doctors', 'daysOfWeekForForm'));
    }

   public function store(Request $request)
{
    // dd('Memulai method store', $request->all()); // Titik 1: Lihat semua input awal

    $validatedData = $request->validate([
        'doctor_id' => 'required|exists:doctors,id',
        'day_of_week' => 'required|in:Sunday,Monday,Tuesday,Wednesday,Thursday,Friday,Saturday',
        'start_time' => 'required|date_format:H:i',
        'end_time' => 'required|date_format:H:i|after:start_time',
        'slot_duration' => 'required|integer|min:5|max:120',
    ]);

    // dd('Setelah validasi', $validatedData); // Titik 2: Lihat data setelah validasi

    $doctorId = $validatedData['doctor_id']; // Diambil dari $validatedData, bukan $request langsung
    $dayStringMapping = $this->getDayStringMappingForStorage();
    $dayOfWeekInteger = (int) $validatedData['day_of_week']; // Diambil dari $validatedData
    $dayOfWeekString = $dayStringMapping[$dayOfWeekInteger] ?? null;

    // dd('Setelah mapping hari', [
    //     'doctorId' => $doctorId,
    //     'dayOfWeekInteger' => $dayOfWeekInteger,
    //     'dayOfWeekString' => $dayOfWeekString
    // ]); // Titik 3: Lihat hasil konversi hari

    if (!$dayOfWeekString) {
        return back()->withInput()->with('error', 'Hari yang dipilih tidak valid.');
    }
    
    // Pengecekan konflik
    $conflict = DoctorAvailability::where('doctor_id', $doctorId)
        ->where('day_of_week', $dayOfWeekString)
        ->where(function ($query) use ($request) { // $request masih bisa digunakan di sini untuk start_time dan end_time asli
            $query->whereTime('start_time', '<', $request->end_time) // Gunakan $request->end_time
                  ->whereTime('end_time', '>', $request->start_time); // Gunakan $request->start_time
        })
        ->exists();

    // dd('Setelah cek konflik', ['conflict' => $conflict]); // Titik 4: Lihat hasil pengecekan konflik

    if ($conflict) {
        return back()->withInput()->with('error', 'Ada konflik jadwal. Dokter sudah memiliki jadwal pada rentang waktu tersebut di hari yang sama.');
    }

    $dataToCreate = [
        'doctor_id' => $doctorId,
        'day_of_week' => $dayOfWeekString,
        'start_time' => Carbon::parse($validatedData['start_time'])->format('H:i:s'), // Ambil dari $validatedData
        'end_time' => Carbon::parse($validatedData['end_time'])->format('H:i:s'),     // Ambil dari $validatedData
        'slot_duration' => $validatedData['slot_duration'],
        'is_available' => true,
    ];

    // dd('Data yang akan dibuat', $dataToCreate); // Titik 5: Lihat data final sebelum create

    DoctorAvailability::create($dataToCreate);

    return redirect()->route('staff.doctor_availabilities.index')->with('success', 'Jadwal ketersediaan berhasil ditambahkan.');
}

    public function edit(DoctorAvailability $doctorAvailability)
    {
        $doctors = Doctor::with('user')->get();
        $daysOfWeekForForm = [
            0 => 'Minggu', 1 => 'Senin', 2 => 'Selasa',
            3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu'
        ];
        
        $dayStringMapping = $this->getDayStringMappingForStorage();
        $selectedDayInteger = array_search($doctorAvailability->day_of_week, $dayStringMapping);
        if ($selectedDayInteger === false && is_numeric($doctorAvailability->day_of_week)) {
             $selectedDayInteger = isset($dayStringMapping[(int)$doctorAvailability->day_of_week]) ? (int)$doctorAvailability->day_of_week : null;
        }

        $selectedDoctor = Doctor::with('user')->find($doctorAvailability->doctor_id);

        return view('staff.doctor_availabilities.edit', compact('doctorAvailability', 'doctors', 'selectedDoctor', 'daysOfWeekForForm', 'selectedDayInteger'));
    }

    public function update(Request $request, DoctorAvailability $doctorAvailability)
    {
        $request->validate([
            'doctor_id' => 'sometimes|required|exists:doctors,id', // Staff mungkin bisa mengubah dokter
            'day_of_week' => 'required|in:Sunday,Monday,Tuesday,Wednesday,Thursday,Friday,Saturday',
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
        
        $doctorIdToUpdate = $request->filled('doctor_id') ? $request->doctor_id : $doctorAvailability->doctor_id;

        $conflict = DoctorAvailability::where('doctor_id', $doctorIdToUpdate)
            ->where('day_of_week', $dayOfWeekString)
            ->where('id', '!=', $doctorAvailability->id)
            ->where(function ($query) use ($request) {
                $query->whereTime('start_time', '<', $request->end_time)
                      ->whereTime('end_time', '>', $request->start_time);
            })
            ->exists();

        if ($conflict) {
            return back()->withInput()->with('error', 'Ada konflik jadwal dengan jadwal lain pada rentang waktu tersebut di hari yang sama.');
        }

        $dataToUpdate = [
            'day_of_week' => $dayOfWeekString,
            'start_time' => Carbon::parse($request->start_time)->format('H:i:s'),
            'end_time' => Carbon::parse($request->end_time)->format('H:i:s'),
            'slot_duration' => $request->slot_duration,
        ];

        if ($request->filled('doctor_id')) {
            $dataToUpdate['doctor_id'] = $request->doctor_id;
        }

        $doctorAvailability->update($dataToUpdate);

        return redirect()->route('staff.doctor_availabilities.index')->with('success', 'Jadwal ketersediaan berhasil diperbarui.');
    }

    public function destroy(DoctorAvailability $doctorAvailability)
    {
        $doctorAvailability->delete();
        return redirect()->route('staff.doctor_availabilities.index')->with('success', 'Jadwal ketersediaan berhasil dihapus.');
    }
    
    public function toggleAvailability(DoctorAvailability $doctorAvailability)
    {
        $doctorAvailability->update(['is_available' => !$doctorAvailability->is_available]);
        $status = $doctorAvailability->is_available ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Status ketersediaan berhasil {$status}.");
    }
}