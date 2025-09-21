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
            'THS',
            'SHS', 
            'Laboratory'
        ];

        foreach ($campuses as $index => $campus) {
            $campusCode = $this->getCampusCode($campus);
            
            // Main Campus gets admin role, others get nurse role
            if ($campus === 'Main Campus') {
                // Create Main Campus Administrator
                User::updateOrCreate(
                    ['email' => 'admin.main@uzicare.com'],
                    [
                        'name' => 'Main Campus Administrator',
                        'employee_id' => 'MAIN-ADM-001',
                        'role' => 'admin',
                        'department' => 'Administration',
                        'campus' => $campus,
                        'contact_number' => '+1-555-1000',
                        'password' => Hash::make('Admin123!'),
                        'is_active' => true,
                        'created_by' => 'system',
                        'email_verified_at' => now(),
                    ]
                );
            } else {
                // Create Nurse for other campuses
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
            }
        }

        $this->command->info('✅ Created staff members for all campuses');
        $this->command->info('');
        $this->command->info('📋 Staff Credentials:');
        $this->command->info('==========================================');
        
        foreach ($campuses as $campus) {
            $campusCode = $this->getCampusCode($campus);
            $this->command->info('🏥 ' . $campus . ':');
            
            if ($campus === 'Main Campus') {
                $this->command->info('   👑 Administrator:');
                $this->command->info('      Email: admin.main@uzicare.com');
                $this->command->info('      Password: Admin123!');
            } else {
                $this->command->info('   👩‍⚕️ Head Nurse:');
                $this->command->info('      Email: ' . strtolower($campusCode) . '.nurse@uzicare.com');
                $this->command->info('      Password: Nurse123!');
            }
            $this->command->info('');
        }
        
        $this->command->info('⚠️  Please change these default passwords after first login!');
    }

    private function getCampusCode($campus)
    {
        $codes = [
            'Main Campus' => 'MAIN',
            'THS' => 'THS',
            'SHS' => 'SHS',
            'Laboratory' => 'LAB'
        ];

        return $codes[$campus] ?? 'XX';
    }
}
