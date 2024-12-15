<?php

namespace App\Http\Controllers;

use App\Models\PayoutRequest;
use Illuminate\Http\Request;

class SellerEarningsController extends Controller
{
    public function toggleDetails(PayoutRequest $payout)
    {
        // Toggle the expanded state in session
        $sessionKey = 'expanded_payout_' . $payout->id;
        if (session($sessionKey)) {
            session()->forget($sessionKey);
        } else {
            session([$sessionKey => true]);
        }
        
        return back();
    }
}
