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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignId('doctor_id')->constrained('doctors')->onDelete('cascade');
            $table->foreignId('specialty_id')->nullable()->constrained('specialties')->onDelete('set null'); // Nullable, if specialty is deleted, set to null
            $table->date('appointment_date');
            $table->time('start_time');
            $table->time('end_time'); // Calculated based on service duration
            $table->text('reason')->nullable();
            $table->enum('status', ['pending', 'confirmed', 'completed', 'cancelled', 'rescheduled'])->default('pending');
            $table->text('notes')->nullable(); // Notes by staff/doctor
            $table->timestamps();

            // Add index for faster queries
            $table->index(['appointment_date', 'doctor_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};