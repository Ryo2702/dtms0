<?php

namespace App\Services\Track;

use App\Models\AuditLog;
use App\Models\Department;
use App\Models\DocumentReview;
use App\Services\User\UserService;
use Illuminate\Database\Eloquent\Builder;

class TrackServices{
    public static function withoutAdminHeads(Builder $query) {
        return $query->whereHas('head', function ($headQuery){
            UserService::applyUserFilters($headQuery, ['include_admin' => false]);
        });
    }

    public static function getDepartmentWithDocumentCounts(array $options  = []) {
        $query = Department::withCount([
            'documentReviews as total_created',
            'documentReviews as pending_count' => function ($query)  {
                $query->where('status', 'pending');
            },
            'documentReviews as completed_count' => function ($query) {
                $query->where('status', 'approved')->whereNotNull('downloaded_at');
            },
            'documentReviews as rejected_count' => function ($query) {
                $query->where('status', 'rejected');
            },
            'documentReviews as canceled_count' => function ($query) {
                $query->where('status', 'canceled');
            },
            'documentReviews as approved_count' => function ($query) {
                $query->where('status', 'approved')->whereNull('downloaded_at');
            }
        ]);


        if (isset($options['exclude_admin_heads']) && $options['exclude_admin_heads']) {
            $query = self::withoutAdminHeads($query);
        }
        

        if (isset($options['sort_by'])) {
            $sortField = $options['sort_by'];
            $sortDirection = $options['sort_direction'] ?? 'asc';


            switch ($sortField) {
                case 'department':
                    $query->orderBy('name', $sortDirection);
                    break;
                case 'total_created':
                case 'pending_count':
                case 'completed_count':
                case 'rejected_count':
                case 'canceled_count':
                    $query->orderBy($sortField,$sortDirection);
                    break;
                default:
                    $query->orderBy('name', 'asc');
            }
        }

        return $query;
    }

    public static function excludeAdminDocuments(Builder $query) {
        return $query->whereHas('department.head', function($headQuery)  {
            UserService::applyUserFilters($headQuery, ['include_admin' => false]);
        });
    }
    public static function getDocumentStatistics(array $options = []) {
      $baseQuery = DocumentReview::query();
      
      if (isset($options['exclude_admin_docs']) && $options['exlude_admin_docs']) {
        $baseQuery = self::excludeAdminDocuments($baseQuery);
      }

      return [
        'total_documents' => (clone $baseQuery)->count(),
        'pending_documents' => (clone $baseQuery)->where('status', 'pending')   ->count(),
        'completed_documents' => (clone $baseQuery)
            ->where('status', 'approved')
            ->whereNotNull('downloaded_at')
            ->count(),
        'overdue_documents' => (clone $baseQuery)
            ->where('status', 'pending')
            ->where('due_at', '<', now())
            ->count(),
        'avg_processing_time' => (clone $baseQuery)
            ->where('status', 'approved')
            ->whereNotNull('downloaded_at')
            ->whereNotNull('submitted_at')
            ->get()
            ->avg(function ($doc) {
                return $doc->submitted_at->diffInMinutes($doc->downloaded_at);
            })
      ];
    }

    public static function getTrackStats(AuditLog $log)  {
        return [
            'total_users' => UserService::applyUserFilters(
                $log->users(), ['include_admin' => false]
            )->count(),
            'active_users' => UserService::applyUserFilters(
                $log->users()->where('status', 1),
                ['include_admin' => false]
            )->count(),
            'has_head' => $log->hasHead(),
            'has_non_admin_head' => $log->head && $log->head->type !== 'Admin',
        ];
    }

    public static function getFilteredLogs(array $options = [])  {
        $query = AuditLog::query();

        if (isset($options['with_relations'])) {
            $query->with($options['with_relations']);
        }

        if (isset($options['only_active']) && $options['only_active']) {
            $query->active();
        }

        if (isset($options['exclude_admin_heads']) && $options['exclude_admin_heads']) {
            $query = self::withoutAdminHeads($query);
        }

        if (isset($options['search']) && $options['search']) {
            $query->where('name', 'like', "%{$options['search']}%");
        }

        if (isset($options['sort_by']) && isset($options['sort_direction'])) {
            $query->orderBy($options['sort_by'], $options['sort_direction']);
        }
        

        return $query;
    }
}