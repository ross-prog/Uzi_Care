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
        Schema::create('ehrs', function (Blueprint $table) {
            $table->id();
            $table->string('student_id');
            $table->string('student_name');
            $table->date('date');
            $table->string('visit_type');
            $table->text('complaint_diagnosis');
            $table->json('medicines')->nullable();
            $table->text('nurse_notes')->nullable();
            $table->string('nurse_assigned');
            $table->timestamps();
            
            $table->index('student_id');
            $table->index('date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ehrs');
    }
};
