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
        DB::statement("ALTER TABLE appointments CHANGE COLUMN status status ENUM('pending','confirmed','completed','cancelled','rescheduled','scheduled') DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Jika ingin mengembalikan, hapus 'scheduled'
        DB::statement("ALTER TABLE appointments CHANGE COLUMN status status ENUM('pending','confirmed','completed','cancelled','rescheduled') DEFAULT 'pending'");
    }
};