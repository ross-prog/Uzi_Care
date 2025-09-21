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
            MedicineInventorySeeder::class, // This handles all medicine and inventory creation per campus
            MedicineDistributionSeeder::class, // Add distributions after inventory is created
            PatientConsultationSeeder::class,
        ]);
    }
}
