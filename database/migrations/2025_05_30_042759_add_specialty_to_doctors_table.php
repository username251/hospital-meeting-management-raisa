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
    $table->string('specialty')->nullable()->after('name'); // Sesuaikan tipe data (string, integer, dll.) dan apakah boleh nullable
    // Anda bisa mengubah `after('name')` jika ingin posisi kolomnya di tempat lain
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            //
        });
    }
};
