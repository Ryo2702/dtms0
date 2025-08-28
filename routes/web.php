<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Document\DocumentController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/login', [LoginController::class, 'show'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.store');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
    // Single dashboard route that handles all roles
    Route::get('/dashboard', fn() => view('dashboard'))->name('dashboard');



    Route::middleware(['role:Staff'])->group(function () {
        Route::get('/staff-area', fn() => "Staff Access Only");
    });

    Route::middleware(['role:Head'])->group(function () {
        Route::get('/head-area', fn() => "Head Access Only");
    });
    Route::get('/documents', [DocumentController::class, 'index'])->name('documents.index');
    Route::get('/documents/download/{file}', [DocumentController::class, 'download'])->name('documents.download');
});

require __DIR__ . '/admin.php';
