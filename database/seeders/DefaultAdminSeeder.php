<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DefaultAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default admin user if it doesn't exist
        User::firstOrCreate(
            ['email' => 'admin@uzicare.com'],
            [
                'name' => 'System Administrator',
                'employee_id' => 'ADM001',
                'role' => 'admin',
                'department' => 'Administration',
                'contact_number' => '+63-XXX-XXXX',
                'password' => Hash::make('admin123'),
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // Create sample users for different roles
        User::firstOrCreate(
            ['email' => 'nurse@uzicare.com'],
            [
                'name' => 'Jane Nurse',
                'employee_id' => 'NUR001',
                'role' => 'nurse',
                'department' => 'Medical',
                'contact_number' => '+63-XXX-XXXX',
                'password' => Hash::make('nurse123'),
                'is_active' => true,
                'created_by' => 'admin@uzicare.com',
                'email_verified_at' => now(),
            ]
        );

        User::firstOrCreate(
            ['email' => 'inventory@uzicare.com'],
            [
                'name' => 'John Inventory',
                'employee_id' => 'INV001',
                'role' => 'inventory_manager',
                'department' => 'Supply Management',
                'contact_number' => '+63-XXX-XXXX',
                'password' => Hash::make('inventory123'),
                'is_active' => true,
                'created_by' => 'admin@uzicare.com',
                'email_verified_at' => now(),
            ]
        );

        User::firstOrCreate(
            ['email' => 'accounts@uzicare.com'],
            [
                'name' => 'Mary Accounts',
                'employee_id' => 'ACC001',
                'role' => 'account_manager',
                'department' => 'Human Resources',
                'contact_number' => '+63-XXX-XXXX',
                'password' => Hash::make('accounts123'),
                'is_active' => true,
                'created_by' => 'admin@uzicare.com',
                'email_verified_at' => now(),
            ]
        );
    }
}
