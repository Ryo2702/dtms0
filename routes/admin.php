<?php

use App\Http\Controllers\Admin\Departments\DepartmentController;
use App\Http\Controllers\Admin\Users\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {

    Route::prefix('admin')->name('admin.')->middleware(['role:Admin'])->group(function () {
        Route::get('/dashboard', fn() => view('admin.admin-dashboard'))->name('dashboard');
        // User Management Routes     
        Route::resource('users', UserController::class)->parameters(['users' => 'user']);
        Route::post('users/bulk-action', [UserController::class, 'bulkAction'])->name('users.bulk-action');
        Route::post('users/{user}/deactivate', [UserController::class, 'deactivate'])->name('users.deactivate');
        Route::post('users/{user}/reactivate', [UserController::class, 'reactivate'])->name('users.reactivate');
        Route::get('users-archives', [UserController::class, 'archives'])->name('users.archives');
        Route::get('user-accounts', [UserController::class, 'userAccounts'])->name('users.user-accounts');
        Route::get('users-archives/{archive}', [UserController::class, 'showArchive'])->name('users.archive-detail');


        // Department Management Routes
        Route::resource('departments', DepartmentController::class)->parameters(['departments' => 'department']);
        Route::patch('departments/{department}/deactivate', [DepartmentController::class, 'deactivate'])->name('departments.deactivate');
        Route::patch('departments/{department}/reactivate', [DepartmentController::class, 'reactivate'])->name('departments.reactivate');
        Route::post('departments/{department}/assign-head', [DepartmentController::class, 'assignHead'])->name('departments.assign-head');
        Route::delete('departments/{department}/remove-head', [DepartmentController::class, 'removeHead'])->name('departments.remove-head');
        Route::get('departments/{department}/available-staff', [DepartmentController::class, 'getAvailableStaff'])->name('departments.available-staff');
    });
});
