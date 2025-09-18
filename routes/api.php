<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\BarcodeController;

Route::post('/Barcode_check', [BarcodeController::class, 'check']);
Route::post('/scan/save', [BarcodeController::class, 'save']);