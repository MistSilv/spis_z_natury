<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProduktSkanyController;
use App\Http\Controllers\BarcodeController;
use App\Http\Controllers\SpisZNaturyController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SpisPdfController;

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

Route::get('/produkt-skany/{produktSkany}/edit', [ProduktSkanyController::class, 'edit'])->name('produkt_skany.edit');
Route::put('/produkt-skany/{produktSkany}', [ProduktSkanyController::class, 'update'])->name('produkt_skany.update');

Route::delete('/produkt-skany/{produktSkany}', [ProduktSkanyController::class, 'destroy'])->name('produkt_skany.destroy');

Route::post('/Barcode_check', [BarcodeController::class, 'check'])->middleware('auth');
Route::post('/scan/save', [BarcodeController::class, 'save'])->middleware('auth');


Route::get('/spisy/archiwum', [SpisZNaturyController::class, 'archiwum'])
     ->name('spisy.archiwum')
     ->middleware('auth');

Route::resource('spisy', SpisZNaturyController::class);  
Route::get('/spisy/{spis}/produkty', [SpisZNaturyController::class, 'showProdukty'])->name('spisy.produkty');

Route::post('/spisy/{spis}/produkty/add', [App\Http\Controllers\SpisZNaturyController::class, 'addProdukty'])
    ->name('spisy.produkty.add');

Route::get('/spisy/{spis}/produkty/spis', [App\Http\Controllers\SpisZNaturyController::class, 'showSpisProdukty'])
    ->name('spisy.spis_produkty');

Route::get('spisy/{spis}/podsumowanie', [SpisZNaturyController::class, 'podsumowanieSpisu'])
    ->name('spisy.podsumowanie');

Route::post('spisy/{spis}/produkt/{produkt}/update', [SpisZNaturyController::class, 'updateProduktSpisu'])
    ->name('spisy.produkty.update');

Route::delete('spisy/{spis}/produkt/{produkt}/delete', [SpisZNaturyController::class, 'deleteProduktSpisu'])
    ->name('spisy.produkty.delete');

Route::post('/spisy/{spis}/produkty/{produkt}/split', [SpisZNaturyController::class, 'splitProduktSpisu'])
    ->name('spisy.produkty.split');


// lista produktów
Route::get('/product-list', [ProductController::class, 'index'])->name('products.index');

// formularz dodania
Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');

// zapis nowego produktu
Route::post('/products', [ProductController::class, 'store'])->name('products.store');

// formularz edycji
Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');

// aktualizacja produktu
Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');

Route::prefix('spisy/{spis}')->group(function () {
    Route::post('/produkty-temp/{produkt}/update', [\App\Http\Controllers\SpisProduktyTmpController::class, 'update'])->name('spisy.produkty.temp.update');
    Route::post('/produkty-temp/{produkt}/split', [\App\Http\Controllers\SpisProduktyTmpController::class, 'split'])->name('spisy.produkty.temp.split');
    Route::delete('/produkty-temp/{produkt}/delete', [\App\Http\Controllers\SpisProduktyTmpController::class, 'destroy'])->name('spisy.produkty.temp.delete');
});

Route::get('/spisy/{spis}/produkty-temp', [SpisZNaturyController::class, 'showTmpProdukty'])->name('spisy.tmp');

Route::get('/spisy/{spis}/pdf', [SpisPdfController::class, 'export'])->name('spisy.export.pdf');





});