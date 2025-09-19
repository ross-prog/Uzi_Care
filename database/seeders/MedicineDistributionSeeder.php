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
            'North Campus',
            'South Campus',
            'East Campus',
            'West Campus',
            'Downtown Campus',
            'Satellite Clinic A',
            'Satellite Clinic B'
        ];

        // Get inventory managers for each campus to act as distributors
        $inventoryManagers = User::where('role', 'inventory_manager')->get()->keyBy('campus');

        // Main Campus acts as the primary distributor to other campuses
        $mainCampusManager = $inventoryManagers->get('Main Campus');
        
        if (!$mainCampusManager) {
            $this->command->warn('No inventory manager found for Main Campus. Skipping distribution seeding.');
            return;
        }

        // Create distributions from Main Campus to other campuses
        foreach ($campuses as $toCampus) {
            if ($toCampus === 'Main Campus') continue; // Skip self-distribution
            
            // Get some inventory items from Main Campus to distribute
            $mainCampusInventories = Inventory::where('campus', 'Main Campus')
                ->where('quantity', '>', 50) // Only distribute from items with good stock
                ->inRandomOrder()
                ->limit(rand(15, 25)) // Distribute 15-25 different items per campus
                ->get();

            foreach ($mainCampusInventories as $inventory) {
                // Calculate distribution quantity (10-30% of available stock)
                $maxDistribution = (int)($inventory->quantity * 0.3);
                $distributionQuantity = rand(10, max(10, $maxDistribution));
                
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
                    'status' => 'completed', // All seeded distributions are completed
                    'distribution_date' => Carbon::now()->subDays(rand(1, 30)),
                    'notes' => $this->getDistributionNotes(),
                ]);

                // Update source inventory (decrease quantity)
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
                    'distributor' => 'Main Campus', // Source campus as distributor
                    'cost_per_unit' => $inventory->cost_per_unit,
                    'low_stock_threshold' => $inventory->low_stock_threshold,
                    'created_at' => $distribution->distribution_date,
                    'updated_at' => $distribution->distribution_date,
                ]);
            }
        }

        // Create some inter-campus distributions (between non-main campuses)
        $nonMainCampuses = array_filter($campuses, fn($c) => $c !== 'Main Campus');
        
        for ($i = 0; $i < 20; $i++) { // Create 20 inter-campus distributions
            $fromCampus = $nonMainCampuses[array_rand($nonMainCampuses)];
            $toCampus = $nonMainCampuses[array_rand($nonMainCampuses)];
            
            if ($fromCampus === $toCampus) continue;
            
            $fromManager = $inventoryManagers->get($fromCampus);
            if (!$fromManager) continue;
            
            // Get inventory from the source campus
            $sourceInventory = Inventory::where('campus', $fromCampus)
                ->where('quantity', '>', 20)
                ->inRandomOrder()
                ->first();
                
            if (!$sourceInventory) continue;
            
            $distributionQuantity = rand(5, min(15, (int)($sourceInventory->quantity * 0.4)));
            
            $distribution = MedicineDistribution::create([
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
                'distribution_date' => Carbon::now()->subDays(rand(1, 15)),
                'notes' => 'Inter-campus transfer',
            ]);

            // Update source inventory
            $sourceInventory->update([
                'quantity' => $sourceInventory->quantity - $distributionQuantity
            ]);

            // Create or update inventory entry for receiving campus
            $existingInventory = Inventory::where([
                'medicine_id' => $sourceInventory->medicine_id,
                'campus' => $toCampus,
                'batch_number' => $sourceInventory->batch_number,
            ])->first();

            if ($existingInventory) {
                // Add to existing inventory
                $existingInventory->update([
                    'quantity' => $existingInventory->quantity + $distributionQuantity
                ]);
            } else {
                // Create new inventory entry
                Inventory::create([
                    'medicine_id' => $sourceInventory->medicine_id,
                    'campus' => $toCampus,
                    'quantity' => $distributionQuantity,
                    'expiry_date' => $sourceInventory->expiry_date,
                    'batch_number' => $sourceInventory->batch_number,
                    'distributor' => $fromCampus,
                    'cost_per_unit' => $sourceInventory->cost_per_unit,
                    'low_stock_threshold' => $sourceInventory->low_stock_threshold,
                    'created_at' => $distribution->distribution_date,
                    'updated_at' => $distribution->distribution_date,
                ]);
            }
        }

        $distributionCount = MedicineDistribution::count();
        $this->command->info("Created {$distributionCount} medicine distributions with corresponding inventory adjustments.");
    }

    private function getRandomDepartment()
    {
        $departments = [
            'Emergency Department',
            'Internal Medicine',
            'Pediatrics',
            'Surgery',
            'Pharmacy',
            'Outpatient Clinic',
            'ICU',
            'Cardiology',
            'Orthopedics',
            'General Practice'
        ];

        return $departments[array_rand($departments)];
    }

    private function getDistributionNotes()
    {
        $notes = [
            'Regular restocking',
            'Emergency supply request',
            'Monthly distribution',
            'Low stock replenishment',
            'Planned distribution',
            'Urgent medical supply',
            'Seasonal restocking',
            'Department request',
            'Inventory balancing',
            'Standard supply chain'
        ];

        return $notes[array_rand($notes)];
    }
}