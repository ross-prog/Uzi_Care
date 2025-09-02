<?php

namespace Database\Seeders;

use App\Models\Inventory;
use App\Models\Medicine;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class InventorySeeder extends Seeder
{
    public function run(): void
    {
        $medicines = Medicine::all();
        
        foreach ($medicines as $medicine) {
            // Create multiple inventory entries for each medicine with different expiry dates
            $inventoryData = [
                [
                    'medicine_id' => $medicine->id,
                    'quantity' => rand(50, 200),
                    'expiry_date' => Carbon::now()->addMonths(rand(6, 24)),
                    'batch_number' => 'BATCH' . rand(1000, 9999),
                    'supplier' => 'Medical Supply Co.',
                    'cost_per_unit' => rand(5, 50) / 10,
                    'low_stock_threshold' => 20,
                ],
                [
                    'medicine_id' => $medicine->id,
                    'quantity' => rand(10, 50),
                    'expiry_date' => Carbon::now()->addDays(rand(15, 45)), // Some nearing expiry
                    'batch_number' => 'BATCH' . rand(1000, 9999),
                    'supplier' => 'PharmaCorp Ltd.',
                    'cost_per_unit' => rand(5, 50) / 10,
                    'low_stock_threshold' => 20,
                ],
            ];
            
            // Some items will be low stock
            if (rand(1, 3) === 1) {
                $inventoryData[0]['quantity'] = rand(5, 15); // Low stock
            }
            
            foreach ($inventoryData as $data) {
                Inventory::create($data);
            }
        }
    }
}
