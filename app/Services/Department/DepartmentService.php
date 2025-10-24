<?php


namespace App\Services\Department;

use App\Models\Department;
use App\Services\User\UserService;
use Illuminate\Database\Eloquent\Builder;


class DepartmentService {
    public static function withNonAdminHeads(Builder $query) {
        return $query->whereHas('head', function ($headQuery) {
            UserService::applyUserFilters($headQuery, ['include_admin' => false]);
        });
    }

    public static function getDepartmentStats(Department $department)  {
        return [
            'total_users' => UserService::applyUserFilters(
                $department->users(), ['include_admin' => false]
            )->count(),
            'active_users' => UserService::applyUserFilters(
                $department->users()->where('status', 1),
                ['include_admin' => false]
            )->count(),
            'has_head' => $department->hasHead(),
            'has_non_admin_head' => $department->head && $department->head->type !== 'Admin'
        ];
    }


    public static function getFilteredDepartments(array $options = [])  {
        $query = Department::query();

        if (isset($options['with_relations'])) {
            $query->with($options['with_relations']);
        }

        if (isset($options['only_active']) && $options['only_active']) {
            $query->active();
        }

        if (isset($options['exclude_admin_heads']) && $options['exclude_admin_heads']) {
            $query = self::withNonAdminHeads($query);
        }

        return $query;
    }
}