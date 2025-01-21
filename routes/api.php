<?php

use App\Http\Controllers\DonationController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    // Donation Routes
    Route::prefix('donations')->group(function () {
        // Item Donations
        Route::get('/', [DonationController::class, 'index']);
        Route::post('/', [DonationController::class, 'store']);
        Route::get('/{donation}', [DonationController::class, 'show']);
        Route::post('/{donation}/request', [DonationController::class, 'requestDonation']);
        
        // Monetary Donations
        Route::post('/monetary', [DonationController::class, 'createMonetaryDonation']);
        Route::get('/monetary/history', [DonationController::class, 'getMonetaryDonationHistory']);
        
        // Admin Routes
        Route::middleware('admin')->group(function () {
            Route::get('/statistics', [DonationController::class, 'getStatistics']);
            Route::post('/{donation}/verify', [DonationController::class, 'verifyDonation']);
        });
    });
});
