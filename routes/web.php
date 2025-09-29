<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Document\DocumentController;
use App\Http\Controllers\Document\DocumentReviewController;
use App\Http\Controllers\Document\DocumentAdminController;
use App\Http\Controllers\Notification\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Verification\VerificationController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;



Route::get('/', function () {
    return view('welcome');
});
Route::get('/login', [LoginController::class, 'show'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.store');
Route::get('/verify/{code}', [VerificationController::class, 'verify'])->name('documents.verify');
Route::get('/qr/{code}', [VerificationController::class, 'qrcode'])->name('documents.qrcode');
Route::get('/lookup', [VerificationController::class, 'lookup'])->name('documents.lookup');

Route::middleware(['auth'])->group(function () {
    // Notification routes
    Route::get('/notifications/counts', [NotificationController::class, 'getCounts'])
        ->name('notifications.counts');
    
    Route::post('/notifications/mark-read', [NotificationController::class, 'markAsRead'])
        ->name('notifications.mark-read');

    // Single dashboard route that handles all roles
    Route::get('/dashboard', function () {
        /** @var User $authUser */
        $authUser = Auth::user();
        $user = $authUser;

        return view('dashboard', compact('user'));
    })->name('dashboard');
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Profile routes
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'show'])->name('show');
        Route::get('/edit', [ProfileController::class, 'edit'])->name('edit');
        Route::put('/', [ProfileController::class, 'update'])->name('update');
        Route::put('/password', [ProfileController::class, 'updatePassword'])->name('update-password');
        Route::get('/remove-avatar', [ProfileController::class, 'removeAvatar'])->name('remove-avatar');
    });

    // Document routes
    Route::prefix('documents')->name('documents.')->group(function () {
        Route::get('/', [DocumentController::class, 'index'])->name('index');
        Route::get('/{file}/fill', [DocumentController::class, 'form'])->name('form');
        Route::post('/{file}/download', [DocumentController::class, 'download'])->name('download');

        Route::prefix('reviews')->name('reviews.')->group(function () {
            Route::get('/', [DocumentReviewController::class, 'index'])->name('index');
            Route::get('/received', [DocumentReviewController::class, 'received'])->name('received');
            Route::get('/sent', [DocumentReviewController::class, 'sent'])->name('sent');
            Route::get('/completed', [DocumentReviewController::class, 'completed'])->name('completed');
            Route::get('/{id}', [DocumentReviewController::class, 'show'])->name('show');
            Route::put('/{id}', [DocumentReviewController::class, 'update'])->name('update');
            Route::get('/{id}/download', [DocumentReviewController::class, 'download'])->name('download');
        });

        Route::prefix('status')->name('status.')->group(function () {
             Route::get('/pending', [DocumentReviewController::class, 'index'])->name('pending');
             Route::get('/closed', [DocumentReviewController::class, 'closed'])->name('closed');
             Route::get('/rejected', [DocumentReviewController::class, 'rejected'])->name('rejected');
             Route::get('/canceled', [DocumentReviewController::class, 'canceled'])->name('canceled');
        });

    });

    Route::middleware(['role:Staff'])->group(function () {
        Route::get('/staff-area', fn() => "Staff Access Only");
    });
});

require __DIR__ . '/admin.php';
require __DIR__ . '/head.php';
