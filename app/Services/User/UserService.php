<?php
namespace App\Services\User;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;


class UserService{
    public static function getNonAdminUsers(Builder $query)  {
        return $query->where('type', '!=', 'Admin');
    }

    public static function applyUserFilters(Builder $query, array $options = [])  {
        //Default
        if (!isset($options['inlcude_admins']) || !$options['include_admins'] ) {
            $query = self::getNonAdminUsers($query);
        }

        if (isset($options['only_active']) && $options['only_active']) {
            $query->where('status', 1);
        }

        if (isset($options['types']) && is_array($options['types'])) {
            $query->whereIn('type', $options['types']);
        }

        return $query;
    }

    public static function getFilteredUsers(array $options = []){
        $query = User::query();

        return self::applyUserFilters($query, $options);
    }
}