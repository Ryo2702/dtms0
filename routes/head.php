<?php

use App\Http\Controllers\Head\Staff\StaffController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::prefix('head')->name('head.')->middleware(['role:Head'])->group(function () {
        Route::resource('staff', StaffController::class)->names('staff');
    });
});
