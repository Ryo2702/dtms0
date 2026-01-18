<?php

namespace App\Http\Controllers\Notification;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    /**
     * Get notifications list for the authenticated user
     */

      public function getCounts(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        try {
            $unreadCount = Notification::where('user_id', $user->id)
                ->where('is_read', false)
                ->count();

            $unreadCounts = Notification::where('user_id', $user->id)
                ->where('is_read', false)
                ->selectRaw('type, count(*) as count')
                ->groupBy('type')
                ->pluck('count', 'type')
                ->toArray();

            return response()->json([
                'success' => true,
                'unread_count' => $unreadCount,
                'unread_counts' => $unreadCounts,
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching notification counts: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error fetching counts'], 500);
        }
    }

    public function getNotifications(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        try {
            $query = Notification::where('user_id', $user->id);
            
            // Filter by transaction status if provided
            $status = $request->query('status');
            if ($status) {
                $query->where('transaction_status', $status);
            }
            
            $notifications = $query
                ->with('document')
                ->orderBy('created_at', 'desc')
                ->take(50)
                ->get()
                ->map(function ($notification) {
                    return [
                        'id' => $notification->id,
                        'document_id' => $notification->document_id,
                        'transaction_status' => $notification->transaction_status,
                        'type' => $notification->type,
                        'title' => $notification->title,
                        'message' => $notification->message,
                        'is_read' => $notification->is_read,
                        'created_at' => $notification->created_at->toISOString(),
                        'read_at' => $notification->read_at?->toISOString(),
                    ];
                });

            $unreadCount = Notification::where('user_id', $user->id)
                ->where('is_read', false)
                ->count();

            //unread counts by status
            $unreadCounts = Notification::where('user_id', $user->id)
                ->where('is_read', false)
                ->selectRaw('type, count(*) as count')
                ->groupBy('type')
                ->pluck('count', 'type')
                ->toArray();
            
            // Unread counts by transaction status
            $unreadByStatus = Notification::where('user_id', $user->id)
                ->where('is_read', false)
                ->selectRaw('transaction_status, count(*) as count')
                ->groupBy('transaction_status')
                ->pluck('count', 'transaction_status')
                ->toArray();

            return response()->json([
                'success' => true,
                'notifications' => $notifications,
                'unread_count' => $unreadCount,
                'unread_counts' => $unreadCounts,
                'unread_by_status' => $unreadByStatus
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching notifications: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error fetching notifications'], 500);
        }
    }

    /**
     * Mark a specific notification as read
     */
    public function markAsRead(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        try {
            $notificationId = $request->input('notification_id');

            $notification = Notification::where('id', $notificationId)
                ->where('user_id', $user->id)
                ->first();

            if (!$notification) {
                return response()->json(['success' => false, 'message' => 'Notification not found'], 404);
            }

            $notification->update([
                'is_read' => true,
                'read_at' => now()
            ]);

            return response()->json(['success' => true, 'message' => 'Notification marked as read']);

        } catch (\Exception $e) {
            Log::error('Error marking notification as read: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error updating notification'], 500);
        }
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        try {
            Notification::where('user_id', $user->id)
                ->where('is_read', false)
                ->update([
                    'is_read' => true,
                    'read_at' => now()
                ]);

            return response()->json(['success' => true, 'message' => 'All notifications marked as read']);

        } catch (\Exception $e) {
            Log::error('Error marking all notifications as read: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error updating notifications'], 500);
        }
    }
}
