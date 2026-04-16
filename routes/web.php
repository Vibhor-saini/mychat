<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
// Route::post('/register', [AuthController::class, 'register']);

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');



Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Day 2 new routes
    Route::get('/users', [AuthController::class, 'userIndex'])->name('users.index');
    Route::post('/users/create', [AuthController::class, 'userStore'])->name('users.store');

    Route::get('/chat/{id}', [AuthController::class, 'startChat'])->name('chat.start');
    Route::post('/send-message', [AuthController::class, 'sendMessage'])->name('message.send');
});
