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
        Schema::create('nurse_notes', function (Blueprint $table) {
            $table->id();
            
            // Link to patient consultation record
            $table->foreignId('patient_consultation_record_id')->constrained()->onDelete('cascade');
            
            // Patient Information (duplicated for easy access)
            $table->string('patient_name');
            $table->integer('age');
            $table->string('department');
            $table->string('student_employee_id');
            $table->string('contact_no');
            
            // Entry Information
            $table->timestamp('entry_date_time');
            
            // Notes and Orders
            $table->text('nurse_notes');
            $table->text('doctor_orders')->nullable();
            
            // Entry metadata
            $table->string('entered_by_nurse');
            $table->string('relationship')->nullable(); // For relationship field mentioned in form
            
            $table->timestamps();
            
            // Indexes
            $table->index('patient_consultation_record_id');
            $table->index('entry_date_time');
            $table->index('student_employee_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nurse_notes');
    }
};
