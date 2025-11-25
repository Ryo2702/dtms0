<?php

use App\Http\Controllers\Admin\AuditLog\AuditLogController;
use App\Http\Controllers\Admin\Departments\DepartmentController;
use App\Http\Controllers\Admin\Users\UserController;
use App\Http\Controllers\Document\DocumentAdminController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {

    Route::prefix('admin')->name('admin.')->middleware(['role:Admin'])->group(function () {

        Route::resource('users', UserController::class)->parameters(['users' => 'user']);


        Route::prefix('departments')->name('departments.')->group(function () {
            Route::resource('/', DepartmentController::class)->parameters(['departments' => 'department']);
            Route::put('/{id}', [DepartmentController::class, 'update'])->name('update');
            Route::get('/{id}/edit', [DepartmentController::class, 'edit'])->name('edit');
            Route::get('/{id}/users', [DepartmentController::class, 'users'])->name('users');
            Route::post('/{id}/assign-user', [DepartmentController::class, 'assignUser'])->name('assign-user');
            Route::delete('/{id}/remove-user', [DepartmentController::class, 'removeUser'])->name('remove-user');
        });

        Route::prefix('audit-logs')->name('audit-logs.')->group(function () {
            Route::get('/', [AuditLogController::class, 'index'])->name('index');
            Route::get('/export', [AuditLogController::class, 'export'])->name('export');
            Route::get('/test', [AuditLogController::class, 'test'])->name('test');
            Route::match(['POST', 'PUT', 'DELETE'], '/test/action', [AuditLogController::class, 'testAction'])->name('test.action');
            Route::get('/{auditLog}', [AuditLogController::class, 'show'])->name('show');
        });
    });
});
