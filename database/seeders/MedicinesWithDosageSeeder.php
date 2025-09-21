<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Medicine;
use App\Models\Inventory;

class MedicinesWithDosageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $medicines = [
            // Pain Relief & Fever Reducers
            [
                'name' => 'Paracetamol 500mg',
                'description' => 'Pain reliever and fever reducer',
                'type' => 'Analgesic',
                'unit' => 'tablet',
                'dosage_strength' => '500mg',
                'form' => 'tablet',
                'stock' => 100
            ],
            [
                'name' => 'Paracetamol Syrup',
                'description' => 'Liquid pain reliever for children',
                'type' => 'Analgesic',
                'unit' => 'ml',
                'dosage_strength' => '120mg/5ml',
                'form' => 'syrup',
                'stock' => 30
            ],
            [
                'name' => 'Ibuprofen 400mg',
                'description' => 'Anti-inflammatory pain reliever',
                'type' => 'NSAID',
                'unit' => 'tablet',
                'dosage_strength' => '400mg',
                'form' => 'tablet',
                'stock' => 75
            ],
            [
                'name' => 'Aspirin 325mg',
                'description' => 'Pain reliever and blood thinner',
                'type' => 'Analgesic',
                'unit' => 'tablet',
                'dosage_strength' => '325mg',
                'form' => 'tablet',
                'stock' => 60
            ],
            [
                'name' => 'Mefenamic Acid 250mg',
                'description' => 'Pain reliever for menstrual cramps',
                'type' => 'NSAID',
                'unit' => 'capsule',
                'dosage_strength' => '250mg',
                'form' => 'capsule',
                'stock' => 45
            ],

            // Antibiotics
            [
                'name' => 'Amoxicillin 250mg',
                'description' => 'Antibiotic for bacterial infections',
                'type' => 'Antibiotic',
                'unit' => 'capsule',
                'dosage_strength' => '250mg',
                'form' => 'capsule',
                'stock' => 50
            ],
            [
                'name' => 'Amoxicillin 500mg',
                'description' => 'Antibiotic for bacterial infections - stronger dose',
                'type' => 'Antibiotic',
                'unit' => 'capsule',
                'dosage_strength' => '500mg',
                'form' => 'capsule',
                'stock' => 40
            ],
            [
                'name' => 'Erythromycin 250mg',
                'description' => 'Antibiotic for respiratory infections',
                'type' => 'Antibiotic',
                'unit' => 'tablet',
                'dosage_strength' => '250mg',
                'form' => 'tablet',
                'stock' => 35
            ],
            [
                'name' => 'Ciprofloxacin 500mg',
                'description' => 'Broad-spectrum antibiotic',
                'type' => 'Antibiotic',
                'unit' => 'tablet',
                'dosage_strength' => '500mg',
                'form' => 'tablet',
                'stock' => 25
            ],

            // Cough & Cold
            [
                'name' => 'Cough Syrup',
                'description' => 'Relieves cough symptoms',
                'type' => 'Antitussive',
                'unit' => 'ml',
                'dosage_strength' => '10mg/5ml',
                'form' => 'syrup',
                'stock' => 25
            ],
            [
                'name' => 'Dextromethorphan 15mg',
                'description' => 'Dry cough suppressant',
                'type' => 'Antitussive',
                'unit' => 'tablet',
                'dosage_strength' => '15mg',
                'form' => 'tablet',
                'stock' => 50
            ],

            // Antihistamines & Allergies
            [
                'name' => 'Loratadine 10mg',
                'description' => 'Antihistamine for allergies',
                'type' => 'Antihistamine',
                'unit' => 'tablet',
                'dosage_strength' => '10mg',
                'form' => 'tablet',
                'stock' => 40
            ],
            [
                'name' => 'Cetirizine 10mg',
                'description' => 'Fast-acting antihistamine',
                'type' => 'Antihistamine',
                'unit' => 'tablet',
                'dosage_strength' => '10mg',
                'form' => 'tablet',
                'stock' => 45
            ],

            // Vitamins & Supplements
            [
                'name' => 'Vitamin C 500mg',
                'description' => 'Immune system support',
                'type' => 'Vitamin',
                'unit' => 'tablet',
                'dosage_strength' => '500mg',
                'form' => 'tablet',
                'stock' => 120
            ],
            [
                'name' => 'Vitamin C 1000mg',
                'description' => 'High-dose immune support',
                'type' => 'Vitamin',
                'unit' => 'tablet',
                'dosage_strength' => '1000mg',
                'form' => 'chewable',
                'stock' => 80
            ],

            // Medical Supplies with Specifications
            [
                'name' => 'Alcohol Swab',
                'description' => 'Antiseptic swab for cleaning',
                'type' => 'Medical Supply',
                'unit' => 'piece',
                'dosage_strength' => '70% Isopropyl Alcohol',
                'form' => 'swab',
                'stock' => 200
            ],
            [
                'name' => 'Gauze Pad 2x2',
                'description' => 'Sterile wound dressing',
                'type' => 'Medical Supply',
                'unit' => 'piece',
                'dosage_strength' => '2x2 inches',
                'form' => 'pad',
                'stock' => 150
            ],
            [
                'name' => 'Elastic Bandage',
                'description' => 'Elastic wound dressing material',
                'type' => 'Medical Supply',
                'unit' => 'roll',
                'dosage_strength' => '2 inch x 4 yard',
                'form' => 'roll',
                'stock' => 80
            ],

            // Medical Devices with Specifications
            [
                'name' => 'Digital Thermometer',
                'description' => 'Digital temperature measurement device',
                'type' => 'Medical Device',
                'unit' => 'piece',
                'dosage_strength' => 'Digital LCD Display',
                'form' => 'digital',
                'stock' => 15
            ],
            [
                'name' => 'Disposable Syringe 5ml',
                'description' => 'Single-use injection syringe',
                'type' => 'Medical Device',
                'unit' => 'piece',
                'dosage_strength' => '5ml capacity',
                'form' => 'syringe',
                'stock' => 100
            ]
        ];

        foreach ($medicines as $medicineData) {
            $stock = $medicineData['stock'];
            unset($medicineData['stock']);

            // Create or update medicine
            $medicine = Medicine::updateOrCreate(
                ['name' => $medicineData['name']],
                $medicineData
            );

            // Create inventory record if it doesn't exist
            $existingInventory = Inventory::where('medicine_id', $medicine->id)->first();
            if (!$existingInventory) {
                Inventory::create([
                    'medicine_id' => $medicine->id,
                    'campus' => 'Main Campus',
                    'quantity' => $stock,
                    'expiry_date' => now()->addYears(2),
                    'batch_number' => 'BATCH-' . strtoupper(substr($medicine->name, 0, 3)) . '-' . date('Y'),
                    'distributor' => 'Main Campus',
                    'date_added' => now()->subDays(rand(1, 30)),
                    'low_stock_threshold' => 10,
                ]);
            }
        }
    }
}
