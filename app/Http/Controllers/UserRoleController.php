<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserRoleController extends Controller
{
    public function setAsSeller()
    {
        $user = auth()->user();
        // Since we're using 'customer' role for sellers, we'll just ensure it's set to 'customer'
        DB::table('users')
            ->where('id', $user->id)
            ->update(['role' => 'customer']);

        return redirect()->route('seller.dashboard');
    }

    public function setAsBuyer()
    {
        $user = auth()->user();
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
