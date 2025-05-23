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
        Schema::create('doctor_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained('doctors')->onDelete('cascade');
            // Using tinyInteger for day_of_week (1=Monday to 7=Sunday)
            $table->tinyInteger('day_of_week')->comment('1=Monday, 7=Sunday');
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('is_available')->default(true); // Can be used to temporarily disable a schedule slot
            $table->timestamps();

            // Ensure a doctor doesn't have duplicate schedule entries for the same day and time range
           // Memberi nama unik yang lebih pendek untuk mengatasi batasan panjang nama identifier MySQL
            $table->unique(['doctor_id', 'day_of_week', 'start_time', 'end_time'], 'doctor_schedule_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctor_schedules');
    }
};