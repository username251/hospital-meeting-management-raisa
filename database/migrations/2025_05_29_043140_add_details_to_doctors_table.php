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
     Schema::table('doctors', function (Blueprint $table) {
    if (!Schema::hasColumn('doctors', 'phone_number')) {
        $table->string('phone_number')->nullable()->after('specialty_id');
    }
    // ... baris lain ...
});
}

public function down(): void
{
    Schema::table('doctors', function (Blueprint $table) {
        $table->dropColumn(['phone_number', 'bio', 'consultation_fee']);
        // Jangan drop license_number jika tidak ditambahkan oleh migrasi ini
        // $table->dropColumn('license_number');
    });
}
};
