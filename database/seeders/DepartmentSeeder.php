<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('departments')->insert([
            [
                'name' => 'Tourism Office',
                'code' => 'TOUR',
                'description' => 'Tourism development and promotion',
            ],
            [
                'name' => 'Civil Security',
                'code' => 'CIVSEC',
                'description' => 'Public safety and security services',
            ],
            [
                'name' => 'Office of the Municipal Accounting',
                'code' => 'ACCT',
                'description' => 'Financial accounting and reporting',
            ],
            [
                'name' => 'Office of the Municipal Agriculture',
                'code' => 'AGRI',
                'description' => 'Agricultural programs and services',
            ],
            [
                'name' => 'Office of the Municipal Mayor',
                'code' => 'MAYOR',
                'description' => 'Executive leadership and administration',
            ],
            [
                'name' => 'Budget and Management Office',
                'code' => 'BUDGET',
                'description' => 'Budget preparation and fund control',
            ],
            [
                'name' => 'Office of the Municipal Civil Engineering',
                'code' => 'ENG',
                'description' => 'Engineering and infrastructure projects',
            ],
            [
                'name' => 'Office of Health and Welfare',
                'code' => 'HEALTH',
                'description' => 'Health and social welfare services',
            ],
            [
                'name' => 'Human Resources Management Office',
                'code' => 'HRMO',
                'description' => 'Personnel and human resource management',
            ],
            [
                'name' => 'Office of the Municipal Treasurer',
                'code' => 'TREAS',
                'description' => 'Revenue collection and cash management',
            ],
            [
                'name' => 'Public Employment Service Office',
                'code' => 'PESO',
                'description' => 'Employment facilitation and labor programs',
            ],
            [
                'name' => 'Municipal Planning and Development Office',
                'code' => 'MPDO',
                'description' => 'Planning and development coordination',
            ],
            [
                'name' => 'Territorial Integrity Sanggui',
                'code' => 'SANG',
                'description' => 'Legislative and territorial functions',
            ],
            [
                'name' => 'Provincial Welfare and Development',
                'code' => 'PWD',
                'description' => 'Provincial social welfare programs',
            ],
            [
                'name' => 'Information Office Municipal',
                'code' => 'INFO',
                'description' => 'Public information and communications',
            ],
            [
                'name' => 'Banasud Office',
                'code' => 'BAN',
                'description' => 'Local administrative office',
            ],
            [
                'name' => 'General Services Office',
                'code' => 'GSO',
                'description' => 'Procurement, property, and general services',
            ],
            [
                'name' => 'Community Development Office',
                'code' => 'CDO',
                'description' => 'Community programs and development',
            ],
            [
                'name' => 'Legal and Compliance Office',
                'code' => 'LEGAL',
                'description' => 'Legal services and compliance monitoring',
            ],
        ]);
    }
}
