<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('doctor_availabilities', function (Blueprint $table) {
            // Tambahkan kolom 'is_available' sebagai boolean (true/false)
            // Defaultnya adalah 'true' (aktif) agar jadwal yang sudah ada menjadi aktif secara default.
            // Anda bisa menempatkannya setelah kolom 'slot_duration' atau di mana pun Anda inginkan.
            $table->boolean('is_available')->default(true)->after('notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('doctor_availabilities', function (Blueprint $table) {
            // Hapus kolom 'is_available' jika migrasi di-rollback
            $table->dropColumn('is_available');
        });
    }
};