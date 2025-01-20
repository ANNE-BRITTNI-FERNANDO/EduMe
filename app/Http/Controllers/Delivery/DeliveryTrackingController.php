<?php

namespace App\Http\Controllers\Delivery;

use App\Http\Controllers\Controller;
use App\Models\DeliveryTracking;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\DeliveryStatusUpdated;

class DeliveryTrackingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = auth()->user();
        $trackings = [];
        
        if ($user->role === 'seller') {
            // Get deliveries for seller's orders
            $trackings = DeliveryTracking::whereHas('order', function ($query) use ($user) {
                $query->where('seller_id', $user->id);
            })->latest()->get();
        } else {
            // Get deliveries for buyer's orders
            $trackings = DeliveryTracking::whereHas('order', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->latest()->get();
        }

        return view('delivery.tracking', compact('trackings'));
    }

    public function show($trackingNumber)
    {
        $tracking = DeliveryTracking::where('tracking_number', $trackingNumber)->firstOrFail();
        
        // Check if user is authorized to view this tracking
        if (auth()->id() !== $tracking->order->user_id && auth()->id() !== $tracking->order->seller_id) {
            abort(403);
        }

        return view('delivery.tracking', compact('tracking'));
    }

    public function update(Request $request, $trackingNumber)
    {
        $tracking = DeliveryTracking::where('tracking_number', $trackingNumber)->firstOrFail();
        
        // Only seller can update tracking
        if (auth()->id() !== $tracking->order->seller_id) {
            abort(403);
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,picked_up,in_transit,out_for_delivery,delivered',
            'current_location' => 'required|string',
            'delivery_notes' => 'nullable|string',
        ]);

        $tracking->update($validated);

        // Create a tracking update record
        $tracking->updates()->create([
            'status' => $validated['status'],
            'location' => $validated['current_location'],
            'description' => $this->getStatusDescription($validated['status']),
            'notes' => $validated['delivery_notes']
        ]);

        // Send email notification
        if ($tracking->order->user) {
            Mail::to($tracking->order->user->email)
                ->queue(new DeliveryStatusUpdated($tracking));
        }

        return back()->with('success', 'Delivery status updated successfully');
    }

    private function getStatusDescription($status)
    {
        return match($status) {
            'pending' => 'Order is pending pickup',
            'picked_up' => 'Order has been picked up',
            'in_transit' => 'Order is in transit',
            'out_for_delivery' => 'Order is out for delivery',
            'delivered' => 'Order has been delivered',
            default => 'Status updated'
        };
    }

    public function adminDashboard()
    {
        // Only admin can access this
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }

        $deliveries = DeliveryTracking::with(['buyer', 'seller'])
            ->latest()
            ->paginate(20);

        return view('admin.delivery-dashboard', compact('deliveries'));
    }
}
