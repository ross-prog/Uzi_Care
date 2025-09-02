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
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medicine_id')->constrained()->onDelete('cascade');
            $table->integer('quantity');
            $table->date('expiry_date');
            $table->string('batch_number')->nullable();
            $table->string('supplier')->nullable();
            $table->decimal('cost_per_unit', 10, 2)->nullable();
            $table->integer('low_stock_threshold')->default(10);
            $table->timestamps();
            
            $table->index(['medicine_id', 'expiry_date']);
            $table->index('quantity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventories');
    }
};
