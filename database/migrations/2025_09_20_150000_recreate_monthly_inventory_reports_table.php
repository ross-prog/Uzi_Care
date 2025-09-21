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
        // Drop the existing table to recreate with proper structure
        Schema::dropIfExists('monthly_inventory_reports');
        
        Schema::create('monthly_inventory_reports', function (Blueprint $table) {
            $table->id();
            $table->string('campus');
            $table->integer('report_month'); // 1-12
            $table->integer('report_year'); // e.g., 2025
            $table->foreignId('generated_by')->constrained('users');
            $table->enum('status', ['draft', 'submitted'])->default('draft');
            $table->json('inventory_data'); // Simplified: medicine_name, current_stock, quantity_to_order
            $table->timestamp('generated_at');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
            
            // Ensure one report per campus per month
            $table->unique(['campus', 'report_month', 'report_year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monthly_inventory_reports');
    }
};