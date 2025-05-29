<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            // Tambahkan kolom yang hilang jika belum ada
            if (!Schema::hasColumn('patients', 'phone')) {
                $table->string('phone', 20)->nullable()->after('user_id');
            }
            if (!Schema::hasColumn('patients', 'address')) {
                $table->string('address')->nullable()->after('phone');
            }
            if (!Schema::hasColumn('patients', 'date_of_birth')) {
                $table->date('date_of_birth')->nullable()->after('address');
            }
            if (!Schema::hasColumn('patients', 'gender')) {
                $table->enum('gender', ['male', 'female', 'other'])->nullable()->after('date_of_birth');
            }
            // Kolom medis yang sudah kita bahas sebelumnya
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
            // Hapus kolom jika ada saat rollback
            if (Schema::hasColumn('patients', 'phone')) {
                $table->dropColumn('phone');
            }
            if (Schema::hasColumn('patients', 'address')) {
                $table->dropColumn('address');
            }
            if (Schema::hasColumn('patients', 'date_of_birth')) {
                $table->dropColumn('date_of_birth');
            }
            if (Schema::hasColumn('patients', 'gender')) {
                $table->dropColumn('gender');
            }
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