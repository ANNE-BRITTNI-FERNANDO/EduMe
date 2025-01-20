<?php

use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\Admin\AdminBundleController;
use App\Http\Controllers\Admin\PayoutController as AdminPayoutController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminOrderController;
use App\Http\Controllers\Admin\ReportsController;
use App\Http\Controllers\Seller\BundleController;
use App\Http\Controllers\Seller\SellerDashboardController;
use App\Http\Controllers\Seller\SellerOrderController;
use App\Http\Controllers\Seller\SellerEarningsController;
use App\Http\Controllers\UserRoleController;
use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\DeliveryTrackingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PayoutController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\StripeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Seller\SellerRatingController;
use App\Models\Product;
use App\Models\Order;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\BudgetManagementController;
use App\Http\Controllers\BuyerDashboardController;
use App\Http\Controllers\Admin\AdminDonationController;
use App\Http\Controllers\DonorController;

Route::get('/', function () {
    return view('home');
});

Route::get('/home', function () {
    return view('home');
})->name('home');

Route::get('/doner', function () {
    return view('doner');
})->name('doner');

Route::get('/product/{product}', [ProductController::class, 'show'])->name('product.show');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard1', function () {
        return view('dashboard1');
    })->name('dashboard1');

    // User role routes
    Route::middleware(['auth'])->group(function () {
        Route::post('/set-as-seller', [UserRoleController::class, 'setAsSeller'])->name('set.seller');
        Route::post('/set-as-buyer', [UserRoleController::class, 'setAsBuyer'])->name('set.buyer');
        Route::post('/set-as-delivery', [UserRoleController::class, 'setAsDeliveryPartner'])->name('set.delivery');
    });

    // Seller routes with single middleware group
    Route::middleware(['auth'])->group(function () {
        Route::prefix('seller')->name('seller.')->group(function () {
            Route::get('/dashboard', [SellerDashboardController::class, 'index'])->name('dashboard');
            
            // Product routes
            Route::get('/products', [ProductController::class, 'sellerProducts'])->name('products.index');
            Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
            Route::post('/products', [ProductController::class, 'store'])->name('products.store');
            Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
            Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
            Route::get('/products/{product}/resubmit', [ProductController::class, 'resubmitForm'])->name('products.resubmit.form');
            Route::post('/products/{product}/resubmit', [ProductController::class, 'resubmit'])->name('products.resubmit');
            Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
            Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
            Route::delete('/products/images/{image}', [ProductController::class, 'deleteImage'])->name('products.images.delete');

            Route::get('/orders', [SellerOrderController::class, 'index'])->name('orders.index');
            Route::get('/orders/{order}', [SellerOrderController::class, 'show'])->name('orders.show');
            Route::post('/orders/{order}/status', [SellerOrderController::class, 'updateDeliveryStatus'])->name('orders.update.status');
            Route::get('/earnings', [SellerEarningsController::class, 'index'])->name('earnings.index');
            Route::post('/earnings/request-payout', [SellerEarningsController::class, 'requestPayout'])->name('earnings.request-payout');
            Route::post('/earnings/toggle-details', [SellerEarningsController::class, 'toggleDetails'])->name('earnings.toggle-details');
            Route::get('/earnings/download-receipt/{payout}', [SellerEarningsController::class, 'downloadReceipt'])->name('earnings.download-receipt');
            Route::get('/bundles', [BundleController::class, 'index'])->name('bundles.index');
            Route::get('/bundles/create', [BundleController::class, 'create'])->name('bundles.create');
            Route::post('/bundles', [BundleController::class, 'store'])->name('bundles.store');
            Route::get('/bundles/{bundle}/edit', [BundleController::class, 'edit'])->name('bundles.edit');
            Route::put('/bundles/{bundle}', [BundleController::class, 'update'])->name('bundles.update');
            Route::delete('/bundles/{bundle}', [BundleController::class, 'destroy'])->name('bundles.destroy');
        });
    });

    // All Admin routes grouped together
    Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', function() {
            if (auth()->user()->role !== 'admin') {
                abort(403, 'Access denied. Admin only area.');
            }
            return app(AdminDashboardController::class)->index();
        })->name('dashboard');

        // Payout Routes
        Route::get('/payouts', [AdminPayoutController::class, 'index'])->name('payouts.index');
        Route::post('/payouts/{payout}/approve', [AdminPayoutController::class, 'approve'])->name('payouts.approve');
        Route::post('/payouts/{payout}/reject', [AdminPayoutController::class, 'reject'])->name('payouts.reject');

        // Admin Reports Routes
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [ReportsController::class, 'index'])->name('index');
            Route::get('/sales', [ReportsController::class, 'sales'])->name('sales');
            Route::get('/users', [ReportsController::class, 'users'])->name('users');
            Route::get('/products', [ReportsController::class, 'products'])->name('products');
            Route::get('/sellers', [ReportsController::class, 'sellers'])->name('sellers');
            Route::get('/download/{type}', [ReportsController::class, 'downloadReport'])->name('download');
        });

        // Admin Dashboard Routes
        Route::get('/approved', [AdminDashboardController::class, 'approved'])->name('approved');
        Route::get('/pending', [AdminDashboardController::class, 'pending'])->name('pending');
        Route::get('/rejected', [AdminDashboardController::class, 'rejected'])->name('rejected');
        
        // Products
        Route::post('/products/{id}/approve', [AdminDashboardController::class, 'approveProduct'])->name('products.approve');
        Route::post('/products/{id}/reject', [AdminDashboardController::class, 'rejectProduct'])->name('products.reject');
        
        // Bundles
        Route::get('/bundles', [AdminBundleController::class, 'index'])->name('bundles.index');
        Route::get('/bundles/{bundle}', [AdminBundleController::class, 'show'])->name('bundles.show');
        Route::get('/bundles/approved', [AdminBundleController::class, 'approvedBundles'])->name('bundles.approved');
        Route::post('/bundles/{id}/update-status', [AdminBundleController::class, 'updateStatus'])->name('bundles.updateStatus');
        
        // Orders
        Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
        Route::post('/orders/{order}/status', [AdminOrderController::class, 'updateStatusApi'])->name('orders.update-status');
        
        // Payouts
        Route::prefix('payouts')->name('payouts.')->group(function () {
            Route::get('/', [AdminPayoutController::class, 'index'])->name('index');
            Route::get('/history', [AdminPayoutController::class, 'history'])->name('history');
            Route::get('/seller-balances', [AdminPayoutController::class, 'sellerBalances'])->name('seller-balances');
            Route::get('/{payoutRequest}', [AdminPayoutController::class, 'show'])->name('show');
            Route::post('/{payoutRequest}/approve', [AdminPayoutController::class, 'approve'])->name('approve');
            Route::post('/{payoutRequest}/reject', [AdminPayoutController::class, 'reject'])->name('reject');
            Route::post('/{payout}/complete', [AdminPayoutController::class, 'complete'])->name('complete');
            Route::post('/{payout}/upload-receipt', [AdminPayoutController::class, 'uploadReceipt'])->name('upload-receipt');
        });

        // Product management routes
        Route::get('/products', [ProductController::class, 'adminIndex'])->name('products.index');
        Route::get('/products/{product}', [ProductController::class, 'adminShow'])->name('products.show');
        Route::post('/products/{id}/approve', [AdminDashboardController::class, 'approveProduct'])->name('products.approve');
        Route::post('/products/{id}/reject', [AdminDashboardController::class, 'rejectProduct'])->name('products.reject');

        Route::post('/sellers/{seller}/recalculate-balance', function(Request $request, $seller) {
            $sellerBalance = \App\Models\SellerBalance::where('seller_id', $seller)->firstOrFail();
            $sellerBalance->recalculateBalance();
            return back()->with('success', 'Balance recalculated successfully');
        })->name('admin.sellers.recalculate-balance');

        // Donations management
        Route::prefix('donations')->name('donations.')->group(function () {
            Route::get('/', [AdminDonationController::class, 'index'])->name('index');
            Route::get('/requests', [AdminDonationController::class, 'requests'])->name('requests');
            Route::post('/{donation}/approve', [AdminDonationController::class, 'approveDonation'])->name('approve');
            Route::post('/{donation}/reject', [AdminDonationController::class, 'rejectDonation'])->name('reject');
            Route::post('/requests/{donationRequest}/approve', [AdminDonationController::class, 'approveRequest'])->name('requests.approve');
            Route::post('/requests/{donationRequest}/reject', [AdminDonationController::class, 'rejectRequest'])->name('requests.reject');
            Route::delete('/{donation}', [AdminDonationController::class, 'deleteDonation'])->name('delete');
            Route::post('/{donation}/remove', [AdminDonationController::class, 'removeDonation'])->name('remove');
        });

        // Ratings management
        Route::prefix('ratings')->name('ratings.')->group(function () {
            Route::get('/', [SellerRatingController::class, 'adminIndex'])->name('index');
            Route::delete('/{id}', [SellerRatingController::class, 'delete'])->name('delete');
        });
    });

    // Cart routes
    Route::middleware(['auth'])->group(function () {
        Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
        Route::post('/cart/add/{product}', [CartController::class, 'addProduct'])->name('cart.add');
        Route::post('/cart/add-bundle/{bundle}', [CartController::class, 'addBundle'])->name('cart.add.bundle');
        Route::delete('/cart/remove/{item}', [CartController::class, 'removeItem'])->name('cart.remove');
        Route::get('/cart/get-districts/{province}', [CartController::class, 'getDistricts'])->name('cart.districts');
        Route::post('/cart/update-delivery', [CartController::class, 'updateDeliveryFee'])->name('cart.update.delivery');
        Route::post('/cart/checkout', [CartController::class, 'checkout'])->name('cart.checkout');
    });

    // Seller rating routes
    Route::middleware(['auth', 'web'])->group(function () {
        Route::post('/seller/ratings/{order}', [SellerRatingController::class, 'store'])->name('seller.ratings.store');
    });

    // Stripe payment routes
    Route::post('/stripe/checkout', [StripeController::class, 'checkout'])->name('stripe.checkout');
    Route::get('/stripe/success', [StripeController::class, 'success'])->name('stripe.success');
    Route::get('/stripe/cancel', [StripeController::class, 'cancel'])->name('stripe.cancel');
    Route::post('/webhook/stripe', [App\Http\Controllers\PaymentController::class, 'handleStripeWebhook'])->name('stripe.webhook');

    Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
});

// Chat routes
Route::get('/conversations', [ChatController::class, 'listConversations'])->name('chat.list');
Route::get('/chat/with-seller/{id}', [ChatController::class, 'startConversation'])->name('chat-with-seller');
Route::get('/chat/{conversation}', [ChatController::class, 'showConversation'])->name('chat.show');
Route::get('/messages/{conversation}', [ChatController::class, 'getMessages'])->name('messages.get');
Route::post('/messages', [ChatController::class, 'store'])->name('messages.store');
Route::post('/messages/{conversation}/mark-read', [ChatController::class, 'markAllRead'])->name('messages.markAllRead');

// Profile management routes
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Stripe Payment Routes
Route::post('/checkout', [StripeController::class, 'checkout'])->name('stripe.checkout');
Route::get('/payment/success', [StripeController::class, 'success'])->name('stripe.success');
Route::get('/payment/cancel', [StripeController::class, 'cancel'])->name('stripe.cancel');

// Order routes
Route::middleware(['verified'])->group(function () {
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/checkout', [OrderController::class, 'checkout'])->name('checkout');
    Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');

    // Seller-specific routes
    Route::post('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.updateStatus');
});

// Route for approving a product
Route::post('/admin/product/approve/{id}', function ($id) {
    $product = \App\Models\Product::findOrFail($id);
    $product->update([
        'is_approved' => true,
        'approved_at' => now()
    ]);

    return redirect()->route('admin.dashboard')->with('success', 'Product approved successfully.');
})->name('product.approve');

// Route for rejecting a product
Route::post('/admin/product/reject/{id}', function ($id) {
    $product = \App\Models\Product::findOrFail($id);
    $product->delete(); // Soft delete the product

    return redirect()->route('admin.dashboard')->with('error', 'Product rejected and removed.');
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

// View route for the product listing page with advanced filtering
Route::get('/productlisting', [ProductController::class, 'listApprovedProducts'])->name('productlisting');

// Additional routes for product approval/rejection
Route::post('/products/{id}/approve', [ProductController::class, 'approve'])->name('products.approve');
Route::post('/products/{id}/reject', [ProductController::class, 'reject'])->name('products.reject');

Route::get('/product/edit/{id}', [ProductController::class, 'edit'])->name('product.edit');
Route::put('/product/{id}', [ProductController::class, 'update'])->name('product.update');
Route::delete('/product/{product}', [ProductController::class, 'destroy'])->name('product.destroy');

// routes/web.php

Route::get('/productlisting', [ProductController::class, 'listApprovedProducts'])->name('productlisting');

// In routes/web.php

// Route::get('/products/approved', [ProductController::class, 'listApprovedProducts'])->name('products.approved');
Route::get('/approved-products/filter', [ProductController::class, 'filterApprovedProducts'])->name('approved.products.filter');
Route::get('/products/filter', [ProductController::class, 'filterApprovedProducts'])->name('products.filter');

Route::get('/product/{id}', [ProductController::class, 'show'])->name('product.show');

// Bundle routes
Route::middleware(['auth'])->group(function () {
    Route::get('/bundles', [BundleController::class, 'index'])->name('bundles.list');
    Route::get('/bundles/create', [BundleController::class, 'create'])->name('bundles.new');
    Route::post('/bundles', [BundleController::class, 'store'])->name('bundles.save');
});

// Admin Bundle routes
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/bundles', [AdminBundleController::class, 'index'])->name('bundles.index');
    Route::get('/bundles/{bundle}', [AdminBundleController::class, 'show'])->name('bundles.show');
    Route::get('/bundles/approved', [AdminBundleController::class, 'approvedBundles'])->name('bundles.approved');
    Route::post('/bundles/{id}/update-status', [AdminBundleController::class, 'updateStatus'])->name('bundles.updateStatus');
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
Route::get('/shop/bundles', [ShopBundleController::class, 'index'])->name('shop.bundles');
Route::get('/shop/bundles/{bundle}', [ShopBundleController::class, 'show'])->name('bundles.show');

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

// Cart and Checkout Routes
Route::middleware(['auth'])->group(function () {
    // Cart Routes
    // Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    // Route::post('/cart/add/{product}', [CartController::class, 'add'])->name('cart.add');
    // Route::delete('/cart/remove/{cartItem}', [CartController::class, 'remove'])->name('cart.remove');
    // Route::patch('/cart/{cartItem}/delivery', [CartController::class, 'updateDelivery'])->name('cart.updateDelivery');
    
    // Checkout Routes
    Route::get('/checkout', [PaymentController::class, 'checkout'])->name('checkout');
    Route::post('/checkout/process', [PaymentController::class, 'process'])->name('checkout.process');
});

Route::delete('/product/{id}', [ProductController::class, 'destroy'])->name('product.destroy');

Route::get('/products', [ProductController::class, 'index'])->name('products.index');

Route::get('/products', [ProductController::class, 'index'])->name('product.index');

// Other routes...

Route::delete('/product/{id}', [ProductController::class, 'destroy'])->name('product.destroy');

// Order routes
Route::middleware(['auth'])->group(function () {
    // Customer routes (both buyer and seller views)
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}/confirm-delivery', [OrderController::class, 'confirmDelivery'])->name('orders.confirm-delivery');
    Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.update-status');
    Route::patch('/orders/{order}/delivery-status', [OrderController::class, 'updateDeliveryStatus'])->name('orders.update-delivery-status');
    Route::get('/warehouses/nearby', [OrderController::class, 'getNearbyWarehouses'])->name('warehouses.nearby');
});

// Admin order routes
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/orders/{order}', [OrderController::class, 'adminShow'])->name('orders.show');
});

// Chat Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/conversations', [ChatController::class, 'listConversations'])->name('chat.index');
    Route::get('/conversations/{conversation}', [ChatController::class, 'showConversation'])->name('chat.show');
    Route::get('/products/{id}/chat', [ChatController::class, 'startConversation'])->name('chat.start.product');
    Route::get('/bundles/{bundle}/chat', [ChatController::class, 'startBundleConversation'])->name('chat.start.bundle');
    Route::post('/conversations/{conversation}/messages', [ChatController::class, 'sendMessage'])->name('chat.message');
});

Route::get('/products', [ProductController::class, 'index'])->name('products.index');

Route::delete('/products/{id}', [ProductController::class, 'destroy'])->name('product.destroy');
Route::post('/admin/bundles/{id}/updateStatus', [BundleController::class, 'updateStatus']);

Route::middleware(['auth'])->group(function () {
    // Route::get('/admin/dashboard', function () {
    //     if (Auth::user()->role !== 'admin') {
    //         return redirect()->route('dashboard'); // Redirect non-admin users
    //     }
    //     return view('admin.dashboard'); // Admin dashboard view
    // })->name('admin.dashboard');
});

Route::middleware(['auth'])->prefix('seller')->name('seller.')->group(function () {
    // Route::get('/dashboard', function() {
    //     $user = auth()->user();
    //     $products = $user->products;
        
    //     return view('seller.dashboard', compact('products'));
    // })->name('dashboard');
    
    // Orders
    Route::get('/orders', [SellerOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [SellerOrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}/status', [SellerOrderController::class, 'updateDeliveryStatus'])->name('orders.update.status');
    // Earnings
    Route::get('/earnings', [SellerEarningsController::class, 'index'])->name('earnings.index');
    Route::post('/earnings/toggle-details', [SellerEarningsController::class, 'toggleDetails'])->name('earnings.toggle-details');
    Route::get('/earnings/download-receipt/{payout}', [SellerEarningsController::class, 'downloadReceipt'])->name('earnings.download-receipt');
});

// Buyer routes
Route::middleware(['auth'])->prefix('buyer')->name('buyer.')->group(function () {
    Route::get('/dashboard', [BuyerDashboardController::class, 'index'])->name('dashboard');
    
    // Budget management routes
    Route::prefix('budget')->name('budget.')->group(function () {
        Route::get('/', [BudgetManagementController::class, 'index'])->name('index');
        Route::post('/set', [BudgetManagementController::class, 'setBudget'])->name('set');
        Route::post('/update', [BudgetManagementController::class, 'update'])->name('update');
    });
});

// Warehouse Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/warehouses', [WarehouseController::class, 'index'])->name('warehouses.index');
    Route::get('/warehouses/map', [WarehouseController::class, 'map'])->name('warehouses.map');
    Route::get('/warehouses/{warehouse}', [WarehouseController::class, 'show'])->name('warehouses.show');
    
    // Admin only routes
    Route::middleware(['can:manage-warehouses'])->group(function () {
        Route::get('/warehouses/create', [WarehouseController::class, 'create'])->name('warehouses.create');
        Route::post('/warehouses', [WarehouseController::class, 'store'])->name('warehouses.store');
        Route::get('/warehouses/{warehouse}/edit', [WarehouseController::class, 'edit'])->name('warehouses.edit');
        Route::put('/warehouses/{warehouse}', [WarehouseController::class, 'update'])->name('warehouses.update');
        Route::delete('/warehouses/{warehouse}', [WarehouseController::class, 'destroy'])->name('warehouses.destroy');
    });
});

// Warehouse Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/warehouses', [WarehouseController::class, 'index'])->name('warehouses.index');
    Route::get('/warehouses/map', [WarehouseController::class, 'map'])->name('warehouses.map');
    Route::get('/warehouses/{warehouse}', [WarehouseController::class, 'show'])->name('warehouses.show');
    
    // Admin only routes
    Route::middleware(['can:manage-warehouses'])->group(function () {
        Route::get('/warehouses/create', [WarehouseController::class, 'create'])->name('warehouses.create');
        Route::post('/warehouses', [WarehouseController::class, 'store'])->name('warehouses.store');
        Route::get('/warehouses/{warehouse}/edit', [WarehouseController::class, 'edit'])->name('warehouses.edit');
        Route::put('/warehouses/{warehouse}', [WarehouseController::class, 'update'])->name('warehouses.update');
        Route::delete('/warehouses/{warehouse}', [WarehouseController::class, 'destroy'])->name('warehouses.destroy');
    });
});

// Notification routes
Route::middleware(['auth'])->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-as-read');
    Route::post('/notifications/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-as-read');
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

Route::middleware(['auth'])->group(function () {
    Route::get('/deliveries/create', [DeliveryController::class, 'create'])->name('deliveries.create');
    Route::post('/deliveries', [DeliveryController::class, 'store'])->name('deliveries.store');
    Route::get('/deliveries/track/{tracking_number}', [DeliveryController::class, 'track'])->name('deliveries.track');
});

// Delivery Tracking Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/delivery/track/{tracking_number}', [DeliveryTrackingController::class, 'show'])->name('delivery.track');
    Route::patch('/delivery/update/{tracking_number}', [DeliveryTrackingController::class, 'update'])->name('delivery.update');
    Route::get('/admin/delivery-dashboard', [DeliveryTrackingController::class, 'adminDashboard'])->name('admin.delivery-dashboard');
});

// Define the delivery tracking route
Route::middleware(['auth'])->group(function () {
    Route::get('/delivery/tracking', [DeliveryTrackingController::class, 'index'])->name('delivery.tracking');
});

// Checkout Process Route
Route::post('/checkout/process', [PaymentController::class, 'processCheckout'])->name('checkout.process');
Route::get('/checkout', [PaymentController::class, 'showCheckout'])->name('checkout.show');

// Route for starting a conversation with the seller
Route::middleware(['auth'])->group(function () {
    Route::get('/chat/start/{product}', [ChatController::class, 'startConversation'])->name('chat.start');
});

// Stripe Routes
Route::middleware(['auth'])->group(function () {
    Route::post('/checkout', [StripeController::class, 'checkout'])->name('stripe.checkout');
    Route::get('/checkout/success', [StripeController::class, 'success'])->name('checkout.success');
    Route::get('/checkout/cancel', [StripeController::class, 'cancel'])->name('checkout.cancel');
});

require __DIR__.'/auth.php';

// Chat Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/conversations', [ChatController::class, 'listConversations'])->name('chat.index');
    Route::get('/conversations/{conversation}', [ChatController::class, 'showConversation'])->name('chat.show');
    Route::get('/products/{id}/chat', [ChatController::class, 'startConversation'])->name('chat.start.product');
    Route::get('/bundles/{bundle}/chat', [ChatController::class, 'startBundleConversation'])->name('chat.start.bundle');
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

// Product routes
Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
Route::post('/products', [ProductController::class, 'store'])->name('products.store');
Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
Route::get('/products/{product}/resubmit', [ProductController::class, 'resubmitForm'])->name('products.resubmit.form');
Route::post('/products/{product}/resubmit', [ProductController::class, 'resubmit'])->name('products.resubmit');
Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');

// View route for bundles
Route::get('/bundles', function() {
    return view('bundles.show');
})->name('bundles.show');

// Other routes...

// Shop bundle routes
Route::get('/shop/bundles', [ShopBundleController::class, 'index'])->name('shop.bundles');
Route::get('/shop/bundles/{bundle}', [ShopBundleController::class, 'show'])->name('bundles.show');

// Delivery routes
Route::middleware(['auth'])->prefix('delivery')->name('delivery.')->group(function () {
    Route::get('/', [DeliveryController::class, 'dashboard'])->name('dashboard');
    Route::get('/create', [DeliveryController::class, 'create'])->name('create');
    Route::post('/calculate-fee', [DeliveryController::class, 'calculateFee'])->name('calculate-fee');
    Route::post('/store', [DeliveryController::class, 'store'])->name('store');
    Route::get('/track/{delivery}', [DeliveryController::class, 'track'])->name('track');
    Route::get('/warehouses/map', [WarehouseController::class, 'map'])->name('warehouses.map');
    Route::get('/tracking', [DeliveryTrackingController::class, 'index'])->name('tracking');
    Route::get('/tracking/{trackingNumber}', [DeliveryTrackingController::class, 'show'])->name('tracking.show');
    Route::post('/tracking/{trackingNumber}/update', [DeliveryTrackingController::class, 'update'])->name('tracking.update');
});

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/approved', function() {
        $products = \App\Models\Product::all();
        return view('admin.approved', compact('products'));
    })->name('approved');
});

// Seller Rating Routes
Route::post('/seller/ratings/{order}', [SellerRatingController::class, 'store'])->name('seller.ratings.store');
Route::get('/seller/ratings', [SellerRatingController::class, 'showSellerRatings'])->name('seller.ratings.index');
Route::get('/admin/ratings', [SellerRatingController::class, 'showAdminRatings'])->name('admin.ratings.index');

// routes/web.php

Route::get('/seller/orders/{order}', function($orderId) {
    $order = \App\Models\Order::with([
        'items' => function($query) {
            $query->with('item');
        }, 
        'user', 
        'items.seller'
    ])->findOrFail($orderId);
    
    return view('seller.orders.show', compact('order'));
})->name('seller.orders.show');

Route::middleware(['auth'])->prefix('seller')->name('seller.')->group(function () {
    // Add any additional seller routes here
});

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    // admin routes here
});

// Payment webhook
Route::post('/stripe/webhook', [PaymentController::class, 'handleStripeWebhook'])->name('stripe.webhook');

Route::post('/webhook/stripe', [StripeController::class, 'handleWebhook'])->name('stripe.webhook');

// Delivery Tracking
Route::get('/delivery/tracking/{order}', [DeliveryTrackingController::class, 'show'])->name('delivery.tracking.show');
Route::post('/delivery/tracking/{order}/update', [DeliveryTrackingController::class, 'update'])->name('delivery.tracking.update');

// Donor routes
Route::middleware(['auth'])->prefix('donor')->name('donor.')->group(function () {
    Route::get('/', function() {
        return view('donor.index');
    })->name('index');
    Route::get('/donations', [DonorDonationController::class, 'index'])->name('donations.index');
    Route::get('/donations/create', [DonorDonationController::class, 'create'])->name('donations.create');
    Route::post('/donations', [DonorDonationController::class, 'store'])->name('donations.store');
    Route::get('/donations/{donation}', [DonorDonationController::class, 'show'])->name('donations.show');
    Route::get('/donations/{donation}/edit', [DonorDonationController::class, 'edit'])->name('donations.edit');
    Route::put('/donations/{donation}', [DonorDonationController::class, 'update'])->name('donations.update');
    Route::delete('/donations/{donation}', [DonorDonationController::class, 'destroy'])->name('donations.destroy');
});

Route::get('/donor', [DonorController::class, 'index'])->name('donor.index');
Route::get('/donations/history', [DonorController::class, 'history'])->name('donations.history');

// routes/web.php

Route::get('/donor', [DonorController::class, 'index'])->name('donor.index');
Route::get('/donations/history', [DonorController::class, 'history'])->name('donations.history');

// Donation routes
Route::prefix('donations')->name('donations.')->group(function () {
    Route::get('/', [DonorController::class, 'index'])->name('index');
    Route::get('/create', [DonorController::class, 'create'])->name('create');
    Route::post('/', [DonorController::class, 'store'])->name('store');
    Route::get('/available', [DonorController::class, 'available'])->name('available');
    Route::get('/chat/{request}', [DonorController::class, 'showChat'])->name('donation.chat.show');
    Route::get('/history', [DonorController::class, 'history'])->name('history');
    Route::delete('/{donation}', [DonorController::class, 'destroy'])->name('destroy');
    Route::get('/{donation}/request', [DonorController::class, 'requestForm'])->name('request.form');
    Route::post('/{donation}/request', [DonorController::class, 'storeRequest'])->name('request.store');
});

Route::get('/donor', [DonorController::class, 'index'])->name('donor.index');
Route::get('/donations/history', [DonorController::class, 'history'])->name('donations.history');
Route::post('/donation/chat/{donationRequestId}/send', [DonorController::class, 'sendMessage'])->name('donation.chat.send');
Route::get('/donation/chat/{donationRequestId}/new/{lastMessageId}', [DonorController::class, 'getNewMessages'])->name('donation.chat.new');

// routes/web.php

Route::get('/seller/orders/{order}', function($orderId) {
    $order = \App\Models\Order::with([
        'items' => function($query) {
            $query->with('item');
        }, 
        'user', 
        'items.seller'
    ])->findOrFail($orderId);
    
    return view('seller.orders.show', compact('order'));
})->name('seller.orders.show');

Route::middleware(['auth'])->prefix('seller')->name('seller.')->group(function () {
    // Add any additional seller routes here
});

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    // admin routes here
});

// Payment webhook
Route::post('/stripe/webhook', [PaymentController::class, 'handleStripeWebhook'])->name('stripe.webhook');

Route::post('/webhook/stripe', [StripeController::class, 'handleWebhook'])->name('stripe.webhook');

// Delivery Tracking
Route::get('/delivery/tracking/{order}', [DeliveryTrackingController::class, 'show'])->name('delivery.tracking.show');
Route::post('/delivery/tracking/{order}/update', [DeliveryTrackingController::class, 'update'])->name('delivery.tracking.update');

// Donor routes
Route::middleware(['auth'])->prefix('donor')->name('donor.')->group(function () {
    Route::get('/', function() {
        return view('donor.index');
    })->name('index');
    Route::get('/donations', [DonorDonationController::class, 'index'])->name('donations.index');
    Route::get('/donations/create', [DonorDonationController::class, 'create'])->name('donations.create');
    Route::post('/donations', [DonorDonationController::class, 'store'])->name('donations.store');
    Route::get('/donations/{donation}', [DonorDonationController::class, 'show'])->name('donations.show');
    Route::get('/donations/{donation}/edit', [DonorDonationController::class, 'edit'])->name('donations.edit');
    Route::put('/donations/{donation}', [DonorDonationController::class, 'update'])->name('donations.update');
    Route::delete('/donations/{donation}', [DonorDonationController::class, 'destroy'])->name('donations.destroy');
});

Route::get('/donor', [DonorController::class, 'index'])->name('donor.index');
Route::get('/donations/history', [DonorController::class, 'history'])->name('donations.history');

// routes/web.php

Route::get('/donor', [DonorController::class, 'index'])->name('donor.index');
Route::get('/donations/history', [DonorController::class, 'history'])->name('donations.history');

// Donation routes
Route::prefix('donations')->name('donations.')->group(function () {
    Route::get('/', [DonorController::class, 'index'])->name('index');
    Route::get('/create', [DonorController::class, 'create'])->name('create');
    Route::post('/', [DonorController::class, 'store'])->name('store');
    Route::get('/available', [DonorController::class, 'available'])->name('available');
    Route::get('/chat/{request}', [DonorController::class, 'showChat'])->name('donation.chat.show');
    Route::get('/history', [DonorController::class, 'history'])->name('history');
});

Route::get('/donor', [DonorController::class, 'index'])->name('donor.index');
Route::get('/donations/history', [DonorController::class, 'history'])->name('donations.history');
Route::post('/donation/chat/{donationRequestId}/send', [DonorController::class, 'sendMessage'])->name('donation.chat.send');
Route::get('/donation/chat/{donationRequestId}/new/{lastMessageId}', [DonorController::class, 'getNewMessages'])->name('donation.chat.new');

Route::get('/donor', [DonorController::class, 'index'])->name('donor.index');
Route::get('/donations/history', [DonorController::class, 'history'])->name('donations.history');
Route::post('/donation/chat/{donationRequestId}/send', [DonorController::class, 'sendMessage'])->name('donation.chat.send');
Route::get('/donation/chat/{donationRequestId}/new/{lastMessageId}', [DonorController::class, 'getNewMessages'])->name('donation.chat.new');

Route::get('/donor', [DonorController::class, 'index'])->name('donor.index');
Route::get('/donations/history', [DonorController::class, 'history'])->name('donations.history');
Route::post('/donation/chat/{donationRequestId}/send', [DonorController::class, 'sendMessage'])->name('donation.chat.send');
Route::get('/donation/chat/{donationRequestId}/new/{lastMessageId}', [DonorController::class, 'getNewMessages'])->name('donation.chat.new');

Route::prefix('donations')->name('donations.')->group(function () {
    Route::get('/', [DonorController::class, 'index'])->name('index');
    Route::get('/create', [DonorController::class, 'create'])->name('create');
    Route::post('/', [DonorController::class, 'store'])->name('store');
    Route::get('/available', [DonorController::class, 'available'])->name('available');
    Route::get('/chat/{request}', [DonorController::class, 'showChat'])->name('donation.chat.show');
    Route::get('/history', [DonorController::class, 'history'])->name('history');
});
Route::get('/donor', [DonorController::class, 'index'])->name('donor.index');
Route::get('/donation/chat/{request}', [DonorController::class, 'showChat'])->name('donation.chat.show');