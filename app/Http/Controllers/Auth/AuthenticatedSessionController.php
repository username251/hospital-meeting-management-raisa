<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate(); // Otentikasi user

        $request->session()->regenerate(); // Regenerasi session

        $user = Auth::user(); // Ambil user yang baru saja login

        switch ($user->role) {
            case 'admin':
                return redirect()->intended(route('admin.index', absolute: false)); // Contoh: ke /admin/dashboard
            case 'doctor':
                return redirect()->intended(route('doctor.dashboard', absolute: false)); // Contoh: ke /doctor/dashboard
            case 'staff':
                return redirect()->intended(route('staff.dashboard', absolute: false)); // Contoh: ke /staff/dashboard
            case 'patient':
            default: // Default untuk 'patient' atau role lain yang tidak spesifik
                return redirect()->intended(route('home.dashboard', absolute: false)); // Contoh: ke /patient/dashboard atau /dashboard
        }
        // --- Logika Pengalihan Berdasarkan Role ---
        // --- Akhir Logika Pengalihan Berdasarkan Role ---
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}