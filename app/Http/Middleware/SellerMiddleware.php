<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SellerMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();
        
        // Check if the user has any seller-related attributes
        $isSeller = $user->products()->exists() || 
                    $user->bundles()->exists() || 
                    $user->sellerBalance()->exists();

        if (!$isSeller) {
            abort(403, 'Access denied. Seller only area.');
        }

        return $next($request);
    }
}
