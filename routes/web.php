<?php

use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/login', [LoginController::class, 'show'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.store');
Route::post('/login/check-lock', [LoginController::class, 'checkLock'])->name('login.check-lock');

require __DIR__.'/auth.php';
require __DIR__.'/admin.php';
require __DIR__.'/reports.php';
require __DIR__.'/api.php';