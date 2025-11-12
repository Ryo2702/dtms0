<?php

namespace App\Services\User;

use App\Models\Department;
use App\Models\User;

class UserService
{
    /**
     * Generate employee ID in format: DEPTCODE+TYPE-YEAR-NUMBER
     * Example: ADMA-2025-000
     *
     * @param Department|null $department
     * @param string $type
     * @return string
     */

    public function generate(?Department $department, string $type)
    {
        $deptCode = $department ? $department->code : 'UKN';

        // Get type initial (first letter of type)
        $typeInitial = strtoupper(substr($type, 0, 1));

        $year = now()->year;

        $query = User::where('type', $type);

        if ($department) {
            $query->where('department_id', $department->id);
        } else {
            $query->whereNull('department_id');
        }

        $lastUser = User::orderBy('id', 'desc')->first();

        if ($lastUser) {
            $parsed = $this->parse($lastUser->employee_id);
            $nextNumber = $parsed ? intval($parsed['sequence']) + 1 : 1;
        }else{
            $nextNumber = 1;
        }

        $paddedNumber = str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        return "{$deptCode}{$typeInitial}-{$year}-{$paddedNumber}";
    }
    /**
     * Validate employee ID format
     *
     * @param string $employeeId
     * @return bool
     */

    public function validate(string $employeeId)
    {
        // Format: CODE+TYPE-YEAR-NUMBER (e.g., ADMA-2025-000)
        return preg_match('/^[A-Z]{2,5}[A-Z]-\d{4}-\d{3}$/', $employeeId) === 1;
    }
    /**
     * Parse employee ID into components
     *
     * @param string $employeeId
     * @return array|null
     */

    public function parse(string $employeeId)
    {
        if (!$this->validate($employeeId)) {
            return null;
        }

        preg_match('/^([A-Z]{2,5})([A-Z])-(\d{4})-(\d{3})$/', $employeeId, $matches);

        if (count($matches) !== 5) {
            return null;
        }


        return[
            'department_code' => $matches[1],
            'type_initial' => $matches[2],
            'year' => $matches[3],
            'sequence' => $matches[4]
        ];
    }
}
