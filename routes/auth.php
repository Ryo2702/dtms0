<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Document\DocumentController;
use App\Http\Controllers\Document\DocumentReviewController;
use App\Http\Controllers\Document\DocumentTypeController;
use App\Http\Controllers\History\HistoryController;
use App\Http\Controllers\Notification\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Staff\StaffController;
use Illuminate\Support\Facades\Route;

Route::get('/document/{documentId}', [DocumentReviewController::class, 'showByDocumentId'])->name('documents.show-by-id');

Route::middleware(['auth'])->group(function () {

    Route::get('/documents/types/{departmentId}', [DocumentController::class, 'getDocumentTypes']);
   
    Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');
   
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'show'])->name('show');
        Route::get('/edit', [ProfileController::class, 'edit'])->name('edit');
        Route::put('/', [ProfileController::class, 'update'])->name('update');
        Route::put('/password', [ProfileController::class, 'updatePassword'])->name('update-password');
        Route::get('/remove-avatar', [ProfileController::class, 'removeAvatar'])->name('remove-avatar');
    });

    Route::prefix('staff')->name('staff.')->group(function () {
        Route::get('/', [StaffController::class,'index'])->name('index');
        Route::post('/create', [StaffController::class,'store'])->name('store');
        Route::put('/{staff}', [StaffController::class, 'update'])->name('update');
    });

});
