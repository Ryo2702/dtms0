<?php

namespace App\Http\Controllers\Notification;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\DocumentReview;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    /**
     * Get notification counts for the authenticated user
     */
    public function getCounts(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user || !in_array($user->type, ['Staff', 'Head'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $counts = [
            'pending' => 0,
            'received' => 0,
            'sent' => 0,
            'completed' => 0,
            'overdue_completed' => 0,
            'rejected' => 0,
            'canceled' => 0,
        ];

        try {
            // Pending reviews assigned to current user
            $counts['pending'] = DocumentReview::where('assigned_to', $user->id)
                ->where('status', 'pending')
                ->count();

            // Received documents (documents from other departments to current department)
            $receivedQuery = DocumentReview::where('current_department_id', $user->department_id)
                ->where('original_department_id', '!=', $user->department_id)
                ->where('status', 'pending');

            if ($user->type === 'Head') {
                $receivedQuery->where(function ($q) use ($user) {
                    $q->whereNull('assigned_to')->orWhere('assigned_to', $user->id);
                });
            } else {
                $receivedQuery->where('assigned_to', $user->id);
            }
            $counts['received'] = $receivedQuery->count();

            // Sent documents
            $sentQuery = DocumentReview::where('original_department_id', $user->department_id)
                ->where('current_department_id', '!=', $user->department_id)
                ->whereIn('status', ['pending', 'approved']);

            if ($user->type === 'Head') {
                $sentQuery->where(function ($q) use ($user) {
                    $q->where('created_by', $user->id)->orWhereExists(function ($subQ) use ($user) {
                        $subQ->select(DB::raw(1))
                            ->from('users')
                            ->whereRaw('users.id = document_reviews.created_by')
                            ->where('users.department_id', $user->department_id);
                    });
                });
            } else {
                $sentQuery->where('created_by', $user->id);
            }
            $counts['sent'] = $sentQuery->count();

            // Completed documents
            $completedQuery = DocumentReview::where('status', 'approved')
                ->whereNotNull('downloaded_at');

            if ($user->type === 'Head') {
                $completedQuery->where(function ($q) use ($user) {
                    $q->where('created_by', $user->id)
                        ->orWhere('assigned_to', $user->id)
                        ->orWhere('current_department_id', $user->department_id)
                        ->orWhere('original_department_id', $user->department_id);
                });
            } else {
                $completedQuery->where(function ($q) use ($user) {
                    $q->where('created_by', $user->id)->orWhere('assigned_to', $user->id);
                });
            }
            $counts['completed'] = $completedQuery->count();

            // Count overdue completed documents
            $overdueCompletedQuery = clone $completedQuery;
            $counts['overdue_completed'] = $overdueCompletedQuery
                ->whereNotNull('due_at')
                ->whereColumn('downloaded_at', '>', 'due_at')
                ->count();

            // Rejected documents
            $rejectedQuery = DocumentReview::where('status', 'rejected');
            if ($user->type === 'Head') {
                $rejectedQuery->where(function ($q) use ($user) {
                    $q->where('created_by', $user->id)
                        ->orWhere('assigned_to', $user->id)
                        ->orWhere('current_department_id', $user->department_id)
                        ->orWhere('original_department_id', $user->department_id);
                });
            } else {
                $rejectedQuery->where(function ($q) use ($user) {
                    $q->where('created_by', $user->id)->orWhere('assigned_to', $user->id);
                });
            }
            $counts['rejected'] = $rejectedQuery->count();

            // Canceled documents
            $canceledQuery = DocumentReview::where('status', 'canceled');
            if ($user->type === 'Head') {
                $canceledQuery->where(function ($q) use ($user) {
                    $q->where('created_by', $user->id)
                        ->orWhere('assigned_to', $user->id)
                        ->orWhere('current_department_id', $user->department_id)
                        ->orWhere('original_department_id', $user->department_id);
                });
            } else {
                $canceledQuery->where(function ($q) use ($user) {
                    $q->where('created_by', $user->id)->orWhere('assigned_to', $user->id);
                });
            }
            $counts['canceled'] = $canceledQuery->count();

            return response()->json([
                'success' => true,
                'counts' => $counts,
                'timestamp' => now()->toISOString()
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching notification counts: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error fetching notification counts',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Mark notifications as read (optional method for future use)
     */
    public function markAsRead(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        try {
            $type = $request->input('type');
            $updated = false;

            return response()->json([
                'success' => true,
                'message' => 'Notifications marked as read',
                'type' => $type
            ]);

        } catch (\Exception $e) {
            Log::error('Error marking notifications as read: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error marking notifications as read'
            ], 500);
        }
    }
}
