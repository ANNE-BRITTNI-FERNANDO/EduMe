<?php
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\AdminBundleController;
use App\Http\Controllers\Seller\EarningsController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\BankTransferController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Admin\AdminDashboardController;

// Basic view routes
Route::get('/', function () {
    return view('home');
});

Route::get('/home', function () {
    return view('home');
})->name('home');

Route::get('/doner', function () {
    return view('doner');
})->name('doner');

// Authenticated routes (only accessible to authenticated users)

Route::middleware(['auth'])->group(function () {
    // Route::get('/admin/dashboard', function () {
    //     // Check if the authenticated user has the admin role
    //     if (Auth::user()->role !== 'admin') {
    //         // If not an admin, redirect to the unauthorized page
    //         return redirect()->route('home');
    //     }

    //     return view('admin.dashboard'); // Only accessible for admins
    // })->name('admin.dashboard');
});

// Unauthorized Route - Custom page for non-admin users
Route::get('/home', function () {
    return view('home'); // Show an "Unauthorized" message
})->name('home');

// Customer Dashboard Route - Accessible by all authenticated users (customers and admins)
Route::middleware(['auth', 'web'])->group(function () {
    Route::get('/dashboard1', function () {
        return view('dashboard1');
    })->name('dashboard1');

    // Seller routes for creating and storing products
    Route::get('/seller', [ProductController::class, 'create'])->name('seller');
    Route::post('/seller', [ProductController::class, 'store'])->name('seller.store');

    // Show approved products for the logged-in user
    Route::get('/approved-products', [ProductController::class, 'showApprovedProducts'])->name('approved.products');
    
    // Profile management routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

});

// Admin routes protected by middleware
Route::middleware(['auth', AdminMiddleware::class])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/approved', [AdminDashboardController::class, 'approved'])->name('approved');

    // Payout Management Routes
    Route::prefix('payouts')->name('payouts.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\PayoutController::class, 'index'])->name('index');
        Route::get('/history', [App\Http\Controllers\Admin\PayoutController::class, 'history'])->name('history');
        Route::get('/seller-balances', [App\Http\Controllers\Admin\PayoutController::class, 'sellerBalances'])->name('seller-balances');
        Route::get('/{payoutRequest}', [App\Http\Controllers\Admin\PayoutController::class, 'show'])->name('show');
        Route::post('/{payoutRequest}/approve', [App\Http\Controllers\Admin\PayoutController::class, 'approve'])->name('approve');
        Route::post('/{payoutRequest}/complete', [App\Http\Controllers\Admin\PayoutController::class, 'complete'])->name('complete');
        Route::post('/{payoutRequest}/reject', [App\Http\Controllers\Admin\PayoutController::class, 'reject'])->name('reject');
    });
});

// Route for approving a product
Route::post('/admin/product/approve/{id}', function ($id) {
    if (!Auth::check() || Auth::user()->role !== 'admin') {
        // Redirect non-admin users
        return redirect()->route('home');
    }
    // Call the ProductController's approve method
    return app(\App\Http\Controllers\ProductController::class)->approve($id);
})->name('product.approve');

// Route for rejecting a product
Route::post('/admin/product/reject/{id}', function ($id) {
    if (!Auth::check() || Auth::user()->role !== 'admin') {
        // Redirect non-admin users
        return redirect()->route('home');
    }
    // Call the ProductController's reject method
    return app(\App\Http\Controllers\ProductController::class)->reject($id);
})->name('product.reject');

// Unauthorized route
Route::get('/home', function () {
    return response()->view('home', [], 403); // Custom unauthorized page
})->name('home');

// Show approved products for the logged-in user
Route::get('/approved-products', [ProductController::class, 'showApprovedProducts'])->name('approved.products');
    
// Profile management routes
Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

// // Admin routes for managing products
// Route::middleware(['auth'])->group(function () {
//     // Display all products in the admin dashboard
//     Route::get('/admin/dashboard', [ProductController::class, 'index'])->name('admin.dashboard');

//     // Routes for approving and rejecting products in the admin dashboard
//     Route::post('/admin/product/approve/{id}', [ProductController::class, 'approve'])->name('product.approve');
//     Route::post('/admin/product/reject/{id}', [ProductController::class, 'reject'])->name('product.reject');

// View route for the product listing page with advanced filtering
Route::get('/productlisting', [ProductController::class, 'listApprovedProducts'])->name('productlisting');

// Additional routes for product approval/rejection
Route::post('/products/{id}/approve', [ProductController::class, 'approve'])->name('product.approve');
Route::post('/products/{id}/reject', [ProductController::class, 'reject'])->name('product.reject');

Route::get('/product/edit/{id}', [ProductController::class, 'edit'])->name('product.edit');
Route::put('/product/{id}', [ProductController::class, 'update'])->name('product.update');
// routes/web.php
Route::delete('/product/{product}', [ProductController::class, 'destroy'])->name('product.destroy');


// routes/web.php

// In routes/web.php

Route::get('/productlisting', [ProductController::class, 'listApprovedProducts'])->name('productlisting');


// In routes/web.php

// Route::get('/products/approved', [ProductController::class, 'listApprovedProducts'])->name('products.approved');
Route::get('/approved-products/filter', [ProductController::class, 'filterApprovedProducts'])->name('approved.products.filter');
Route::get('/products/filter', [ProductController::class, 'filterApprovedProducts'])->name('products.filter');

Route::get('/product/{id}', [ProductController::class, 'show'])->name('product.show');



use App\Http\Controllers\BundleController;

// Bundle routes
Route::get('/bundle/{id}', [BundleController::class, 'show'])->name('bundle.show');
Route::get('/shopbundle', [BundleController::class, 'index'])->name('shopbundle.index');
Route::get('/sell-bundle', [BundleController::class, 'create'])->name('sell-bundle');
Route::post('/bundle/store', [BundleController::class, 'store'])->name('bundle.store');

// Admin Bundle routes
Route::prefix('admin')->middleware('auth')->group(function () {
    Route::get('/bundles', [AdminBundleController::class, 'index'])->name('admin.bundles.index');
    Route::get('/bundles/approved', [AdminBundleController::class, 'approvedBundles'])->name('admin.bundles.approved');
    Route::post('/bundles/{id}/update-status', [AdminBundleController::class, 'updateStatus'])->name('admin.bundles.updateStatus');
});

Route::resource('admin/bundles', AdminBundleController::class);
Route::resource('bundles', BundleController::class);

Route::get('admin/bundles', [AdminBundleController::class, 'index'])->name('admin.bundles.index');

// In routes/web.php
Route::get('/bundle/{id}', [BundleController::class, 'show'])->name('bundle.show');
// In routes/web.php
Route::put('/admin/bundles/{id}/update-status', [AdminBundleController::class, 'updateStatus'])->name('admin.bundles.updateStatus');

// In routes/web.php





// Shop Routes
use App\Http\Controllers\ShopBundleController;
Route::get('/shop/bundles', [BundleController::class, 'index'])->name('shop.bundles');
Route::get('/shop/bundles/{id}', [BundleController::class, 'show'])->name('bundles.show');

// Route::get('/shop/bundles', [BundleController::class, 'index'])->name('bundles.index');
// Route::get('/shop/bundles', [AdminBundleController::class, 'index'])->name('shop.bundles.index');
// Route::get('/shop/bundles', [AdminBundleController::class, 'index'])->name('shop.bundles.index');
// Route::get('shop/bundles', [AdminBundleController::class, 'showApprovedBundles'])->name('shop.bundles.index');
// Route::get('/shop/bundles', [ShopController::class, 'showBundles'])->name('shop.bundles');

// In routes/web.php

// Route to view approved bundles
Route::get('/admin/bundles/approved', [BundleController::class, 'approved'])->name('admin.bundles.approved');

// Route for updating the status (Approve/Reject)
Route::post('/admin/bundles/{id}/update-status', [BundleController::class, 'updateStatus'])->name('admin.bundles.updateStatus');
// In routes/web.php
Route::get('/admin/approved-bundles', [AdminBundleController::class, 'approvedBundles'])->name('admin.approved.bundles');

Route::get('/admin/bundles/approved', [AdminBundleController::class, 'approvedBundles'])->name('admin.bundles.approved');

// In routes/web.php
use App\Http\Controllers\ShopController;

Route::get('/shop/bundles', [BundleController::class, 'index'])->name('shop.bundles');


// Define the product.store route
Route::post('/product/store', [ProductController::class, 'store'])->name('product.store');


Route::get('/sell-bundle', [BundleController::class, 'create'])->name('sell-bundle');

// Bank Transfer Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/orders/bank-transfer', [OrderController::class, 'bankTransfer'])->name('orders.bank-transfer');
    Route::post('/orders/bank-transfer/store', [OrderController::class, 'storeBankTransfer'])->name('orders.bank-transfer.store');
    Route::get('/orders/bank-transfer/success', [OrderController::class, 'bankTransferSuccess'])->name('orders.bank-transfer.success');
    Route::post('/bank-transfer/{conversation}/confirm', [BankTransferController::class, 'confirm'])->name('bank-transfer.confirm');
    Route::post('/bank-transfer/{conversation}/reject', [BankTransferController::class, 'reject'])->name('bank-transfer.reject');
});

Route::delete('/product/{id}', [ProductController::class, 'destroy'])->name('product.destroy');

Route::get('/products', [ProductController::class, 'index'])->name('products.index');

Route::get('/products', [ProductController::class, 'index'])->name('product.index');



// Other routes...

Route::delete('/product/{id}', [ProductController::class, 'destroy'])->name('product.destroy');

// Cart and Checkout Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add/{type}/{id}', [CartController::class, 'addToCart'])->name('cart.add');
    Route::match(['post', 'delete'], '/cart/remove/{cartItem}', [CartController::class, 'removeFromCart'])->name('cart.remove');
    
    // Checkout Routes
    Route::get('/checkout', [CartController::class, 'checkout'])->name('checkout');
    Route::post('/checkout/process', [CartController::class, 'processCheckout'])->name('checkout.process');
    Route::get('/checkout/success', [CartController::class, 'checkoutSuccess'])->name('checkout.success');
    
    // Payment Routes
    Route::get('/payment/checkout', [PaymentController::class, 'showCheckout'])->name('payment.checkout');
    Route::post('/payment/create-intent', [PaymentController::class, 'createPaymentIntent'])->name('payment.intent');
    Route::get('/payment/success', [PaymentController::class, 'success'])->name('payment.success');
    Route::get('/payment/cancel', [PaymentController::class, 'cancel'])->name('payment.cancel');
});

// Chat Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/conversations', [App\Http\Controllers\ChatController::class, 'listConversations'])->name('chat.index');
    Route::get('/conversations/{conversation}', [App\Http\Controllers\ChatController::class, 'showConversation'])->name('chat.show');
    Route::get('/products/{id}/chat', [App\Http\Controllers\ChatController::class, 'startConversation'])->name('chat.start.product');
    Route::get('/bundles/{id}/chat', [App\Http\Controllers\ChatController::class, 'startConversation'])->name('chat.start.bundle');
    Route::post('/conversations/{conversation}/messages', [App\Http\Controllers\ChatController::class, 'sendMessage'])->name('chat.message');
});

Route::get('/products', [ProductController::class, 'index'])->name('products.index');


Route::delete('/products/{id}', [ProductController::class, 'destroy'])->name('product.destroy');
Route::post('/admin/bundles/{id}/updateStatus', [BundleController::class, 'updateStatus']);

Route::middleware(['auth'])->group(function () {
    // Route::get('/admin/dashboard', function () {
    //     if (Auth::user()->role !== 'admin') {
    //         return redirect()->route('dashboard1'); // Redirect non-admin users
    //     }
    //     return view('admin.dashboard'); // Admin dashboard view
    // })->name('admin.dashboard');
});

// Seller Routes
Route::middleware(['auth'])->prefix('seller')->name('seller.')->group(function () {
    // Earnings Routes
    Route::prefix('earnings')->name('earnings.')->group(function () {
        Route::get('/', [App\Http\Controllers\Seller\EarningsController::class, 'index'])->name('index');
        Route::get('/history', [App\Http\Controllers\Seller\EarningsController::class, 'history'])->name('history');
        Route::post('/request-payout', [App\Http\Controllers\Seller\EarningsController::class, 'requestPayout'])->name('request-payout');
        Route::get('/payouts/{payoutRequest}', [App\Http\Controllers\Seller\EarningsController::class, 'showPayout'])->name('show-payout');
    });
});

// Admin Routes
Route::middleware(['auth', AdminMiddleware::class])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/approved', [AdminDashboardController::class, 'approved'])->name('approved');

    // Payout Management Routes
    Route::prefix('payouts')->name('payouts.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\PayoutController::class, 'index'])->name('index');
        Route::get('/history', [App\Http\Controllers\Admin\PayoutController::class, 'history'])->name('history');
        Route::get('/seller-balances', [App\Http\Controllers\Admin\PayoutController::class, 'sellerBalances'])->name('seller-balances');
        Route::get('/{payoutRequest}', [App\Http\Controllers\Admin\PayoutController::class, 'show'])->name('show');
        Route::post('/{payoutRequest}/approve', [App\Http\Controllers\Admin\PayoutController::class, 'approve'])->name('approve');
        Route::post('/{payoutRequest}/complete', [App\Http\Controllers\Admin\PayoutController::class, 'complete'])->name('complete');
        Route::post('/{payoutRequest}/reject', [App\Http\Controllers\Admin\PayoutController::class, 'reject'])->name('reject');
    });
});

// Route::get('/admin/dashboard', function () {
//     // Ensure the user is authenticated and has an admin role
//     if (!Auth::check() || Auth::user()->role !== 'admin') {
//         abort(403, 'Unauthorized access');
//     }

//     // Fetch all products for the admin dashboard
//     $products = \App\Models\Product::all();
    
//     // Pass the products to the view
//     return view('admin.dashboard', ['products' => $products]);
// })->middleware('auth')->name('admin.dashboard');

Route::get('/dashboard1', function () {
    return view('dashboard1');
})->middleware(['auth'])->name('dashboard');

require __DIR__.'/auth.php';

use App\Http\Controllers\ChatController;

// Chat Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/conversations', [ChatController::class, 'listConversations'])->name('chat.index');
    Route::get('/conversations/{conversation}', [ChatController::class, 'showConversation'])->name('chat.show');
    Route::get('/products/{id}/chat', [ChatController::class, 'startConversation'])->name('chat.start.product');
    Route::get('/bundles/{id}/chat', [ChatController::class, 'startConversation'])->name('chat.start.bundle');
    Route::post('/conversations/{conversation}/messages', [ChatController::class, 'sendMessage'])->name('chat.message');
});

// Test route for chat view
Route::get('/test-chat/{conversation}', function(Conversation $conversation) {
    return view('chat.show', ['conversation' => $conversation, 'messages' => $conversation->messages]);
})->middleware(['auth'])->name('test.chat.show');

// Bank Transfer Routes
Route::middleware(['auth'])->group(function () {
    Route::post('/bank-transfer/{conversation}', [BankTransferController::class, 'store'])->name('bank-transfer.store');
    Route::post('/bank-transfer/confirm/{conversation}', [BankTransferController::class, 'confirm'])->name('bank-transfer.confirm');
    Route::post('/bank-transfer/reject/{conversation}', [BankTransferController::class, 'reject'])->name('bank-transfer.reject');
});

// Notification Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/notifications', function () {
        // Mark all notifications as read
        auth()->user()->unreadNotifications->markAsRead();
        return view('notifications.index');
    })->name('notifications.index');
});

// Payment Management Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/payments/manage', [PaymentManagementController::class, 'index'])->name('payments.manage');
});

// Role-Based Redirection
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        if (Auth::user()->role === 'admin') {
            return redirect()->route('admin.dashboard'); // Admin users are redirected to the admin dashboard
        }
        return view('dashboard1'); // Customer users see the customer dashboard
    })->name('dashboard');

    // Admin-specific dashboard route
    // Route::get('admin/dashboard', function () {
    //     if (Auth::user()->role !== 'admin') {
    //         return redirect()->route('dashboard'); // Non-admin users are redirected to the customer dashboard
    //     }
    //     return view('admin.dashboard'); // Admin dashboard view
    // })->name('admin.dashboard');
});