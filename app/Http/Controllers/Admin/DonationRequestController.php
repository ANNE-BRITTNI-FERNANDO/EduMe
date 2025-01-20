<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DonationRequest;
use App\Models\DonationItem;
use Illuminate\Http\Request;

class DonationRequestController extends Controller
{
    public function index()
    {
        $itemRequests = DonationRequest::where('type', 'item')
            ->with(['user', 'donationItem'])
            ->latest()
            ->paginate(10);

        $itemStats = [
            'available_count' => DonationItem::where('status', 'available')->count(),
            'pending_requests' => DonationRequest::where('type', 'item')->where('status', 'pending')->count(),
        ];

        $pendingRequests = DonationRequest::with(['user', 'donationItem'])
            ->where('status', 'pending')
            ->where('type', 'item')
            ->latest()
            ->get();

        $approvedRequests = DonationRequest::with(['user', 'donationItem'])
            ->where('status', 'approved')
            ->where('type', 'item')
            ->latest()
            ->get();

        $rejectedRequests = DonationRequest::with(['user', 'donationItem'])
            ->where('status', 'rejected')
            ->where('type', 'item')
            ->latest()
            ->get();

        return view('admin.donations.index', compact('itemRequests', 'itemStats', 'pendingRequests', 'approvedRequests', 'rejectedRequests'));
    }

    public function update(Request $request, DonationRequest $donationRequest)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
        ]);

        try {
            DB::beginTransaction();

            $donationRequest->status = $request->status;
            
            if ($request->status === 'approved') {
                if ($donationRequest->type === 'item') {
                    // Update item donation status and quantity
                    $itemDonation = $donationRequest->donationItem;
                    
                    // Check if the donation item exists and is available
                    if (!$itemDonation || $itemDonation->status !== 'available') {
                        throw new \Exception('Item donation is not available');
                    }

                    // Compare with available_quantity instead of quantity
                    if ($itemDonation->available_quantity < $donationRequest->quantity) {
                        throw new \Exception('Requested quantity (' . $donationRequest->quantity . ') exceeds available quantity (' . $itemDonation->available_quantity . ')');
                    }
                    
                    $itemDonation->available_quantity -= $donationRequest->quantity;
                    if ($itemDonation->available_quantity === 0) {
                        $itemDonation->status = 'allocated';
                    }
                    $itemDonation->save();
                    
                    // Notify users
                    $donationRequest->user->notify(new DonationRequestApproved($donationRequest));
                    $itemDonation->user->notify(new DonationRequestApproved($donationRequest));
                    
                }
            } else {
                // Notify users about rejection
                $donationRequest->user->notify(new DonationRequestRejected($donationRequest));
            }

            $donationRequest->save();
            DB::commit();

            return back()->with('success', 'Donation request has been ' . $request->status);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error processing request: ' . $e->getMessage());
        }
    }
}
