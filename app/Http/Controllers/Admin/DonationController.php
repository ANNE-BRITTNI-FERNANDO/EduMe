<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DonationItem;
use App\Models\DonationRequest;
use App\Models\User;
use App\Notifications\DonationRequestApproved;
use App\Notifications\DonationRequestRejected;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class DonationController extends Controller
{
    public function index()
    {
        $pendingDonations = DonationItem::where('status', 'pending')->with(['user', 'requests'])->latest()->get();
        $verifiedDonations = DonationItem::where('status', 'verified')->with(['user', 'requests'])->latest()->get();
        $availableDonations = DonationItem::where('status', 'available')->with(['user', 'requests'])->latest()->get();
        $completedDonations = DonationItem::where('status', 'completed')->with(['user', 'requests'])->latest()->get();
        $rejectedDonations = DonationItem::where('status', 'rejected')->with(['user', 'requests'])->latest()->get();
        $pendingRequestsCount = DonationRequest::where('status', 'pending')->count();
        
        return view('admin.donations.index', compact(
            'pendingDonations',
            'verifiedDonations',
            'availableDonations',
            'completedDonations',
            'rejectedDonations',
            'pendingRequestsCount'
        ));
    }

    public function show(DonationItem $donation)
    {
        $donation->load(['user', 'requests.user']);
        return view('admin.donations.show', compact('donation'));
    }

    public function requests()
    {
        try {
            \Log::info('Starting DonationController@requests');
            
            // Get requests filtered at database level
            $pendingRequests = DonationRequest::with(['user', 'donationItem', 'rejectedBy'])
                ->where('status', 'pending')
                ->latest()
                ->get();

            $approvedRequests = DonationRequest::with(['user', 'donationItem', 'rejectedBy'])
                ->where('status', 'approved')
                ->latest()
                ->get();

            $rejectedRequests = DonationRequest::with(['user', 'donationItem', 'rejectedBy'])
                ->where('status', 'rejected')
                ->latest()
                ->get();

            \Log::info('Requests counts:', [
                'pending' => $pendingRequests->count(),
                'approved' => $approvedRequests->count(),
                'rejected' => $rejectedRequests->count()
            ]);

            return view('admin.donations.requests', compact('pendingRequests', 'approvedRequests', 'rejectedRequests'));
        } catch (\Exception $e) {
            \Log::error('Error in DonationController@requests: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            return back()->with('error', 'An error occurred while loading donation requests.');
        }
    }

    public function showRequest(DonationRequest $donationRequest)
    {
        try {
            $donationRequest = DonationRequest::with(['user', 'donationItem.user'])
                ->findOrFail($donationRequest->id);

            Log::info('Loading donation request:', [
                'id' => $donationRequest->id,
                'user_id' => $donationRequest->user_id,
                'donation_item_id' => $donationRequest->donation_item_id,
                'has_donation_item' => $donationRequest->donationItem ? 'yes' : 'no'
            ]);
                
            return view('admin.donations.request-details', ['donationRequest' => $donationRequest]);
        } catch (\Exception $e) {
            Log::error('Error loading donation request: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return back()->with('error', 'Error loading donation request: ' . $e->getMessage());
        }
    }

    public function approveRequest(Request $request, DonationRequest $donationRequest)
    {
        $request->validate([
            'admin_notes' => 'nullable|string'
        ]);

        try {
            $donationRequest = DonationRequest::with(['user', 'donationItem.user'])
                ->findOrFail($donationRequest->id);

            Log::info('Starting approval process for donation request #' . $donationRequest->id);
            Log::info('Current status: ' . $donationRequest->status);
            Log::info('Request data:', $request->all());
            Log::info('Donation Item:', ['id' => $donationRequest->donation_item_id, 'exists' => $donationRequest->donationItem ? 'yes' : 'no']);

            DB::beginTransaction();

            $donationRequest->update([
                'status' => 'approved',
                'approved_at' => now(),
                'approved_by' => Auth::id(),
                'admin_notes' => $request->admin_notes,
                'updated_at' => now()
            ]);

            // Refresh the model to get the updated data
            $donationRequest->refresh();
            Log::info('New status: ' . $donationRequest->status);

            // Update the donation item status if needed
            if ($donationRequest->donationItem) {
                DB::table('donation_items')
                    ->where('id', $donationRequest->donationItem->id)
                    ->update([
                        'status' => 'approved',
                        'updated_at' => now()
                    ]);
            }

            // Notify the recipient
            if ($donationRequest->user) {
                $donationRequest->user->notify(new DonationRequestApproved($donationRequest));
            }

            // Notify the donor
            if ($donationRequest->donationItem && $donationRequest->donationItem->user) {
                $donationRequest->donationItem->user->notify(new DonationRequestApproved($donationRequest));
            }

            DB::commit();

            return redirect('/admin/donations/requests')
                ->with('success', 'Donation request has been approved successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error approving donation request: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return back()->with('error', 'An error occurred while approving the request: ' . $e->getMessage());
        }
    }

    public function rejectRequest(Request $request, DonationRequest $donationRequest)
    {
        try {
            Log::info('Starting rejectRequest in DonationController');
            Log::info('Request data:', $request->all());
            Log::info('Rejection reason from request: ' . $request->reason);
            Log::info('DonationRequest ID: ' . $donationRequest->id);
            Log::info('DonationRequest current status: ' . $donationRequest->status);
            
            DB::beginTransaction();

            // Store the current state for debugging
            Log::info('Before update - Rejection reason: ' . $donationRequest->rejection_reason);

            $updateData = [
                'status' => 'rejected',
                'rejected_at' => now(),
                'rejected_by' => Auth::id(),
                'rejection_reason' => $request->reason
            ];
            Log::info('Update data:', $updateData);

            $donationRequest->update($updateData);

            // Verify the update
            $donationRequest->refresh();
            Log::info('After update - New status: ' . $donationRequest->status);
            Log::info('After update - Rejection reason: ' . $donationRequest->rejection_reason);

            // Update available quantity if needed
            if ($donationRequest->donationItem) {
                Log::info('Updating donation item quantity');
                DB::table('donation_items')
                    ->where('id', $donationRequest->donationItem->id)
                    ->increment('available_quantity', $donationRequest->quantity);
                Log::info('Updated donation item quantity');
            }

            // Notify the recipient
            if ($donationRequest->user) {
                Log::info('Notifying recipient: ' . $donationRequest->user->id);
                $donationRequest->user->notify(new DonationRequestRejected($donationRequest));
            }

            // Notify the donor
            if ($donationRequest->donationItem && $donationRequest->donationItem->user) {
                Log::info('Notifying donor: ' . $donationRequest->donationItem->user->id);
                $donationRequest->donationItem->user->notify(new DonationRequestRejected($donationRequest));
            }

            DB::commit();
            Log::info('Rejection completed successfully');

            return redirect('/admin/donations/requests')
                ->with('success', 'Donation request has been rejected successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error rejecting donation request: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            Log::error('Request data at time of error:', $request->all());
            return back()->with('error', 'An error occurred while rejecting the request: ' . $e->getMessage());
        }
    }

    public function approveDonation(DonationItem $donation)
    {
        $donation->status = 'approved';
        $donation->save();

        return redirect()->route('admin.donations.index')
            ->with('success', 'Donation has been approved successfully.');
    }

    public function rejectDonation(DonationItem $donation)
    {
        $donation->status = 'rejected';
        $donation->save();

        return redirect()->route('admin.donations.index')
            ->with('success', 'Donation has been rejected successfully.');
    }

    public function deleteDonation(DonationItem $donation)
    {
        $donation->delete();

        return redirect()->route('admin.donations.index')
            ->with('success', 'Donation has been deleted successfully.');
    }
}
