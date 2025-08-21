<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {

    Route::prefix('admin')->name('admin.')->middleware(['role:Admin'])->group(function () {
        Route::resource('users', UserController::class);

        // User deactivation/reactivation routes
        Route::post('users/{user}/deactivate', [UserController::class, 'deactivate'])->name('users.deactivate');
        Route::post('users/{user}/reactivate', [UserController::class, 'reactivate'])->name('users.reactivate');

        // User archives routes
        Route::get('users-archives', [UserController::class, 'archives'])->name('users.archives');
        Route::get('user-accounts', [UserController::class, 'userAccounts'])->name('users.user-accounts');
        Route::get('users-archives/{archive}', [UserController::class, 'showArchive'])->name('users.archive-detail');
    });
});
