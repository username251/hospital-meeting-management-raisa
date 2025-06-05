<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\   Controller;
use App\Models\Appointment;
use App\Models\Feedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FeedbackController extends Controller
{
    // Menampilkan form untuk membuat feedback
    public function create(Appointment $appointment)
    {
        // Pastikan appointment ini milik pasien yang sedang login dan statusnya sudah 'completed'
        if ($appointment->patient->user_id !== Auth::id() || $appointment->status !== 'completed') {
            abort(403, 'Akses Ditolak');
        }

        // Pastikan belum ada feedback untuk appointment ini
        if ($appointment->feedback) {
            return redirect()->route('appointments.index')->with('error', 'Anda sudah memberikan feedback untuk janji temu ini.');
        }

        return view('patient.feedback.create', compact('appointment'));
    }

    // Menyimpan feedback baru
    public function store(Request $request, Appointment $appointment)
    {
        // Validasi
        if ($appointment->patient->user_id !== Auth::id() || $appointment->status !== 'completed') {
            abort(403, 'Akses Ditolak');
        }

        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        // Simpan feedback
        Feedback::create([
            'appointment_id' => $appointment->id,
            'doctor_id' => $appointment->doctor_id,
            'patient_id' => $appointment->patient_id,
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return redirect()->route('appointments.index')->with('success', 'Terima kasih, feedback Anda telah kami terima.');
    }
}
