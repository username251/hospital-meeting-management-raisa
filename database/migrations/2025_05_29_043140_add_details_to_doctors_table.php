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
        $table->string('phone_number')->nullable()->after('specialty_id');
        // Hapus baris ini jika license_number sudah ada
        // $table->string('license_number')->unique()->nullable()->after('phone_number');
        $table->text('bio')->nullable()->after('phone_number'); // Sesuaikan 'after' jika license_number dihapus
        $table->decimal('consultation_fee', 10, 2)->default(0)->after('bio');
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
