<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserRoleController extends Controller
{
    public function setAsSeller()
    {
        $user = auth()->user();
        
        // Set the user as a seller
        DB::table('users')
            ->where('id', $user->id)
            ->update([
                'is_seller' => true,
                'role' => 'customer'
            ]);

        return redirect('/seller/dashboard');
    }

    public function setAsBuyer()
    {
        $user = auth()->user();
        
        // Keep is_seller status but set role as customer
        DB::table('users')
            ->where('id', $user->id)
            ->update(['role' => 'customer']);

        return redirect()->route('productlisting');
    }

    public function setAsDeliveryPartner()
    {
        $user = auth()->user();
        DB::table('users')
            ->where('id', $user->id)
            ->update(['role' => 'delivery']);

        return redirect()->route('delivery.dashboard');
    }
}
