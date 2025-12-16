<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\User;
use App\Services\User\UserService;
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
        $employeeIdGenerate = app(UserService::class);
        $adminEmployeeId = $employeeIdGenerate->generate($department, 'Admin');


        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'System Admin',
                'employee_id' => $adminEmployeeId,
                'password' => Hash::make('password'),
                'department_id' => $department->id,
                'type' => 'Admin',
                'status' => true,
            ]
        );
        $admin->assignRole('Admin');

      

        $this->call(TransactionTypeSeeder::class);
        $this->call(DepartmentSeeder::class);
    }
}
