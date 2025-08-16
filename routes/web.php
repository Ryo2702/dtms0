<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/login', [LoginController::class, 'show'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.store');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', fn() => view('dashboard'))->name('dashboard');

    Route::prefix('admin')->name('admin.')->middleware(['role:Admin'])->group(function () {
        Route::get('users', [UserController::class, 'index'])->name('users.index');     // list users
        Route::get('users/create', [UserController::class, 'create'])->name('users.create'); // form create
        Route::post('users', [UserController::class, 'store'])->name('users.store');    // store user
        Route::get('users/{user}', [UserController::class, 'show'])->name('users.show');    // show single user
        Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit'); // edit form
        Route::put('users/{user}', [UserController::class, 'update'])->name('users.update'); // update user
    });
    Route::middleware(['role:Staff'])->group(function () {
        Route::get('/staff-area', fn() => "Staff Access Only");
    });

    Route::middleware(['role:Officer'])->group(function () {
        Route::get('/officer-area', fn() => "Officer Access Only");
    });
});
