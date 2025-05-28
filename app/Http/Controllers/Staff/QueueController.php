<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Patient;
use Illuminate\Http\Request;
use Carbon\Carbon;

class QueueController extends Controller
{
    /**
     * Menampilkan daftar pasien dalam antrean untuk hari ini.
     */
    public function index()
    {
        $today = Carbon::today()->toDateString();

        // Ambil janji temu untuk hari ini yang relevan dengan antrean
        // Contoh status: 'confirmed', 'pending', 'check-in', 'waiting', 'in-consultation'
        $queueAppointments = Appointment::whereDate('appointment_date', $today)
                                        ->whereIn('status', ['confirmed', 'pending', 'check-in', 'waiting', 'in-consultation'])
                                        ->with(['patient.user', 'doctor.user', 'specialty'])
                                        ->orderBy('start_time', 'asc') // Urutkan berdasarkan waktu janji temu
                                        ->get();

        // Anda bisa menambahkan logika untuk menentukan "pasien yang akan dipanggil"
        // Misalnya, pasien pertama dengan status 'check-in' atau 'waiting'
        $nextPatient = $queueAppointments->whereIn('status', ['check-in', 'waiting'])->sortBy('start_time')->first();

        return view('staff.queue.index', compact('queueAppointments', 'nextPatient'));
    }

    /**
     * Memperbarui status janji temu pasien.
     */
    public function updateStatus(Request $request, Appointment $appointment)
    {
        $request->validate([
            'status' => 'required|in:check-in,waiting,in-consultation,completed,cancelled,no-show',
            // Anda bisa menambahkan validasi lebih lanjut jika status transisi tertentu tidak diizinkan
        ]);

        $appointment->update(['status' => $request->status]);

        return redirect()->back()->with('success', 'Status pasien berhasil diperbarui!');
    }

    /**
     * Menandai pasien sebagai "Sedang Konsultasi" atau "Dipanggil"
     * Ini bisa jadi fungsi shortcut untuk staf.
     */
    public function callPatient(Appointment $appointment)
    {
        // Logika untuk menandai pasien sebagai sedang dipanggil/sedang konsultasi
        // Misalnya, mengubah status menjadi 'in-consultation'
        // Atau Anda bisa memiliki field 'is_called' terpisah di tabel appointment/queue
        if ($appointment->status === 'check-in' || $appointment->status === 'waiting' || $appointment->status === 'confirmed' || $appointment->status === 'pending') {
            $appointment->update(['status' => 'in-consultation']);
            return redirect()->back()->with('success', 'Pasien ' . ($appointment->patient->user->name ?? 'N/A') . ' berhasil dipanggil dan sedang konsultasi.');
        }

        return redirect()->back()->with('error', 'Tidak dapat memanggil pasien. Status tidak valid atau sudah dipanggil.');
    }

    // Anda bisa menambahkan fitur pencarian universal di sini jika ingin mencari di halaman antrean
    public function search(Request $request)
    {
        $query = $request->input('query');
        $today = Carbon::today()->toDateString();

        $searchResults = Appointment::whereDate('appointment_date', $today)
                                    ->where(function($q) use ($query) {
                                        $q->whereHas('patient.user', function($userQuery) use ($query) {
                                            $userQuery->where('name', 'like', '%' . $query . '%');
                                        })
                                        ->orWhereHas('doctor.user', function($userQuery) use ($query) {
                                            $userQuery->where('name', 'like', '%' . $query . '%');
                                        })
                                        ->orWhere('id', 'like', '%' . $query . '%'); // Cari berdasarkan ID Janji Temu
                                    })
                                    ->with(['patient.user', 'doctor.user', 'specialty'])
                                    ->orderBy('start_time', 'asc')
                                    ->get();

        return view('staff.queue.search_results', compact('searchResults', 'query')); // Anda perlu membuat view ini
    }
}