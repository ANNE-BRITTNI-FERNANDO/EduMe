<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Stripe\Stripe;
use Stripe\PaymentIntent;

class DeliveryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');  // Ensure user is authenticated
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    public function dashboard()
    {
        $user = Auth::user();
        $deliveries = [];
        
        if ($user->role === 'seller') {
            // Get deliveries for seller's orders
            $deliveries = Delivery::whereHas('order', function ($query) use ($user) {
                $query->where('seller_id', $user->id);
            })->latest()->get();
        } else {
            // Get deliveries for buyer's orders
            $deliveries = Delivery::whereHas('order', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->latest()->get();
        }

        return view('deliveries.dashboard', compact('deliveries'));
    }

    public function create()
    {
        return view('deliveries.create');
    }

    public function calculateFee(Request $request)
    {
        $validated = $request->validate([
            'sender_address' => 'required|string',
            'receiver_address' => 'required|string',
            'package_weight' => 'nullable|numeric|min:0',
            'delivery_type' => 'required|in:standard,express',
        ]);

        $distance = Delivery::calculateDistance(
            $validated['sender_address'],
            $validated['receiver_address']
        );

        $fee = Delivery::calculateDeliveryFee(
            $distance,
            $validated['package_weight'],
            $validated['delivery_type']
        );

        $estimatedHours = Delivery::estimateDeliveryTime(
            $distance,
            $validated['delivery_type']
        );

        return response()->json([
            'fee' => $fee,
            'distance' => $distance,
            'estimated_hours' => $estimatedHours,
            'amount_cents' => $fee * 100 // For Stripe
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sender_name' => 'required|string|max:255',
            'sender_phone' => ['required', 'regex:/^(?:\+94|0)[0-9]{9}$/'], // Sri Lankan phone format
            'sender_address' => 'required|string',
            'receiver_name' => 'required|string|max:255',
            'receiver_phone' => ['required', 'regex:/^(?:\+94|0)[0-9]{9}$/'],
            'receiver_address' => 'required|string',
            'package_description' => 'required|string',
            'package_weight' => 'nullable|numeric|min:0',
            'delivery_type' => 'required|in:standard,express',
            'payment_intent_id' => 'required|string'
        ]);

        try {
            // Verify payment intent
            $paymentIntent = PaymentIntent::retrieve($validated['payment_intent_id']);
            
            if ($paymentIntent->status !== 'succeeded') {
                return response()->json([
                    'error' => 'Payment has not been completed'
                ], 400);
            }

            // Create delivery
            $delivery = Delivery::create($validated);

            // Create order record
            $order = new Order();
            $order->user_id = Auth::id();
            $order->item_id = $delivery->id;
            $order->item_type = 'delivery';
            $order->total_amount = $delivery->delivery_fee;
            $order->payment_method = 'stripe';
            $order->payment_status = 'completed';
            $order->save();

            return redirect()->route('deliveries.track', $delivery->tracking_number)
                ->with('success', 'Delivery scheduled successfully! Your tracking number is: ' . $delivery->tracking_number);

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to process delivery: ' . $e->getMessage()]);
        }
    }

    public function track($trackingNumber)
    {
        $delivery = Delivery::where('tracking_number', $trackingNumber)->firstOrFail();
        return view('deliveries.track', compact('delivery'));
    }

    public function createPaymentIntent(Request $request)
    {
        try {
            $validated = $request->validate([
                'amount_cents' => 'required|integer|min:100',
            ]);

            $paymentIntent = PaymentIntent::create([
                'amount' => $validated['amount_cents'],
                'currency' => 'lkr', // Sri Lankan Rupees
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
            ]);

            return response()->json([
                'clientSecret' => $paymentIntent->client_secret,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
