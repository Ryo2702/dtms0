<?php

namespace App\Helpers;

use App\Models\Notification;
use App\Models\User;




class NotificationHelper{
    public static function send(User $user, string $type, string $title, string $message, ?int $documentId, ?array $data = null) {
        return Notification::create([
            'user_id' => $user->id,
            'document_id' => $documentId,
            'title' => $title,
            'type' => $type,
            'message' => $message,
            'data' => $data
        ]);
    }

    public static function sendtoMultiple(array $userIds, string $type, string $message, string $title, ?int $documentId, ?array $data = null ){

        $notifications = [];
        foreach ($userIds as $userId) {
            $notifications[] = [
                'user_id' => $userId,
                'document_id' => $documentId,
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
}