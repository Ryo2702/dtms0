<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Staff\StaffController;
use App\Http\Controllers\Transaction\TransactionWorkflowController;
use Illuminate\Support\Facades\Route;


Route::middleware(['auth'])->group(function () {


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

    Route::prefix('workflow-routing')->name('workflow.')->middleware('auth')->group(function () {
    // Head-only routes
    Route::middleware('head.only')->group(function () {
        Route::post('/route-transaction', [TransactionWorkflowController::class, 'routeTransaction'])->name('route-transaction');
        Route::post('/create-workflow', [TransactionWorkflowController::class, 'createWorkflow'])->name('create-workflow');
        Route::get('/pending-transactions', [TransactionWorkflowController::class, 'getPendingTransactions'])->name('pending');
        Route::get('/stats', [TransactionWorkflowController::class, 'getWorkflowStats'])->name('stats');
    });

    // Admin-only routes
    Route::middleware('admin.only')->group(function () {
        Route::post('/configure-cycle', [TransactionWorkflowController::class, 'configureWorkflowCycle'])->name('configure-cycle');
    });

    // Public routes (for data retrieval)
    Route::get('/by-type', [TransactionWorkflowController::class, 'getWorkflowsByType'])->name('by-type');
    Route::get('/active', [TransactionWorkflowController::class, 'getActiveWorkflows'])->name('active');
    Route::get('/chain', [TransactionWorkflowController::class, 'getWorkflowChain'])->name('chain');
    Route::get('/next-reviewers', [TransactionWorkflowController::class, 'getNextReviewers'])->name('next-reviewers');
});

});
