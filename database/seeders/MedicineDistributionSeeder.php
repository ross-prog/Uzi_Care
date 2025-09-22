<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\MedicineDistribution;
use App\Models\Inventory;
use App\Models\User;
use Carbon\Carbon;

class MedicineDistributionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $campuses = [
            'Main Campus',
            'THS',
            'SHS',
            'Laboratory'
        ];

        // Get administrators and head nurses for each campus
        $campusManagers = User::whereIn('role', ['admin', 'head_nurse'])->get()->keyBy('campus');

        // Main Campus acts as the primary distributor
        $mainCampusManager = $campusManagers->get('Main Campus');
        
        if (!$mainCampusManager) {
            $this->command->warn('No administrator found for Main Campus. Skipping distribution seeding.');
            return;
        }

        // Create distributions from Main Campus to other campuses
        foreach ($campuses as $toCampus) {
            if ($toCampus === 'Main Campus') continue;
            
            // Get limited inventory items from Main Campus
            $mainCampusInventories = Inventory::where('campus', 'Main Campus')
                ->where('quantity', '>', 30)
                ->inRandomOrder()
                ->limit(rand(8, 12)) // Distribute 8-12 items per campus
                ->get();

            foreach ($mainCampusInventories as $inventory) {
                // Calculate distribution quantity (15-25% of available stock)
                $maxDistribution = (int)($inventory->quantity * 0.25);
                $distributionQuantity = rand(5, max(5, $maxDistribution));
                
                // Create the distribution record
                $distribution = MedicineDistribution::create([
                    'medicine_id' => $inventory->medicine_id,
                    'inventory_id' => $inventory->id,
                    'distributed_by' => $mainCampusManager->id,
                    'from_campus' => 'Main Campus',
                    'to_campus' => $toCampus,
                    'to_department' => $this->getRandomDepartment(),
                    'quantity_distributed' => $distributionQuantity,
                    'batch_number' => $inventory->batch_number,
                    'expiry_date' => $inventory->expiry_date,
                    'status' => 'completed',
                    'distribution_date' => Carbon::now()->subDays(rand(1, 20)),
                    'notes' => $this->getDistributionNotes(),
                ]);

                // Update source inventory
                $inventory->update([
                    'quantity' => $inventory->quantity - $distributionQuantity
                ]);

                // Create inventory entry for receiving campus
                Inventory::create([
                    'medicine_id' => $inventory->medicine_id,
                    'campus' => $toCampus,
                    'quantity' => $distributionQuantity,
                    'expiry_date' => $inventory->expiry_date,
                    'batch_number' => $inventory->batch_number,
                    'distributor' => 'Main Campus',
                    'date_added' => $distribution->distribution_date,
                    'low_stock_threshold' => $inventory->low_stock_threshold,
                ]);
            }
        }

        // Create a few inter-campus distributions
        $nonMainCampuses = ['THS', 'SHS', 'Laboratory'];
        
        for ($i = 0; $i < 6; $i++) { // Create only 6 inter-campus distributions
            $fromCampus = $nonMainCampuses[array_rand($nonMainCampuses)];
            $toCampus = $nonMainCampuses[array_rand($nonMainCampuses)];
            
            if ($fromCampus === $toCampus) continue;
            
            $fromManager = $campusManagers->get($fromCampus);
            if (!$fromManager) continue;
            
            // Get inventory from the source campus
            $sourceInventory = Inventory::where('campus', $fromCampus)
                ->where('quantity', '>', 15)
                ->inRandomOrder()
                ->first();
                
            if (!$sourceInventory) continue;
            
            $distributionQuantity = rand(3, min(10, (int)($sourceInventory->quantity * 0.3)));
            
            MedicineDistribution::create([
                'medicine_id' => $sourceInventory->medicine_id,
                'inventory_id' => $sourceInventory->id,
                'distributed_by' => $fromManager->id,
                'from_campus' => $fromCampus,
                'to_campus' => $toCampus,
                'to_department' => $this->getRandomDepartment(),
                'quantity_distributed' => $distributionQuantity,
                'batch_number' => $sourceInventory->batch_number,
                'expiry_date' => $sourceInventory->expiry_date,
                'status' => 'completed',
                'distribution_date' => Carbon::now()->subDays(rand(1, 10)),
                'notes' => 'Inter-campus transfer',
            ]);

            // Update source inventory
            $sourceInventory->update([
                'quantity' => $sourceInventory->quantity - $distributionQuantity
            ]);
        }

        $distributionCount = MedicineDistribution::count();
        $this->command->info("Created {$distributionCount} medicine distributions.");
    }

    private function getRandomDepartment()
    {
        $departments = [
            'Emergency Department',
            'Internal Medicine',
            'Pharmacy',
            'Outpatient Clinic',
            'General Practice'
        ];

        return $departments[array_rand($departments)];
    }

    private function getDistributionNotes()
    {
        $notes = [
            'Regular restocking',
            'Emergency supply',
            'Monthly distribution',
            'Low stock replenishment',
            'Standard supply'
        ];

        return $notes[array_rand($notes)];
    }
}