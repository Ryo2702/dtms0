<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Document\DocumentController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/login', [LoginController::class, 'show'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.store');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
    // Single dashboard route that handles all roles
    Route::get('/dashboard', function () {
        /** @var User $authUser */
        $authUser = Auth::user();
        $user = $authUser;

        return view('dashboard', compact('user'));
    })->name('dashboard');

    Route::prefix('documents')->name('documents.')->group(function () {
        Route::get('/', [DocumentController::class, 'index'])->name('index');
        Route::get('/{file}/fill', [DocumentController::class, 'form'])->name('form');
        Route::post('/{file}/download', [DocumentController::class, 'download'])->name('download');
    });

    Route::middleware(['role:Staff'])->group(function () {
        Route::get('/staff-area', fn() => "Staff Access Only");
    });
});

require __DIR__ . '/admin.php';
require __DIR__ . '/head.php';
