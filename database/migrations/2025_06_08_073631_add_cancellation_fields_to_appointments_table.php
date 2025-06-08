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
        Schema::table('appointments', function (Blueprint $table) {
            $table->string('cancellation_reason')->nullable()->after('reason');
            $table->timestamp('cancelled_at')->nullable()->after('cancellation_reason');
            $table->enum('cancelled_by', ['patient', 'doctor', 'admin', 'system'])->nullable()->after('cancelled_at');
            
            // Index untuk query yang lebih cepat
            $table->index(['status', 'appointment_date', 'end_time'], 'idx_appointments_status_date_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropIndex('idx_appointments_status_date_time');
            $table->dropColumn(['cancellation_reason', 'cancelled_at', 'cancelled_by']);
        });
    }
};