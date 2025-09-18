<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProduktSkanyController;
use App\Http\Controllers\BarcodeController;
use App\Http\Controllers\SpisZNaturyController;

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

// ✅ Dodajemy edycję ilości
Route::get('/produkt-skany/{produktSkany}/edit', [ProduktSkanyController::class, 'edit'])->name('produkt_skany.edit');
Route::put('/produkt-skany/{produktSkany}', [ProduktSkanyController::class, 'update'])->name('produkt_skany.update');

// ✅ Dodajemy możliwość usuwania
Route::delete('/produkt-skany/{produktSkany}', [ProduktSkanyController::class, 'destroy'])->name('produkt_skany.destroy');

Route::post('/Barcode_check', [BarcodeController::class, 'check'])->middleware('auth');
Route::post('/scan/save', [BarcodeController::class, 'save'])->middleware('auth');

Route::resource('spisy', SpisZNaturyController::class);  
Route::get('/spisy/{spis}/produkty', [SpisZNaturyController::class, 'showProdukty'])->name('spisy.produkty');















    // idk do wyjebania kiedyś to na dole wszystko





});