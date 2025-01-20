<?php

// app/Http/Controllers/Shop/ShopController.php
namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Bundle; // Import your Bundle model

class ShopController extends Controller
{
    public function showBundles()
    {
        // Retrieve bundles from the database
        $bundles = Bundle::all(); // Or use other query methods as needed

        // Pass the bundles variable to the view
        return view('shop.bundles', compact('bundles'));
    }
}
