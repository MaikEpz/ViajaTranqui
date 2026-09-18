<?php

use App\Http\Controllers\CountryController;
use App\Http\Controllers\QuotationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - ViajaTranqui
|--------------------------------------------------------------------------
*/

Route::get('/countries', [CountryController::class, 'index']);

Route::prefix('quotes')->group(function () {
    Route::post('/calculate', [QuotationController::class, 'calculate']);
    Route::get('/', [QuotationController::class, 'index']);
    Route::post('/', [QuotationController::class, 'store']);
    Route::get('/{quotation}', [QuotationController::class, 'show']);
    Route::patch('/{quotation}/contract', [QuotationController::class, 'contract']);
    Route::get('/{quotation}/pdf', [QuotationController::class, 'downloadPdf']);
});
