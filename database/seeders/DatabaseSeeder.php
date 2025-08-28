<?php

namespace Database\Seeders;

use App\Models\Department;
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
        // Call the RoleSeeder first
        $this->call(RoleSeeder::class);

        // Ensure at least one department exists
        $department = Department::firstOrCreate(
            ['code' => 'ADM'],
            [
                'name' => 'Administration',
                'description' => 'Default department for Admin users',
                'logo' => null,
                'color' => '#000000',
                'status' => true,
            ]
        );

        // Create Admin user if not exists
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name'          => 'System Admin',
                'password'      => Hash::make('password'), // ⚠️ Change after seeding
                'department_id' => $department->id,
                'type'          => 'Admin',
                'status'        => true,
            ]
        );

        // ✅ Assign Admin role
        $admin->assignRole('Admin');
    }
}
