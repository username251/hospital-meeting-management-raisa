<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function store(Request $request)
    {
        // Validasi input
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string'
        ]);

        // Simpan pesan ke database
        Contact::create($validated);

        // Kirim email notifikasi (opsional)
        try {
            Mail::to('admin@example.com')->send(new \App\Mail\ContactFormSubmission($validated));
        } catch (\Exception $e) {
            // Log error tapi jangan gagalkan proses
            \Log::error('Failed to send contact form email: ' . $e->getMessage());
        }

        // Redirect dengan pesan sukses
        return back()->with('success', 'Thank you for your message. We will contact you soon!');
    }
}