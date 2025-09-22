<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Medicine;

class UpdateMedicineCategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Update all items with supply-related types to be categorized as 'Supply'
        $supplyTypes = ['Supply', 'Medical Supply', 'Equipment'];
        
        Medicine::whereIn('type', $supplyTypes)->update(['category' => 'Supply']);
        
        // Ensure all other items are categorized as 'Medicine'
        Medicine::whereNotIn('type', $supplyTypes)->update(['category' => 'Medicine']);
        
        $this->command->info('Updated medicine categories:');
        $this->command->info('- Medicines: ' . Medicine::where('category', 'Medicine')->count());
        $this->command->info('- Supplies: ' . Medicine::where('category', 'Supply')->count());
    }
}
