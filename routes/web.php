<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\StocktakingController;

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

    Route::resource('products', ProductController::class);

    Route::resource('stocktakings', StocktakingController::class)->except(['edit', 'update', 'destroy']);
    Route::post('stocktakings/{stocktaking}/items', [StocktakingController::class, 'addItem'])->name('stocktakings.addItem');

    // **Nowe trasy dla generowania i edycji spisu**
Route::get('/stocktakings/{stocktaking}/generate', [StocktakingController::class, 'generate'])
    ->name('stocktakings.generate'); // otwiera stronę w nowej karcie

Route::put('/stocktakings/{stocktaking}/update-items', [StocktakingController::class, 'updateItems'])
    ->name('stocktakings.updateItems'); // zapis zmian w tabeli

    Route::get('/stocktakings/{stocktaking}/print', [StocktakingController::class, 'print'])
    ->name('stocktakings.print');

    Route::post('/stocktakings/{stocktaking}/remember-selected', [StocktakingController::class, 'rememberSelected'])
    ->name('stocktakings.rememberSelected');

Route::delete('/stocktakings/item/{item}', [StocktakingController::class, 'deleteItem'])
    ->name('stocktakings.deleteItem');


   














    // idk do wyjebania kiedyś to na dole wszystko

    Route::get('/raporty', [ProductController::class, 'raport'])->name('raporty');



    // Ustawienia
    Route::get('/settings', function () {
        return view('settings');
    })->name('settings');
    Route::get('/ustawienia', function () {
        return redirect()->route('settings');
    })->name('ustawienia');
});