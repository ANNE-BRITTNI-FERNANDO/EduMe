<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\DonationRequestController;
use App\Http\Controllers\Admin\AdminOrderController;
use App\Http\Controllers\Admin\ReportsController;
use App\Http\Controllers\Admin\PayoutController;
use App\Http\Controllers\Admin\BundleController;
use App\Http\Controllers\Admin\DonationController;
use App\Http\Controllers\Admin\ProductController;

Route::middleware(['web', 'auth'])->group(function () {
    Route::prefix('admin')->name('admin.')->group(function () {
        // Dashboard
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        // Legacy route for backward compatibility
        Route::get('/pending', [AdminDashboardController::class, 'pending'])->name('pending');

        // Donations Management
Route::group(['prefix' => 'donations', 'as' => 'donations.'], function () {
    Route::get('/', [DonationController::class, 'viewDonations'])->name('index');
    Route::get('/requests', [DonationController::class, 'requests'])->name('requests');
    Route::get('/{donation}', [DonationController::class, 'donationDetails'])->name('show');
    Route::post('/{donation}/start-review', [DonationController::class, 'startReview'])->name('start-review');
    Route::post('/{donation}/verify', [DonationController::class, 'verifyDonation'])->name('verify');
    Route::post('/{donation}/reject', [DonationController::class, 'rejectDonation'])->name('reject');
    Route::post('/requests/{donationRequest}/reject', [DonationController::class, 'rejectRequest'])->name('requests.reject');
    Route::post('/requests/{donationRequest}/approve', [DonationController::class, 'approveRequest'])->name('requests.approve');
});

// Products Management
Route::group(['prefix' => 'products', 'as' => 'products.'], function () {
    Route::get('/pending', [ProductController::class, 'pending'])->name('pending');
    Route::get('/approved', [ProductController::class, 'approved'])->name('approved');
    Route::get('/rejected', [ProductController::class, 'rejected'])->name('rejected');
    Route::post('/{product}/approve', [ProductController::class, 'updateStatus'])->name('approve');
    Route::post('/{product}/reject', [ProductController::class, 'updateStatus'])->name('reject');
});

        // Orders
        Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');

        // Reports
        Route::get('/reports', [ReportsController::class, 'index'])->name('reports.index');
        Route::get('/reports/generate', [ReportsController::class, 'generate'])->name('reports.generate');

        // Payouts
        Route::get('/payouts', [PayoutController::class, 'index'])->name('payouts.index');
        Route::post('/payouts/{id}/approve', [PayoutController::class, 'approve'])->name('payouts.approve');
        Route::post('/payouts/{id}/reject', [PayoutController::class, 'reject'])->name('payouts.reject');
    });
});
