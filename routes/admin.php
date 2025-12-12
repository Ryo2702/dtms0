<?php

use App\Http\Controllers\Admin\AuditLog\AuditLogController;
use App\Http\Controllers\Admin\Departments\DepartmentController;
use App\Http\Controllers\Admin\Users\UserController;
use App\Http\Controllers\Transaction\TransactionTypeController;
use App\Http\Controllers\Transaction\WorkflowConfigController;
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

        Route::prefix('workflows')->name('workflows.')->group(function () {
            Route::get('/', [WorkflowConfigController::class, 'index'])->name('index');
            Route::get('/{transactionType}/edit', [WorkflowConfigController::class, 'edit'])->name('edit');
            Route::put('/{transactionType}', [WorkflowConfigController::class, 'update'])->name('update');
            Route::post('/preview', [WorkflowConfigController::class, 'preview'])->name('preview');
            Route::get('/{transactionType}/steps', [WorkflowConfigController::class, 'getSteps'])->name('steps');
            Route::post('/{transactionType}/duplicate', [WorkflowConfigController::class, 'duplicate'])->name('duplicate');
        });

        Route::resource('transaction-types', TransactionTypeController::class);
    });
});
