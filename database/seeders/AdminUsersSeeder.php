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
        // Create a system super admin that can manage users (outside campus structure)
        User::updateOrCreate(
            ['email' => 'superadmin@uzicare.com'],
            [
                'name' => 'System Administrator',
                'employee_id' => 'SYS-ADM-001',
                'role' => 'super_admin',
                'department' => 'System Administration',
                'campus' => 'System',
                'contact_number' => '+1-555-0001',
                'password' => Hash::make('SuperAdmin123!'),
                'is_active' => true,
                'created_by' => 'system',
                'email_verified_at' => now(),
            ]
        );

        // Create Main Campus admin (has head admin privileges but cannot manage users)
        User::updateOrCreate(
            ['email' => 'admin.main@uzicare.com'],
            [
                'name' => 'Main Campus Head Administrator',
                'employee_id' => 'MAIN-ADM-001',
                'role' => 'admin',
                'department' => 'Administration',
                'campus' => 'Main Campus',
                'contact_number' => '+1-555-1000',
                'password' => Hash::make('Admin123!'),
                'is_active' => true,
                'created_by' => 'system',
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('✅ Created system admin and Main Campus admin');
        $this->command->info('');
        $this->command->info('📋 Admin Credentials:');
        $this->command->info('==========================================');
        $this->command->info('🔐 System Administrator (User Management):');
        $this->command->info('   Email: superadmin@uzicare.com');
        $this->command->info('   Password: SuperAdmin123!');
        $this->command->info('   Role: Can manage all users and system');
        $this->command->info('');
        $this->command->info('🔐 Main Campus Head Administrator:');
        $this->command->info('   Email: admin.main@uzicare.com');
        $this->command->info('   Password: Admin123!');
        $this->command->info('   Role: Full access except user management');
        $this->command->info('');
        $this->command->info('⚠️  Please change these default passwords after first login!');
    }
}
