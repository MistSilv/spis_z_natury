<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProduktSkanyController;

// Przekierowanie ze strony głównej na /login
Route::get('/', function () {
    return redirect()->route('login');
});

// Strona logowania dla niezalogowanych
Route::get('/login', [AuthController::class, 'showLogin'])->middleware('guest')->name('login');

// Logowanie
Route::post('/login', [AuthController::class, 'login'])->middleware('guest');

// Wylogowanie
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Rejestracja
Route::get('/register', [AuthController::class, 'showRegister'])->middleware('guest')->name('register');
Route::post('/register', [AuthController::class, 'register'])->middleware('guest');

// Wszystkie poniższe trasy wymagają zalogowania
Route::middleware('auth')->group(function () {
    Route::get('/welcome', function () {
        return view('welcome');
    })->name('welcome');






Route::get('/produkt-skany', [ProduktSkanyController::class, 'index'])->name('produkt_skany.index');
Route::get('/produkt-skany/create', [ProduktSkanyController::class, 'create'])->name('produkt_skany.create');
Route::post('/produkt-skany', [ProduktSkanyController::class, 'store'])->name('produkt_skany.store');
Route::get('/produkt-skany/{produktSkany}', [ProduktSkanyController::class, 'show'])->name('produkt_skany.show');















    // idk do wyjebania kiedyś to na dole wszystko




    // Ustawienia
    Route::get('/settings', function () {
        return view('settings');
    })->name('settings');
    Route::get('/ustawienia', function () {
        return redirect()->route('settings');
    })->name('ustawienia');
});