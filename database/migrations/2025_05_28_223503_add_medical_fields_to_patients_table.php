<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            // Periksa apakah kolom sudah ada sebelum menambahkannya
            // Ini adalah praktik yang baik untuk menghindari error di masa depan
            if (!Schema::hasColumn('patients', 'medical_history')) {
                $table->text('medical_history')->nullable()->after('gender');
            }
            if (!Schema::hasColumn('patients', 'allergies')) {
                $table->text('allergies')->nullable()->after('medical_history');
            }
            if (!Schema::hasColumn('patients', 'current_medications')) {
                $table->text('current_medications')->nullable()->after('allergies');
            }
            if (!Schema::hasColumn('patients', 'blood_type')) {
                $table->string('blood_type', 5)->nullable()->after('current_medications');
            }
        });
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            // Periksa apakah kolom ada sebelum menghapusnya
            if (Schema::hasColumn('patients', 'medical_history')) {
                $table->dropColumn('medical_history');
            }
            if (Schema::hasColumn('patients', 'allergies')) {
                $table->dropColumn('allergies');
            }
            if (Schema::hasColumn('patients', 'current_medications')) {
                $table->dropColumn('current_medications');
            }
            if (Schema::hasColumn('patients', 'blood_type')) {
                $table->dropColumn('blood_type');
            }
        });
    }
};