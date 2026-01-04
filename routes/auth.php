<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Staff\StaffController;
use App\Http\Controllers\Transaction\TransactionController;
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
        Route::get('/', [StaffController::class, 'index'])->name('index');
        Route::post('/create', [StaffController::class, 'store'])->name('store');
        Route::put('/{staff}', [StaffController::class, 'update'])->name('update');
    });

    Route::prefix('transactions')->name('transactions.')->group(function () {
        Route::get('/', [TransactionController::class, 'index'])->name('index');
        Route::get('/create', [TransactionController::class, 'create'])->name('create');
        Route::post('/', [TransactionController::class, 'store'])->name('store');
        Route::get('/{transaction}', [TransactionController::class, 'show'])->name('show');
        Route::get('/{transaction}/edit', [TransactionController::class, 'edit'])->name('edit');
        Route::put('/{transaction}', [TransactionController::class, 'update'])->name('update');

        // Workflow actions
        Route::post('/{transaction}/execute-action', [TransactionController::class, 'executeAction'])->name('execute-action');
        Route::get('/{transaction}/tracker', [TransactionController::class, 'tracker'])->name('tracker');
        Route::get('/{transaction}/history', [TransactionController::class, 'history'])->name('history');
        Route::get('/{transaction}/workflow-config', [TransactionController::class, 'getDefaultWorkflowConfig'])->name('workflow-config');

        // AJAX endpoint
        Route::get('/workflow/{workflow}/config', [TransactionController::class, 'getWorkflowConfig'])->name('workflow.config');
    });
});
