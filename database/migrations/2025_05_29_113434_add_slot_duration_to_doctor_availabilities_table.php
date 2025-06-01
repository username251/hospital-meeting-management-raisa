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
            // Tambahkan kolom 'slot_duration' sebagai integer
            // Saya asumsikan defaultnya adalah 30 menit, Anda bisa sesuaikan ini.
            // Penempatan 'after' juga bisa disesuaikan dengan kebutuhan Anda.
            $table->integer('slot_duration')->default(30)->after('end_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('doctor_availabilities', function (Blueprint $table) {
            // Hapus kolom 'slot_duration' jika migrasi di-rollback
            $table->dropColumn('slot_duration');
        });
    }
};