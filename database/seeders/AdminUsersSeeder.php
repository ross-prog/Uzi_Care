<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define campuses with their admin details
        $campusAdmins = [
            [
                'campus' => 'Main Campus',
                'name' => 'Main Campus Administrator',
                'email' => 'admin.main@uzicare.com',
                'employee_id' => 'MC-ADM-001',
                'department' => 'Administration'
            ],
            [
                'campus' => 'North Campus',
                'name' => 'North Campus Administrator',
                'email' => 'admin.north@uzicare.com',
                'employee_id' => 'NC-ADM-001',
                'department' => 'Administration'
            ],
            [
                'campus' => 'South Campus',
                'name' => 'South Campus Administrator',
                'email' => 'admin.south@uzicare.com',
                'employee_id' => 'SC-ADM-001',
                'department' => 'Administration'
            ],
            [
                'campus' => 'East Campus',
                'name' => 'East Campus Administrator',
                'email' => 'admin.east@uzicare.com',
                'employee_id' => 'EC-ADM-001',
                'department' => 'Administration'
            ],
            [
                'campus' => 'West Campus',
                'name' => 'West Campus Administrator',
                'email' => 'admin.west@uzicare.com',
                'employee_id' => 'WC-ADM-001',
                'department' => 'Administration'
            ],
            [
                'campus' => 'Downtown Campus',
                'name' => 'Downtown Campus Administrator',
                'email' => 'admin.downtown@uzicare.com',
                'employee_id' => 'DC-ADM-001',
                'department' => 'Administration'
            ],
            [
                'campus' => 'Satellite Clinic A',
                'name' => 'Satellite Clinic A Administrator',
                'email' => 'admin.clinica@uzicare.com',
                'employee_id' => 'CA-ADM-001',
                'department' => 'Administration'
            ],
            [
                'campus' => 'Satellite Clinic B',
                'name' => 'Satellite Clinic B Administrator',
                'email' => 'admin.clinicb@uzicare.com',
                'employee_id' => 'CB-ADM-001',
                'department' => 'Administration'
            ]
        ];

        // Create or update admin users for each campus
        foreach ($campusAdmins as $adminData) {
            User::updateOrCreate(
                ['email' => $adminData['email']], // Find by email
                [
                    'name' => $adminData['name'],
                    'employee_id' => $adminData['employee_id'],
                    'role' => 'admin',
                    'department' => $adminData['department'],
                    'campus' => $adminData['campus'],
                    'contact_number' => '+1-555-0100',
                    'password' => Hash::make('Admin123!'),
                    'is_active' => true,
                    'created_by' => 'system',
                    'email_verified_at' => now(),
                ]
            );
        }

        // Also create a super admin that can manage all campuses
        User::updateOrCreate(
            ['email' => 'superadmin@uzicare.com'],
            [
                'name' => 'Super Administrator',
                'employee_id' => 'SA-001',
                'role' => 'admin',
                'department' => 'System Administration',
                'campus' => 'Main Campus',
                'contact_number' => '+1-555-0001',
                'password' => Hash::make('SuperAdmin123!'),
                'is_active' => true,
                'created_by' => 'system',
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('✅ Created admin users for all campuses');
        $this->command->info('');
        $this->command->info('📋 Admin Credentials:');
        $this->command->info('==========================================');
        $this->command->info('🔐 Super Admin (All Campuses):');
        $this->command->info('   Email: superadmin@uzicare.com');
        $this->command->info('   Password: SuperAdmin123!');
        $this->command->info('');
        
        foreach ($campusAdmins as $admin) {
            $this->command->info('🔐 ' . $admin['campus'] . ':');
            $this->command->info('   Email: ' . $admin['email']);
            $this->command->info('   Password: Admin123!');
            $this->command->info('');
        }
        
        $this->command->info('⚠️  Please change these default passwords after first login!');
    }
}
