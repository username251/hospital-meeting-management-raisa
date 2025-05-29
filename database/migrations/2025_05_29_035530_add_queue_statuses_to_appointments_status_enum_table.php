<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // Tambahkan ini

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Pastikan untuk memasukkan SEMUA nilai ENUM yang sudah ada
        // ditambah nilai-nilai baru yang ingin Anda tambahkan.
        // Contoh: 'scheduled' (jika sudah ditambahkan sebelumnya), 'pending', 'confirmed',
        // 'completed', 'cancelled', 'rescheduled', 'check-in', 'waiting', 'in-consultation', 'no-show'.
        DB::statement("ALTER TABLE appointments CHANGE COLUMN status status ENUM('pending','confirmed','completed','cancelled','rescheduled','scheduled','check-in','waiting','in-consultation','no-show') DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Jika Anda ingin mengembalikan, hapus nilai-nilai baru yang Anda tambahkan
        DB::statement("ALTER TABLE appointments CHANGE COLUMN status status ENUM('pending','confirmed','completed','cancelled','rescheduled') DEFAULT 'pending'");
    }
};