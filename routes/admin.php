<?php

use App\Http\Controllers\Admin\AuditLog\AuditLogController;
use App\Http\Controllers\Admin\Departments\DepartmentController;
use App\Http\Controllers\DocumentTagController;
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
            Route::get('/{id}/document-tags', [DepartmentController::class, 'documentTags'])->name('document-tags');
            Route::get('/{id}/workflows-with-tags', [DepartmentController::class, 'workflowsWithTags'])->name('workflows-with-tags');
            Route::get('/{id}/active-document-tags', [DepartmentController::class, 'activeDocumentTags'])->name('active-document-tags');
        });

        Route::prefix('document-tags')->name('document-tags.')->group(function () {
            Route::get('/', [DocumentTagController::class, 'index'])->name('index');
            Route::get('/create', [DocumentTagController::class, 'create'])->name('create');
            Route::post('/', [DocumentTagController::class, 'store'])->name('store');
            Route::get('/{documentTag}', [DocumentTagController::class, 'show'])->name('show');
            Route::get('/{documentTag}/edit', [DocumentTagController::class, 'edit'])->name('edit');
            Route::put('/{documentTag}', [DocumentTagController::class, 'update'])->name('update');
            Route::delete('/{documentTag}', [DocumentTagController::class, 'destroy'])->name('destroy');
            Route::post('/{documentTag}/toggle-status', [DocumentTagController::class, 'toggleStatus'])->name('toggle-status');
            Route::get('/department/{department}', [DocumentTagController::class, 'getByDepartment'])->name('by-department');
            Route::post('/bulk-assign', [DocumentTagController::class, 'bulkAssignToWorkflow'])->name('bulk-assign');
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
            Route::get('/create', [WorkflowConfigController::class, 'create'])->name('create');
            Route::post('/', [WorkflowConfigController::class, 'store'])->name('store');
            Route::get('/{workflow}/edit', [WorkflowConfigController::class, 'edit'])->name('edit');
            Route::put('/{workflow}', [WorkflowConfigController::class, 'update'])->name('update');
            Route::delete('/{workflow}', [WorkflowConfigController::class, 'destroy'])->name('destroy');
            Route::post('/preview', [WorkflowConfigController::class, 'preview'])->name('preview');
            Route::post('/{workflow}/duplicate', [WorkflowConfigController::class, 'duplicate'])->name('duplicate');
            Route::post('/{workflow}/toggle-status', [WorkflowConfigController::class, 'toggleStatus'])->name('toggle-status');
            Route::get('/{workflow}/document-tags', [WorkflowConfigController::class, 'getDocumentTags'])->name('document-tags');
            Route::get('/{workflow}/tag-departments', [WorkflowConfigController::class, 'getTagDepartments'])->name('tag-departments');
        });

        Route::resource('transaction-types', TransactionTypeController::class);
    });
});
