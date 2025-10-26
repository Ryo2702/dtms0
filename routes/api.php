<?php

use App\Http\Controllers\Notification\NotificationController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function (){
  Route::get('/notifications/counts', [NotificationController::class, 'getCounts'])
        ->name('notifications.counts');

    Route::post('/notifications/mark-read', [NotificationController::class, 'markAsRead'])
        ->name('notifications.mark-read');
});