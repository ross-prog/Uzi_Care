<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Medicine;
use App\Models\Inventory;
use Carbon\Carbon;

class MedicineInventorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define campuses
        $campuses = [
            'Main Campus',
            'THS',
            'SHS',
            'Laboratory'
        ];

        // Define common medicines
        $medicines = [
            [
                'name' => 'Paracetamol',
                'description' => 'Pain reliever and fever reducer',
                'type' => 'Analgesic',
                'unit' => 'tablets',
                'dosage_strength' => '500mg',
                'form' => 'Tablet'
            ],
            [
                'name' => 'Ibuprofen',
                'description' => 'Anti-inflammatory pain reliever',
                'type' => 'NSAID',
                'unit' => 'tablets',
                'dosage_strength' => '400mg',
                'form' => 'Tablet'
            ],
            [
                'name' => 'Amoxicillin',
                'description' => 'Antibiotic for bacterial infections',
                'type' => 'Antibiotic',
                'unit' => 'capsules',
                'dosage_strength' => '250mg',
                'form' => 'Capsule'
            ],
            [
                'name' => 'Cetirizine',
                'description' => 'Antihistamine for allergies',
                'type' => 'Antihistamine',
                'unit' => 'tablets',
                'dosage_strength' => '10mg',
                'form' => 'Tablet'
            ],
            [
                'name' => 'Omeprazole',
                'description' => 'Proton pump inhibitor for acid reflux',
                'type' => 'PPI',
                'unit' => 'capsules',
                'dosage_strength' => '20mg',
                'form' => 'Capsule'
            ],
            [
                'name' => 'Salbutamol',
                'description' => 'Bronchodilator for asthma',
                'type' => 'Bronchodilator',
                'unit' => 'inhalers',
                'dosage_strength' => '100mcg',
                'form' => 'Inhaler'
            ],
            [
                'name' => 'Loratadine',
                'description' => 'Long-acting antihistamine',
                'type' => 'Antihistamine',
                'unit' => 'tablets',
                'dosage_strength' => '10mg',
                'form' => 'Tablet'
            ],
            [
                'name' => 'Metformin',
                'description' => 'Diabetes medication',
                'type' => 'Antidiabetic',
                'unit' => 'tablets',
                'dosage_strength' => '500mg',
                'form' => 'Tablet'
            ],
            [
                'name' => 'Amlodipine',
                'description' => 'Calcium channel blocker for hypertension',
                'type' => 'Antihypertensive',
                'unit' => 'tablets',
                'dosage_strength' => '5mg',
                'form' => 'Tablet'
            ],
            [
                'name' => 'Simvastatin',
                'description' => 'Statin for cholesterol management',
                'type' => 'Statin',
                'unit' => 'tablets',
                'dosage_strength' => '20mg',
                'form' => 'Tablet'
            ],
            [
                'name' => 'Aspirin',
                'description' => 'Low-dose for cardiovascular protection',
                'type' => 'Antiplatelet',
                'unit' => 'tablets',
                'dosage_strength' => '81mg',
                'form' => 'Tablet'
            ],
            [
                'name' => 'Dextromethorphan',
                'description' => 'Cough suppressant',
                'type' => 'Antitussive',
                'unit' => 'bottles',
                'dosage_strength' => '15mg/5ml',
                'form' => 'Syrup'
            ],
            [
                'name' => 'Loperamide',
                'description' => 'Anti-diarrheal medication',
                'type' => 'Antidiarrheal',
                'unit' => 'capsules',
                'dosage_strength' => '2mg',
                'form' => 'Capsule'
            ],
            [
                'name' => 'Ranitidine',
                'description' => 'H2 receptor antagonist for acid reduction',
                'type' => 'H2 Blocker',
                'unit' => 'tablets',
                'dosage_strength' => '150mg',
                'form' => 'Tablet'
            ],
            [
                'name' => 'Multivitamins',
                'description' => 'Daily vitamin supplement',
                'type' => 'Supplement',
                'unit' => 'tablets',
                'dosage_strength' => 'Various',
                'form' => 'Tablet'
            ]
        ];

        // Create medicines first
        foreach ($medicines as $medicineData) {
            $medicine = Medicine::firstOrCreate(
                ['name' => $medicineData['name']],
                $medicineData
            );

            // Create inventory for each campus
            foreach ($campuses as $campus) {
                // Create 2-3 different batches per medicine per campus
                for ($batch = 1; $batch <= rand(2, 3); $batch++) {
                    $batchNumber = strtoupper(substr($campus, 0, 2)) . date('y') . sprintf('%03d', $medicine->id) . sprintf('%02d', $batch);
                    
                    // Vary quantities based on campus size and medicine type
                    $baseQuantity = $this->getBaseQuantity($campus, $medicineData['type']);
                    $quantity = $baseQuantity + rand(-20, 50);
                    
                    // Generate realistic expiry dates (6 months to 3 years from now)
                    $expiryDate = Carbon::now()->addMonths(rand(6, 36));
                    
                    Inventory::create([
                        'medicine_id' => $medicine->id,
                        'campus' => $campus,
                        'quantity' => max(0, $quantity), // Ensure non-negative
                        'expiry_date' => $expiryDate,
                        'batch_number' => $batchNumber,
                        'distributor' => $this->getLogicalDistributor($campus),
                        'date_added' => Carbon::now()->subDays(rand(1, 60)),
                        'low_stock_threshold' => $this->getLowStockThreshold($medicineData['type']),
                    ]);
                }
            }
        }

        $this->command->info('Created ' . count($medicines) . ' medicines with inventory across ' . count($campuses) . ' campuses.');
    }

    private function getBaseQuantity($campus, $medicineType)
    {
        // Main campuses have more stock
        $campusMultiplier = in_array($campus, ['Main Campus', 'North Campus', 'South Campus']) ? 1.5 : 1.0;
        
        // Common medicines have higher stock
        $typeMultipliers = [
            'Analgesic' => 2.0,
            'NSAID' => 1.8,
            'Antihistamine' => 1.5,
            'Antibiotic' => 1.2,
            'Supplement' => 2.5,
            'Antitussive' => 1.0,
            'Bronchodilator' => 0.8,
            'Antidiabetic' => 1.3,
            'Antihypertensive' => 1.4,
            'Statin' => 1.1,
        ];

        $baseQuantity = 100;
        $typeMultiplier = $typeMultipliers[$medicineType] ?? 1.0;
        
        return (int)($baseQuantity * $campusMultiplier * $typeMultiplier);
    }

    private function getLowStockThreshold($medicineType)
    {
        $thresholds = [
            'Analgesic' => 50,
            'NSAID' => 40,
            'Antihistamine' => 30,
            'Antibiotic' => 25,
            'Bronchodilator' => 10,
            'Supplement' => 60,
        ];

        return $thresholds[$medicineType] ?? 20;
    }

    private function getLogicalDistributor($campus)
    {
        // Main Campus and larger campuses act as their own distributors
        // Smaller campuses typically receive from Main Campus
        $mainDistributors = [
            'Main Campus',
            'North Campus', 
            'South Campus',
            'East Campus',
            'West Campus',
            'Downtown Campus'
        ];

        if (in_array($campus, $mainDistributors)) {
            return $campus; // Self-sourced/main distributor
        } else {
            // Satellite clinics typically receive from Main Campus
            return 'Main Campus';
        }
    }
}
