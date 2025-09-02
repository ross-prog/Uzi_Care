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
        Schema::create('patient_consultation_records', function (Blueprint $table) {
            $table->id();
            
            // Basic Information
            $table->string('student_employee_id');
            $table->timestamp('consultation_date_time');
            
            // Personal Information
            $table->string('last_name');
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->integer('age');
            $table->date('birthdate');
            $table->enum('civil_status', ['Single', 'Married', 'Divorced', 'Widowed', 'Separated'])->default('Single');
            $table->enum('sex', ['Male', 'Female']);
            $table->text('address');
            $table->string('department_course');
            $table->string('contact_no');
            
            // Guardian Information
            $table->string('guardian_name')->nullable();
            $table->string('guardian_relationship')->nullable();
            $table->string('guardian_contact_no')->nullable();
            
            // Chief Complaints
            $table->text('chief_complaints');
            
            // Medical History
            $table->boolean('has_allergy')->default(false);
            $table->text('allergy_specify')->nullable();
            $table->boolean('has_hypertension')->default(false);
            $table->boolean('has_diabetes')->default(false);
            $table->boolean('has_asthma')->default(false);
            $table->date('asthma_last_attack')->nullable();
            $table->text('other_medical_history')->nullable();
            
            // Vital Signs (Time 1)
            $table->time('vital_signs_time_1')->nullable();
            $table->decimal('weight', 5, 2)->nullable();
            $table->decimal('height', 5, 2)->nullable();
            $table->decimal('bmi', 4, 2)->nullable();
            $table->date('last_menstrual_period')->nullable(); // For females only
            $table->string('blood_pressure_1')->nullable(); // e.g., "120/80"
            $table->integer('heart_rate_1')->nullable();
            $table->integer('respiratory_rate_1')->nullable();
            $table->decimal('temperature_1', 4, 1)->nullable();
            $table->integer('oxygen_saturation_1')->nullable();
            
            // Vital Signs (Time 2)
            $table->time('vital_signs_time_2')->nullable();
            $table->string('blood_pressure_2')->nullable();
            $table->integer('heart_rate_2')->nullable();
            $table->integer('respiratory_rate_2')->nullable();
            $table->decimal('temperature_2', 4, 1)->nullable();
            $table->integer('oxygen_saturation_2')->nullable();
            
            // Diagnosis and Staff
            $table->text('diagnosis');
            $table->string('nurse_on_duty');
            $table->string('physician_on_duty')->nullable();
            
            // Medicines and Equipment (keeping from original)
            $table->json('medicines')->nullable();
            $table->json('equipment')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index('student_employee_id');
            $table->index('consultation_date_time');
            $table->index(['last_name', 'first_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_consultation_records');
    }
};
