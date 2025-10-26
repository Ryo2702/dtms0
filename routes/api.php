<?php

use App\Http\Controllers\Notification\NotificationController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
  Route::get('/notifications/counts', [NotificationController::class, 'getCounts'])
    ->name('notifications.counts');

  Route::get('/notifications/list', [NotificationController::class, 'getNotifications'])
    ->name('notifications.list');

  Route::post('/notifications/mark-read', [NotificationController::class, 'markAsRead'])
    ->name('notifications.mark-read');

  Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])
    ->name('notifications.mark-all-read');
});