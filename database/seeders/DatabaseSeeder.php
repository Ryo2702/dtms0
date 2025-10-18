<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // // Call the RoleSeeder first
        $this->call(RoleSeeder::class);

        // Ensure at least one department exists
        $department = Department::firstOrCreate(
            ['code' => 'ADM'],
            [
                'name' => 'System Administrator',
                'description' => 'Default department for Admin users',
                'logo' => null,
                'status' => true,
            ]
        );

        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'System Admin',
                'password' => Hash::make('password'),
                'department_id' => $department->id,
                'type' => 'Admin',
                'status' => true,
            ]
        );
        $admin->assignRole('Admin');

        // // Mayor's Office
        // $mayorDepartment = Department::firstOrCreate(
        //     ['code' => 'MO'],
        //     [
        //         'name' => "Mayor's Office",
        //         'description' => 'Office of the Mayor',
        //         'logo' => null,
        //         'status' => true,
        //     ]
        // );

        // $mayorHead = User::firstOrCreate(
        //     ['email' => 'mayor@example.com'],
        //     [
        //         'name' => 'Mayor Department Head',
        //         'password' => Hash::make('password'),
        //         'department_id' => $mayorDepartment->id,
        //         'type' => 'Department Head',
        //         'status' => true,
        //     ]
        // );
        // $mayorHead->assignRole('Department Head');

        // // General Services Office
        // $gsDepartment = Department::firstOrCreate(
        //     ['code' => 'GSO'],
        //     [
        //         'name' => 'General Services Office',
        //         'description' => 'General Services Office',
        //         'logo' => null,
        //         'status' => true,
        //     ]
        // );

        // $gsHead = User::firstOrCreate(
        //     ['email' => 'gso@example.com'],
        //     [
        //         'name' => 'GSO Department Head',
        //         'password' => Hash::make('password'),
        //         'department_id' => $gsDepartment->id,
        //         'type' => 'Department Head',
        //         'status' => true,
        //     ]
        // );
        // $gsHead->assignRole('Department Head');

        // // Treasurer Office
        // $treasurerDepartment = Department::firstOrCreate(
        //     ['code' => 'TO'],
        //     [
        //         'name' => 'Treasurer Office',
        //         'description' => 'Treasurer Office',
        //         'logo' => null,
        //         'status' => true,
        //     ]
        // );

        // $treasurerHead = User::firstOrCreate(
        //     ['email' => 'treasurer@example.com'],
        //     [
        //         'name' => 'Treasurer Department Head',
        //         'password' => Hash::make('password'),
        //         'department_id' => $treasurerDepartment->id,
        //         'type' => 'Department Head',
        //         'status' => true,
        //     ]
        // );
        // $treasurerHead->assignRole('Department Head');

    }
}
