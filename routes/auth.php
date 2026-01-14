<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Staff\StaffController;
use App\Http\Controllers\Transaction\TransactionController;
use App\Http\Controllers\Transaction\TransactionReviewerController;
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
        Route::get('/my', [TransactionController::class, 'my'])->name('my');
        Route::get('/create', [TransactionController::class, 'create'])->name('create');
        Route::post('/', [TransactionController::class, 'store'])->name('store');
        Route::post('/{transaction}/creator-resubmit', [TransactionController::class, 'creatorResubmit'])->name('creator-resubmit');

        // Transaction Reviewer routes (must be before {transaction} wildcard)
        Route::prefix('reviews')->name('reviews.')->group(function () {
            Route::get('/', [TransactionReviewerController::class, 'index'])->name('index');
            Route::get('/overdue', [TransactionReviewerController::class, 'overdue'])->name('overdue');
            Route::get('/{reviewer}', [TransactionReviewerController::class, 'show'])->name('show');
            Route::get('/{reviewer}/review', [TransactionReviewerController::class, 'review'])->name('review');
            Route::post('/{reviewer}/approve', [TransactionReviewerController::class, 'approve'])->name('approve');
            Route::post('/{reviewer}/reject', [TransactionReviewerController::class, 'reject'])->name('reject');
            Route::post('/{reviewer}/receive', [TransactionReviewerController::class, 'receive'])->name('receive');
            Route::post('/{reviewer}/resubmit', [TransactionReviewerController::class, 'resubmit'])->name('resubmit');
            Route::put('/{reviewer}/due-date', [TransactionReviewerController::class, 'updateDueDate'])->name('update-due-date');
            Route::put('/{reviewer}/reassign', [TransactionReviewerController::class, 'reassign'])->name('reassign');
        });

        // AJAX endpoint
        Route::get('/workflow/{workflow}/config', [TransactionController::class, 'getWorkflowConfig'])->name('workflow.config');

        // Routes with {transaction} wildcard (must be after static routes)
        Route::get('/{transaction}', [TransactionController::class, 'show'])->name('show');
        Route::get('/{transaction}/edit', [TransactionController::class, 'edit'])->name('edit');
        Route::put('/{transaction}', [TransactionController::class, 'update'])->name('update');

        // Workflow actions
        Route::post('/{transaction}/execute-action', [TransactionController::class, 'executeAction'])->name('execute-action');
        Route::get('/{transaction}/tracker', [TransactionController::class, 'tracker'])->name('tracker');
        Route::get('/{transaction}/history', [TransactionController::class, 'history'])->name('history');
        Route::get('/{transaction}/workflow-config', [TransactionController::class, 'getDefaultWorkflowConfig'])->name('workflow-config');

        // Transaction cancellation
        Route::post('/{transaction}/cancel', [TransactionController::class, 'cancel'])->name('cancel');

        // Receiving confirmation for completed transactions
        Route::post('/{transaction}/confirm-received', [TransactionController::class, 'confirmReceived'])->name('confirm-received');
        Route::post('/{transaction}/mark-not-received', [TransactionController::class, 'markNotReceived'])->name('mark-not-received');

        // Review history for a specific transaction
        Route::get('/{transaction}/reviews', [TransactionReviewerController::class, 'history'])->name('review-history');
    });
});
