<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// Strona startowa – logowanie
Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

// Wylogowanie
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Rejestracja
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);

// Dashboard / strona po zalogowaniu
Route::get('/welcome', function() {
    return view('welcome');
})->middleware('auth')->name('welcome');
