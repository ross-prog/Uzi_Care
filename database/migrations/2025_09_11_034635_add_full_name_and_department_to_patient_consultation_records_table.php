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
        Schema::table('patient_consultation_records', function (Blueprint $table) {
            $table->string('full_name')->nullable()->after('consultation_date_time');
            $table->string('department')->nullable()->after('address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patient_consultation_records', function (Blueprint $table) {
            $table->dropColumn(['full_name', 'department']);
        });
    }
};
