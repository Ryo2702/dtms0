<?php

use App\Http\Controllers\Document\DocumentController;
use App\Http\Controllers\Head\Staff\StaffController;
use App\Http\Controllers\Report\ReportController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::prefix('head')->name('head.')->middleware(['role:Head'])->group(function () {
        Route::resource('staff', StaffController::class)->names('staff');
        
        // Report Routes
        Route::prefix('report')->name('report.')->group(function () {
            Route::get('/', [ReportController::class, 'index'])->name('index');
            Route::get('/document-performance', [ReportController::class, 'documentPerformance'])->name('document-performance');
            Route::get('/department-summary', [ReportController::class, 'departmentSummary'])->name('department-summary');
            Route::get('/staff-productivity', [ReportController::class, 'staffProductivity'])->name('staff-productivity');
            Route::get('/export/{type}', [ReportController::class, 'export'])->name('export');
        });
    });
});
