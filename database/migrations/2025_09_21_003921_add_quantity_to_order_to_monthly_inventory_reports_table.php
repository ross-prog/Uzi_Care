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
        Schema::table('monthly_inventory_reports', function (Blueprint $table) {
            // Add quantity_to_order JSON field to store order quantities for each medicine
            $table->json('quantity_to_order')->nullable()->after('inventory_data');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('monthly_inventory_reports', function (Blueprint $table) {
            $table->dropColumn('quantity_to_order');
        });
    }
};
