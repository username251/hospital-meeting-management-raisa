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
        Schema::create('feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained()->onDelete('cascade'); // Terhubung ke appointment
            $table->foreignId('doctor_id')->constrained()->onDelete('cascade');      // Dokter yang dinilai
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');     // Pasien yang memberi nilai
            $table->unsignedTinyInteger('rating'); // Rating dari 1 sampai 5
            $table->text('comment')->nullable();   // Komentar dari pasien
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feedback');
    }
};