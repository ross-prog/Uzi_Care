<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Fix existing monthly inventory reports with NULL quantity_to_order
        $reports = DB::table('monthly_inventory_reports')
            ->whereNull('quantity_to_order')
            ->get();
        
        foreach ($reports as $report) {
            $inventoryData = json_decode($report->inventory_data, true);
            $quantityToOrder = [];
            
            if (is_array($inventoryData)) {
                foreach ($inventoryData as $item) {
                    if (isset($item['medicine_name'])) {
                        // Initialize all medicines with 0 quantity to order
                        $quantityToOrder[$item['medicine_name']] = 0;
                    }
                }
                
                // Update the report with the new quantity_to_order structure
                DB::table('monthly_inventory_reports')
                    ->where('id', $report->id)
                    ->update(['quantity_to_order' => json_encode($quantityToOrder)]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Set quantity_to_order back to NULL for reports that were fixed
        DB::table('monthly_inventory_reports')
            ->update(['quantity_to_order' => null]);
    }
};