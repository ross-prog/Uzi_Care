<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AdminUsersSeeder::class,
            CampusStaffSeeder::class,
            MedicineSeeder::class,
            InventorySeeder::class,
            MedicineInventorySeeder::class,
            MedicineDistributionSeeder::class, // Add distributions after inventory is created
            PatientConsultationSeeder::class,
        ]);
    }
}
