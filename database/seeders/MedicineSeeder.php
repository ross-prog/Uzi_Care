<?php

namespace Database\Seeders;

use App\Models\Medicine;
use Illuminate\Database\Seeder;

class MedicineSeeder extends Seeder
{
    public function run(): void
    {
        $medicines = [
            ['name' => 'Acetaminophen', 'description' => 'Pain reliever and fever reducer', 'type' => 'Analgesic', 'unit' => 'tablet'],
            ['name' => 'Amoxicillin', 'description' => 'Antibiotic for bacterial infections', 'type' => 'Antibiotic', 'unit' => 'capsule'],
            ['name' => 'Aspirin', 'description' => 'Pain reliever, anti-inflammatory', 'type' => 'NSAID', 'unit' => 'tablet'],
            ['name' => 'Bandage', 'description' => 'Wound dressing and protection', 'type' => 'Medical Supply', 'unit' => 'piece'],
            ['name' => 'Betadine', 'description' => 'Antiseptic solution', 'type' => 'Antiseptic', 'unit' => 'ml'],
            ['name' => 'Cetirizine', 'description' => 'Antihistamine for allergies', 'type' => 'Antihistamine', 'unit' => 'tablet'],
            ['name' => 'Dextromethorphan', 'description' => 'Cough suppressant', 'type' => 'Antitussive', 'unit' => 'ml'],
            ['name' => 'Gauze Pads', 'description' => 'Sterile wound covering', 'type' => 'Medical Supply', 'unit' => 'piece'],
            ['name' => 'Ibuprofen', 'description' => 'Pain reliever and anti-inflammatory', 'type' => 'NSAID', 'unit' => 'tablet'],
            ['name' => 'Loratadine', 'description' => 'Non-drowsy allergy relief', 'type' => 'Antihistamine', 'unit' => 'tablet'],
            ['name' => 'Mefenamic Acid', 'description' => 'Pain reliever for menstrual cramps', 'type' => 'NSAID', 'unit' => 'tablet'],
            ['name' => 'Omeprazole', 'description' => 'Acid reflux and heartburn relief', 'type' => 'Proton Pump Inhibitor', 'unit' => 'capsule'],
            ['name' => 'Paracetamol', 'description' => 'Fever reducer and pain reliever', 'type' => 'Analgesic', 'unit' => 'tablet'],
            ['name' => 'Thermometer', 'description' => 'Digital body temperature measurement', 'type' => 'Medical Device', 'unit' => 'piece'],
            ['name' => 'Vitamin C', 'description' => 'Immune system support', 'type' => 'Vitamin', 'unit' => 'tablet'],
        ];

        foreach ($medicines as $medicine) {
            Medicine::create($medicine);
        }
    }
}
