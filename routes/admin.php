<?php

use App\Http\Controllers\Admin\Departments\DepartmentController;
use App\Http\Controllers\Admin\Users\UserController;
use App\Http\Controllers\Document\DocumentAdminController;
use App\Http\Controllers\Document\DocumentController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {

    Route::prefix('admin')->name('admin.')->middleware(['role:Admin'])->group(function () {
        // User Management Routes     
        Route::resource('users', UserController::class)->parameters(['users' => 'user']);

        // Department Management Routes
        Route::resource('departments', DepartmentController::class)->parameters(['departments' => 'department']);
        Route::post('departments/{department}/assign-head', [DepartmentController::class, 'assignHead'])->name('departments.assign-head');
        Route::delete('departments/{department}/remove-head', [DepartmentController::class, 'removeHead'])->name('departments.remove-head');
        Route::get('departments/{department}/available-staff', [DepartmentController::class, 'getAvailableStaff'])->name('departments.available-staff');

        //document-tract
        Route::prefix('documents')->name('documents.')->group(function () {
            Route::get('/track', [DocumentAdminController::class, 'track'])->name('track');
        });
    });
});
