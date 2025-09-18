<?php

namespace Database\Seeders;

use App\Models\Medicine;
use Illuminate\Database\Seeder;

class MedicineSeeder extends Seeder
{
    public function run(): void
    {
        $medicines = [
            // Medicines
            ['name' => 'Acetaminophen', 'description' => 'Pain reliever and fever reducer', 'type' => 'Analgesic', 'unit' => 'tablet'],
            ['name' => 'Amoxicillin', 'description' => 'Antibiotic for bacterial infections', 'type' => 'Antibiotic', 'unit' => 'capsule'],
            ['name' => 'Aspirin', 'description' => 'Pain reliever, anti-inflammatory', 'type' => 'NSAID', 'unit' => 'tablet'],
            ['name' => 'Cetirizine', 'description' => 'Antihistamine for allergies', 'type' => 'Antihistamine', 'unit' => 'tablet'],
            ['name' => 'Dextromethorphan', 'description' => 'Cough suppressant', 'type' => 'Antitussive', 'unit' => 'ml'],
            ['name' => 'Ibuprofen', 'description' => 'Pain reliever and anti-inflammatory', 'type' => 'NSAID', 'unit' => 'tablet'],
            ['name' => 'Loratadine', 'description' => 'Non-drowsy allergy relief', 'type' => 'Antihistamine', 'unit' => 'tablet'],
            ['name' => 'Mefenamic Acid', 'description' => 'Pain reliever for menstrual cramps', 'type' => 'NSAID', 'unit' => 'tablet'],
            ['name' => 'Omeprazole', 'description' => 'Acid reflux and heartburn relief', 'type' => 'Proton Pump Inhibitor', 'unit' => 'capsule'],
            ['name' => 'Paracetamol', 'description' => 'Fever reducer and pain reliever', 'type' => 'Analgesic', 'unit' => 'tablet'],
            ['name' => 'Vitamin C', 'description' => 'Immune system support', 'type' => 'Vitamin', 'unit' => 'tablet'],
            ['name' => 'Betadine', 'description' => 'Antiseptic solution', 'type' => 'Antiseptic', 'unit' => 'ml'],
            
            // Medical Supplies
            ['name' => 'Bandage', 'description' => 'Wound dressing and protection', 'type' => 'Supply', 'unit' => 'piece'],
            ['name' => 'Gauze Pads', 'description' => 'Sterile wound covering', 'type' => 'Supply', 'unit' => 'piece'],
            ['name' => 'Cotton Balls', 'description' => 'Soft absorbent cotton for cleaning', 'type' => 'Supply', 'unit' => 'piece'],
            ['name' => 'Alcohol Swabs', 'description' => 'Pre-moistened antiseptic wipes', 'type' => 'Supply', 'unit' => 'piece'],
            ['name' => 'Adhesive Tape', 'description' => 'Medical tape for securing bandages', 'type' => 'Supply', 'unit' => 'roll'],
            ['name' => 'Surgical Gloves', 'description' => 'Disposable latex examination gloves', 'type' => 'Supply', 'unit' => 'pair'],
            ['name' => 'Face Masks', 'description' => 'Disposable surgical face masks', 'type' => 'Supply', 'unit' => 'piece'],
            ['name' => 'Syringes', 'description' => 'Disposable injection syringes', 'type' => 'Supply', 'unit' => 'piece'],
            
            // Medical Equipment
            ['name' => 'Digital Thermometer', 'description' => 'Digital body temperature measurement', 'type' => 'Equipment', 'unit' => 'piece'],
            ['name' => 'Blood Pressure Monitor', 'description' => 'Automatic BP measurement device', 'type' => 'Equipment', 'unit' => 'piece'],
            ['name' => 'Stethoscope', 'description' => 'Cardiac and respiratory examination tool', 'type' => 'Equipment', 'unit' => 'piece'],
            ['name' => 'Pulse Oximeter', 'description' => 'Oxygen saturation measurement device', 'type' => 'Equipment', 'unit' => 'piece'],
            ['name' => 'Weighing Scale', 'description' => 'Digital body weight measurement', 'type' => 'Equipment', 'unit' => 'piece'],
            ['name' => 'Otoscope', 'description' => 'Ear examination instrument', 'type' => 'Equipment', 'unit' => 'piece'],
        ];

        foreach ($medicines as $medicine) {
            Medicine::create($medicine);
        }
    }
}
