<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SellerMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check()) {
            $user = auth()->user();
            
            // Check if user is a customer (which can be either seller or buyer)
            if ($user->role === 'customer') {
                // Check if they're in seller mode
                if (session('user_mode') === 'seller') {
                    return $next($request);
                }
            }
            
            // If not in seller mode or not a customer, redirect to home
            return redirect()->route('home')->with('error', 'Please switch to seller mode to access this page.');
        }

        return redirect()->route('login');
    }
}
