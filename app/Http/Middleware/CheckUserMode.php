<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckUserMode
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check() && auth()->user()->role === 'customer') {
            // If trying to access seller dashboard
            if ($request->routeIs('seller.*')) {
                if (session('user_mode') !== 'seller') {
                    return redirect()->route('home');
                }
            }
            
            // If trying to access buyer routes
            if ($request->routeIs('buyer.*')) {
                if (session('user_mode') !== 'buyer') {
                    return redirect()->route('home');
                }
            }
        }

        return $next($request);
    }
}
