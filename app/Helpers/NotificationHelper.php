<?php

namespace App\Helpers;

use App\Models\Notification;
use App\Models\User;
use App\Models\Transaction;

class NotificationHelper{
    
    /**
     * Send notification to a single user
     */
    public static function send(User $user, string $type, string $title, string $message, ?int $documentId, ?array $data = null, ?string $transactionStatus = null) {
        return Notification::create([
            'user_id' => $user->id,
            'document_id' => $documentId,
            'transaction_status' => $transactionStatus,
            'title' => $title,
            'type' => $type,
            'message' => $message,
            'data' => $data
        ]);
    }

    /**
     * Send notification to multiple users
     */
    public static function sendtoMultiple(array $userIds, string $type, string $message, string $title, ?int $documentId, ?array $data = null, ?string $transactionStatus = null ){

        $notifications = [];
        foreach ($userIds as $userId) {
            $notifications[] = [
                'user_id' => $userId,
                'document_id' => $documentId,
                'transaction_status' => $transactionStatus,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'data' => $data,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        Notification::insert($notifications);
        
    }

    /**
     * Send notification to all users of a specific role/type
     */
    public static function sendToAllByUserType(string $userType, string $type, string $title, string $message, ?int $documentId, ?array $data = null, ?string $transactionStatus = null) {
        $users = User::where('type', $userType)->get();
        $userIds = $users->pluck('id')->toArray();
        
        if (!empty($userIds)) {
            self::sendtoMultiple($userIds, $type, $message, $title, $documentId, $data, $transactionStatus);
        }
    }

    /**
     * Send notification based on transaction status to relevant users
     */
    public static function sendByTransactionStatus(Transaction $transaction, string $type, string $title, string $message, ?array $data = null) {
        $status = $transaction->transaction_status;
        
        // Get all relevant users that should receive notifications based on status
        $users = self::getRecipientsForTransactionStatus($transaction, $status);
        
        if (!empty($users)) {
            foreach ($users as $user) {
                self::send($user, $type, $title, $message, $transaction->id, $data, $status);
            }
        }
    }

    /**
     * Get users who should receive notifications based on transaction status
     */
    private static function getRecipientsForTransactionStatus(Transaction $transaction, string $status): array {
        $recipients = [];
        
        switch ($status) {
            case 'pending':
            case 'in_progress':
                // Send to current reviewer/assignee and department heads
                if ($transaction->assign_staff_id) {
                    $staff = User::find($transaction->assign_staff_id);
                    if ($staff) $recipients[] = $staff;
                }
                // Add department heads
                $heads = User::where('type', 'Head')
                    ->where('department_id', $transaction->current_workflow_step > 0 ? $transaction->department_id : $transaction->origin_department_id)
                    ->get()
                    ->toArray();
                $recipients = array_merge($recipients, $heads);
                break;
                
            case 'approved':
            case 'completed':
                // Send to origin creator and their department
                $creator = User::find($transaction->created_by);
                if ($creator) $recipients[] = $creator;
                
                $deptHeads = User::where('type', 'Head')
                    ->where('department_id', $transaction->origin_department_id)
                    ->get()
                    ->toArray();
                $recipients = array_merge($recipients, $deptHeads);
                break;
                
            case 'rejected':
            case 'returned_to_creator':
                // Send to origin creator
                $creator = User::find($transaction->created_by);
                if ($creator) $recipients[] = $creator;
                break;
                
            case 'cancelled':
                // Send to all involved parties
                $creator = User::find($transaction->created_by);
                if ($creator) $recipients[] = $creator;
                
                $allReviewers = $transaction->reviewers()->get();
                foreach ($allReviewers as $reviewer) {
                    $user = User::find($reviewer->assigned_to);
                    if ($user) $recipients[] = $user;
                }
                break;
                
            default:
                // Default: send to all system users
                $recipients = User::all()->toArray();
                break;
        }
        
        // Remove duplicates based on user ID
        $unique = [];
        foreach ($recipients as $user) {
            if (is_object($user)) {
                $id = $user->id;
            } else {
                $id = $user['id'];
            }
            
            if (!isset($unique[$id])) {
                $unique[$id] = $user;
            }
        }
        
        return array_values($unique);
    }
}