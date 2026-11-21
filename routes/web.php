<?php

use App\Http\Controllers\AvatarController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'index'])->name('home');
Route::get('/about', [PageController::class, 'about'])->name('about');

Route::middleware('auth')->group(function (): void {
    // One resource registration per account type exposes all seven conventional actions.
    Route::resource('customers', CustomerController::class);
    Route::resource('users', UserController::class);

    Route::get('/dashboard', fn () => redirect()->route('customers.index'))->name('dashboard');

    Route::get('/avatars/{filename}', [AvatarController::class, 'show'])
        ->where('filename', '[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}\.jpg')
        ->name('avatars.show');
});

// Published by the actual Laravel Breeze installer; includes login, register, and logout.
require __DIR__.'/auth.php';
