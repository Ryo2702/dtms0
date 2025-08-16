<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        // Create roles only if they don't exist
        $adminRole   = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $staffRole   = Role::firstOrCreate(['name' => 'Staff', 'guard_name' => 'web']);
        $officerRole = Role::firstOrCreate(['name' => 'Officer', 'guard_name' => 'web']);
        // Admin account
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'], // unique check
            [
                'municipal_id' => 'A-0001',
                'name' => 'System Admin',
                'department' => 'Administrator',
                'password' => Hash::make('password'),
            ]
        );

        // Assign role to admin
        if (! $admin->hasRole('Admin')) {
            $admin->assignRole($adminRole);
        }

        $staff = User::firstOrCreate(
            ['email' => 'staff@example.com'], // unique check
            [
                'municipal_id' => 'S-0001',
                'name' => 'Staff01',
                'department' => 'Mayor\'s Office',
                'password' => Hash::make('password'),
            ]
        );

        // Assign role to admin
        if (! $staff->hasRole('Staff')) {
            $staff->assignRole($staffRole);
        }


        $officer = User::firstOrCreate(
            ['email' => 'officer@example.com'], // unique check
            [
                'municipal_id' => 'OF-0001',
                'name' => 'Office001',
                'department' => 'Mayor\'s Office',
                'password' => Hash::make('password'),
            ]
        );

        // Assign role to admin
        if (! $officer->hasRole('Officer')) {
            $officer->assignRole($officerRole);
        }
    }
}
