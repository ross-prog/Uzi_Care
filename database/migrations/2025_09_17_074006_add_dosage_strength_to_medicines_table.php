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
        Schema::table('medicines', function (Blueprint $table) {
            $table->string('dosage_strength')->nullable()->after('type'); // e.g., "500mg", "10ml", "250mg/5ml"
            $table->string('form')->nullable()->after('dosage_strength'); // e.g., "tablet", "capsule", "syrup", "injection"
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('medicines', function (Blueprint $table) {
            $table->dropColumn(['dosage_strength', 'form']);
        });
    }
};
