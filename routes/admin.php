<?php

use App\Http\Controllers\Admin\AuditLog\AuditLogController;
use App\Http\Controllers\Admin\Departments\DepartmentController;
use App\Http\Controllers\Admin\Users\UserController;
use App\Http\Controllers\Document\DocumentAdminController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {

    Route::prefix('admin')->name('admin.')->middleware(['role:Admin'])->group(function () {

        Route::resource('users', UserController::class)->parameters(['users' => 'user']);

        Route::resource('departments', DepartmentController::class)->parameters(['departments' => 'department']);
        Route::post('departments/{department}/assign-head', [DepartmentController::class, 'assignHead'])->name('departments.assign-head');
        Route::delete('departments/{department}/remove-head', [DepartmentController::class, 'removeHead'])->name('departments.remove-head');
        Route::put('/departments/{id}', [DepartmentController::class, 'update'])->name('admin.departments.update');
        Route::get('departments/{id}/edit', [DepartmentController::class, 'edit'])->name('admin.departments.edit');

        Route::prefix('documents')->name('documents.')->group(function () {
            Route::get('/track', [DocumentAdminController::class, 'track'])->name('track');
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
