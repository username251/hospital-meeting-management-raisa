<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('doctor_availabilities', function (Blueprint $table) {
            DB::statement("ALTER TABLE doctor_availabilities CHANGE day_of_week day_of_week ENUM('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday') NOT NULL");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('doctor_availabilities', function (Blueprint $table) {
            DB::statement("ALTER TABLE doctor_availabilities CHANGE day_of_week day_of_week VARCHAR(255) NOT NULL");
        });
    }
};