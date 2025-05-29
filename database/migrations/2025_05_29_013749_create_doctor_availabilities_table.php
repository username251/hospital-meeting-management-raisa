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
        Schema::create('doctor_availabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained('doctors')->onDelete('cascade'); // Foreign key ke tabel doctors
            $table->string('day_of_week', 10); // Contoh: 'Monday', 'Tuesday', 'Wednesday'
            $table->time('start_time');
            $table->time('end_time');
            $table->text('notes')->nullable(); // Catatan tambahan (misal: istirahat makan siang)
            $table->timestamps();

            // Menambahkan constraint unik agar satu dokter tidak memiliki dua ketersediaan yang tumpang tindih
            // untuk hari dan waktu yang sama. Atau setidaknya tidak ada duplikat entri.
            $table->unique(['doctor_id', 'day_of_week', 'start_time', 'end_time'], 'unique_doctor_availability');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctor_availabilities');
    }
};