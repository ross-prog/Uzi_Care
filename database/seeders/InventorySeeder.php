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
                    'distributor' => 'Main Campus', // Main campus as primary distributor
                    'date_added' => Carbon::now()->subDays(rand(1, 30)),
                    'low_stock_threshold' => 20,
                    'campus' => 'Main Campus', // Add campus field
                ],
                [
                    'medicine_id' => $medicine->id,
                    'quantity' => rand(10, 50),
                    'expiry_date' => Carbon::now()->addDays(rand(15, 45)), // Some nearing expiry
                    'batch_number' => 'BATCH' . rand(1000, 9999),
                    'distributor' => 'Main Campus', // Received from main campus
                    'date_added' => Carbon::now()->subDays(rand(1, 15)),
                    'low_stock_threshold' => 20,
                    'campus' => 'North Campus', // Add campus field
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
