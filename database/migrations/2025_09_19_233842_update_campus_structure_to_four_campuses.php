<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update users table campus values
        DB::statement("
            UPDATE users 
            SET campus = CASE 
                WHEN campus IN ('Main', 'main', 'Main Campus') THEN 'Main Campus'
                WHEN campus IN ('North', 'north', 'THS', 'ths') THEN 'THS'
                WHEN campus IN ('South', 'south', 'SHS', 'shs') THEN 'SHS'
                WHEN campus IN ('East', 'west', 'Downtown', 'Satellite A', 'Satellite B', 'Laboratory', 'lab') THEN 'Laboratory'
                ELSE 'Laboratory'
            END
        ");

        // Update inventories table campus values
        DB::statement("
            UPDATE inventories 
            SET campus = CASE 
                WHEN campus IN ('Main', 'main', 'Main Campus') THEN 'Main Campus'
                WHEN campus IN ('North', 'north', 'THS', 'ths') THEN 'THS'
                WHEN campus IN ('South', 'south', 'SHS', 'shs') THEN 'SHS'
                WHEN campus IN ('East', 'west', 'Downtown', 'Satellite A', 'Satellite B', 'Laboratory', 'lab') THEN 'Laboratory'
                ELSE 'Laboratory'
            END
        ");

        // Update monthly_inventory_reports table campus values
        DB::statement("
            UPDATE monthly_inventory_reports 
            SET campus = CASE 
                WHEN campus IN ('Main', 'main', 'Main Campus') THEN 'Main Campus'
                WHEN campus IN ('North', 'north', 'THS', 'ths') THEN 'THS'
                WHEN campus IN ('South', 'south', 'SHS', 'shs') THEN 'SHS'
                WHEN campus IN ('East', 'west', 'Downtown', 'Satellite A', 'Satellite B', 'Laboratory', 'lab') THEN 'Laboratory'
                ELSE 'Laboratory'
            END
        ");

        // Update medicine_distributions table if it exists
        if (Schema::hasTable('medicine_distributions')) {
            DB::statement("
                UPDATE medicine_distributions 
                SET from_campus = CASE 
                    WHEN from_campus IN ('Main', 'main', 'Main Campus') THEN 'Main Campus'
                    WHEN from_campus IN ('North', 'north', 'THS', 'ths') THEN 'THS'
                    WHEN from_campus IN ('South', 'south', 'SHS', 'shs') THEN 'SHS'
                    WHEN from_campus IN ('East', 'west', 'Downtown', 'Satellite A', 'Satellite B', 'Laboratory', 'lab') THEN 'Laboratory'
                    ELSE 'Laboratory'
                END,
                to_campus = CASE 
                    WHEN to_campus IN ('Main', 'main', 'Main Campus') THEN 'Main Campus'
                    WHEN to_campus IN ('North', 'north', 'THS', 'ths') THEN 'THS'
                    WHEN to_campus IN ('South', 'south', 'SHS', 'shs') THEN 'SHS'
                    WHEN to_campus IN ('East', 'west', 'Downtown', 'Satellite A', 'Satellite B', 'Laboratory', 'lab') THEN 'Laboratory'
                    ELSE 'Laboratory'
                END
            ");
        }

        // Set all Main Campus users to admin role, others to nurse
        DB::statement("
            UPDATE users 
            SET role = CASE 
                WHEN campus = 'Main Campus' THEN 'admin'
                ELSE 'nurse'
            END
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration is not easily reversible as it consolidates data
        // Manual restoration would be needed if rollback is required
    }
};
