<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Appointment;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display the doctor's dashboard with comprehensive statistics and today's appointments.
     */
    public function index()
    {
        $doctor = Auth::user()->doctor;

        if (!$doctor) {
            return redirect()->route('dashboard')->with('error', 'Anda tidak memiliki profil dokter.');
        }

        // Today's appointments with patient relationship
        $todayAppointments = Appointment::with(['patient.user'])
            ->where('doctor_id', $doctor->id)
            ->whereDate('appointment_date', Carbon::today())
            ->orderBy('start_time')
            ->get();

        // Jumlah janji temu minggu ini
        $weeklyAppointmentsCount = Appointment::where('doctor_id', $doctor->id)
            ->whereBetween('appointment_date', [
                Carbon::today()->startOfWeek(), // Senin
                Carbon::today()->endOfWeek()    // Minggu
            ])
            ->count();

        // Jumlah janji temu bulan ini
        $monthlyAppointmentsCount = Appointment::where('doctor_id', $doctor->id)
            ->whereMonth('appointment_date', Carbon::today()->month)
            ->whereYear('appointment_date', Carbon::today()->year)
            ->count();

        // Additional statistics for enhanced dashboard
        $statistics = $this->getDashboardStatistics($doctor->id);

        return view('doctor.dashboard', compact('doctor', 'todayAppointments', 'statistics', 'weeklyAppointmentsCount', 'monthlyAppointmentsCount'));
    }

    /**
     * Display today's appointments on a dedicated page.
     */
    public function todayAppointments()
    {
        $doctor = Auth::user()->doctor;

        if (!$doctor) {
            return redirect()->route('doctor.dashboard')->with('error', 'Anda tidak memiliki profil dokter.');
        }

        $todayAppointments = Appointment::with(['patient.user'])
            ->where('doctor_id', $doctor->id)
            ->whereDate('appointment_date', Carbon::today())
            ->orderBy('start_time')
            ->get();

        return view('doctor.appointments.today', compact('todayAppointments'));
    }

    /**
     * Update the status of an appointment with additional validation.
     */
    public function updateAppointmentStatus(Request $request, Appointment $appointment)
    {
        // Ensure the logged-in doctor owns this appointment
        if ($appointment->doctor_id !== Auth::user()->doctor->id) {
            return back()->with('error', 'Anda tidak memiliki akses untuk mengubah janji temu ini.');
        }

        $request->validate([
            'status' => ['required', 'string', 'in:confirmed,completed,cancelled,missed,in_consultation'],
        ]);

        // Additional business logic for status transitions
        $oldStatus = $appointment->status;
        $newStatus = $request->status;

        // Validate status transitions
        if (!$this->isValidStatusTransition($oldStatus, $newStatus)) {
            return back()->with('error', 'Transisi status tidak valid.');
        }

        $appointment->update([
            'status' => $newStatus,
            'updated_at' => Carbon::now(),
        ]);

        // Log the status change if needed
        $this->logStatusChange($appointment, $oldStatus, $newStatus);

        $statusMessages = [
            'confirmed' => 'Janji temu dikonfirmasi.',
            'completed' => 'Konsultasi selesai.',
            'cancelled' => 'Janji temu dibatalkan.',
            'missed' => 'Janji temu terlewat.',
            'in_consultation' => 'Konsultasi dimulai.',
        ];

        return back()->with('success', $statusMessages[$newStatus] ?? 'Status janji temu berhasil diperbarui.');
    }

    /**
     * Get comprehensive dashboard statistics.
     */
    private function getDashboardStatistics($doctorId)
    {
        $today = Carbon::today();
        $thisWeek = Carbon::now()->startOfWeek();
        $thisMonth = Carbon::now()->startOfMonth();

        return [
            'today' => [
                'total' => Appointment::where('doctor_id', $doctorId)
                    ->whereDate('appointment_date', $today)
                    ->count(),
                'confirmed' => Appointment::where('doctor_id', $doctorId)
                    ->whereDate('appointment_date', $today)
                    ->where('status', 'confirmed')
                    ->count(),
                'completed' => Appointment::where('doctor_id', $doctorId)
                    ->whereDate('appointment_date', $today)
                    ->where('status', 'completed')
                    ->count(),
                'in_consultation' => Appointment::where('doctor_id', $doctorId)
                    ->whereDate('appointment_date', $today)
                    ->where('status', 'in_consultation')
                    ->count(),
                'cancelled' => Appointment::where('doctor_id', $doctorId)
                    ->whereDate('appointment_date', $today)
                    ->where('status', 'cancelled')
                    ->count(),
            ],
            'week' => [
                'total' => Appointment::where('doctor_id', $doctorId)
                    ->whereBetween('appointment_date', [$thisWeek, $thisWeek->copy()->endOfWeek()])
                    ->count(),
                'completed' => Appointment::where('doctor_id', $doctorId)
                    ->whereBetween('appointment_date', [$thisWeek, $thisWeek->copy()->endOfWeek()])
                    ->where('status', 'completed')
                    ->count(),
            ],
            'month' => [
                'total' => Appointment::where('doctor_id', $doctorId)
                    ->whereBetween('appointment_date', [$thisMonth, $thisMonth->copy()->endOfMonth()])
                    ->count(),
                'completed' => Appointment::where('doctor_id', $doctorId)
                    ->whereBetween('appointment_date', [$thisMonth, $thisMonth->copy()->endOfMonth()])
                    ->where('status', 'completed')
                    ->count(),
            ],
        ];
    }

    /**
     * Validate if status transition is allowed.
     */
    private function isValidStatusTransition($oldStatus, $newStatus)
    {
        $allowedTransitions = [
            'confirmed' => ['in_consultation', 'cancelled', 'missed'],
            'in_consultation' => ['completed', 'cancelled'],
            'completed' => [], // Cannot change from completed
            'cancelled' => ['confirmed'], // Can be re-confirmed if needed
            'missed' => ['confirmed'], // Can be re-confirmed if patient shows up
        ];

        return in_array($newStatus, $allowedTransitions[$oldStatus] ?? []);
    }

    /**
     * Log status changes for audit trail.
     */
    private function logStatusChange($appointment, $oldStatus, $newStatus)
    {
        // You can implement logging here if needed
        // For example, create an AppointmentLog model to track changes
        
        \Log::info('Appointment status changed', [
            'appointment_id' => $appointment->id,
            'doctor_id' => $appointment->doctor_id,
            'patient_id' => $appointment->patient_id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'changed_by' => Auth::id(),
            'changed_at' => Carbon::now(),
        ]);
    }

    /**
     * Get upcoming appointments for the next few days (AJAX endpoint).
     */
    public function getUpcomingAppointments(Request $request)
    {
        $doctor = Auth::user()->doctor;
        $days = $request->get('days', 7); // Default 7 days

        $upcomingAppointments = Appointment::with(['patient.user'])
            ->where('doctor_id', $doctor->id)
            ->whereBetween('appointment_date', [
                Carbon::tomorrow(),
                Carbon::now()->addDays($days)
            ])
            ->orderBy('appointment_date')
            ->orderBy('start_time')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $upcomingAppointments,
        ]);
    }

    /**
     * Get dashboard statistics (AJAX endpoint for real-time updates).
     */
    public function getDashboardStats()
    {
        $doctor = Auth::user()->doctor;
        $statistics = $this->getDashboardStatistics($doctor->id);

        return response()->json([
            'success' => true,
            'data' => $statistics,
        ]);
    }
}