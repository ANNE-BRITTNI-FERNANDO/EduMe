<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WarehouseController extends Controller
{
    public function index()
    {
        $warehouses = Warehouse::all();
        return view('warehouses.index', compact('warehouses'));
    }

    public function show(Warehouse $warehouse)
    {
        // Get pending deliveries for sellers
        $pendingDeliveries = [];
        if (Auth::user()->role === 'seller') {
            $pendingDeliveries = Auth::user()
                ->products()
                ->whereHas('cartItems', function ($query) use ($warehouse) {
                    $query->where('status', 'pending')
                        ->whereHas('order', function ($q) use ($warehouse) {
                            $q->where('warehouse_id', $warehouse->id);
                        });
                })->get();
        }

        // Get items ready for pickup for buyers
        $readyForPickup = [];
        if (Auth::user()->role === 'buyer') {
            $readyForPickup = Auth::user()
                ->orders()
                ->where('warehouse_id', $warehouse->id)
                ->where('status', 'ready_for_pickup')
                ->get();
        }

        return view('warehouses.show', compact('warehouse', 'pendingDeliveries', 'readyForPickup'));
    }

    public function map()
    {
        $warehouses = Warehouse::all();
        return view('warehouses.map', compact('warehouses'));
    }

    // Admin methods
    public function create()
    {
        $this->authorize('manage-warehouses');
        return view('warehouses.create');
    }

    public function store(Request $request)
    {
        $this->authorize('manage-warehouses');
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'address' => 'required|string',
            'contact_number' => 'required|string',
            'opening_time' => 'required|date_format:H:i',
            'closing_time' => 'required|date_format:H:i|after:opening_time',
            'pickup_available' => 'boolean',
        ]);

        Warehouse::create($validated);

        return redirect()->route('warehouses.index')
            ->with('success', 'Warehouse created successfully');
    }

    public function edit(Warehouse $warehouse)
    {
        $this->authorize('manage-warehouses');
        return view('warehouses.edit', compact('warehouse'));
    }

    public function update(Request $request, Warehouse $warehouse)
    {
        $this->authorize('manage-warehouses');
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'address' => 'required|string',
            'contact_number' => 'required|string',
            'opening_time' => 'required|date_format:H:i',
            'closing_time' => 'required|date_format:H:i|after:opening_time',
            'pickup_available' => 'boolean',
        ]);

        $warehouse->update($validated);

        return redirect()->route('warehouses.index')
            ->with('success', 'Warehouse updated successfully');
    }

    public function destroy(Warehouse $warehouse)
    {
        $this->authorize('manage-warehouses');
        
        $warehouse->delete();

        return redirect()->route('warehouses.index')
            ->with('success', 'Warehouse deleted successfully');
    }
}
