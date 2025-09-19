<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CampusStaffSeeder extends Seeder
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

        foreach ($campuses as $index => $campus) {
            $campusCode = $this->getCampusCode($campus);
            
            // Create Inventory Manager for each campus
            User::updateOrCreate(
                ['email' => strtolower($campusCode) . '.inventory@uzicare.com'],
                [
                    'name' => $campus . ' Inventory Manager',
                    'employee_id' => $campusCode . '-INV-001',
                    'role' => 'inventory_manager',
                    'department' => 'Supply Management',
                    'campus' => $campus,
                    'contact_number' => '+1-555-' . sprintf('%04d', 200 + $index),
                    'password' => Hash::make('Inventory123!'),
                    'is_active' => true,
                    'created_by' => 'system',
                    'email_verified_at' => now(),
                ]
            );

            // Create Head Nurse for each campus
            User::updateOrCreate(
                ['email' => strtolower($campusCode) . '.nurse@uzicare.com'],
                [
                    'name' => $campus . ' Head Nurse',
                    'employee_id' => $campusCode . '-NUR-001',
                    'role' => 'nurse',
                    'department' => 'Medical',
                    'campus' => $campus,
                    'contact_number' => '+1-555-' . sprintf('%04d', 300 + $index),
                    'password' => Hash::make('Nurse123!'),
                    'is_active' => true,
                    'created_by' => 'system',
                    'email_verified_at' => now(),
                ]
            );

            // Create Account Manager for main campuses only
            if (in_array($campus, ['Main Campus', 'North Campus', 'South Campus'])) {
                User::updateOrCreate(
                    ['email' => strtolower($campusCode) . '.accounts@uzicare.com'],
                    [
                        'name' => $campus . ' Account Manager',
                        'employee_id' => $campusCode . '-ACC-001',
                        'role' => 'account_manager',
                        'department' => 'Administration',
                        'campus' => $campus,
                        'contact_number' => '+1-555-' . sprintf('%04d', 400 + $index),
                        'password' => Hash::make('Accounts123!'),
                        'is_active' => true,
                        'created_by' => 'system',
                        'email_verified_at' => now(),
                    ]
                );
            }
        }

        $this->command->info('✅ Created staff members for all campuses');
        $this->command->info('');
        $this->command->info('📋 Staff Credentials:');
        $this->command->info('==========================================');
        
        foreach ($campuses as $campus) {
            $campusCode = $this->getCampusCode($campus);
            $this->command->info('🏥 ' . $campus . ':');
            $this->command->info('   📦 Inventory Manager:');
            $this->command->info('      Email: ' . strtolower($campusCode) . '.inventory@uzicare.com');
            $this->command->info('      Password: Inventory123!');
            $this->command->info('   👩‍⚕️ Head Nurse:');
            $this->command->info('      Email: ' . strtolower($campusCode) . '.nurse@uzicare.com');
            $this->command->info('      Password: Nurse123!');
            
            if (in_array($campus, ['Main Campus', 'North Campus', 'South Campus'])) {
                $this->command->info('   👔 Account Manager:');
                $this->command->info('      Email: ' . strtolower($campusCode) . '.accounts@uzicare.com');
                $this->command->info('      Password: Accounts123!');
            }
            $this->command->info('');
        }
        
        $this->command->info('⚠️  Please change these default passwords after first login!');
    }

    private function getCampusCode($campus)
    {
        $codes = [
            'Main Campus' => 'MC',
            'North Campus' => 'NC',
            'South Campus' => 'SC',
            'East Campus' => 'EC',
            'West Campus' => 'WC',
            'Downtown Campus' => 'DC',
            'Satellite Clinic A' => 'CA',
            'Satellite Clinic B' => 'CB'
        ];

        return $codes[$campus] ?? 'XX';
    }
}
