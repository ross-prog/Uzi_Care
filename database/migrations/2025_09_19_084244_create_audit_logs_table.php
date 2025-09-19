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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            
            // Record being audited
            $table->string('auditable_type'); // Model class name (e.g., PatientConsultationRecord)
            $table->unsignedBigInteger('auditable_id'); // ID of the record
            
            // Change details
            $table->string('event'); // create, update, delete, view
            $table->json('old_values')->nullable(); // Previous values before change
            $table->json('new_values')->nullable(); // New values after change
            $table->json('changes')->nullable(); // Only the fields that changed
            
            // User who made the change
            $table->string('user_type')->nullable(); // User model type
            $table->unsignedBigInteger('user_id')->nullable(); // User ID
            $table->string('user_name')->nullable(); // User display name
            $table->string('user_role')->nullable(); // User role (nurse, doctor, admin)
            
            // Additional metadata
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->json('metadata')->nullable(); // Additional context data
            $table->text('description')->nullable(); // Human readable description
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['auditable_type', 'auditable_id']);
            $table->index(['event']);
            $table->index(['user_id']);
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
