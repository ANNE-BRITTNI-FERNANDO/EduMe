<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DonationRequest;
use App\Models\DonationItem;
use App\Models\Donation;
use App\Notifications\DonationApprovedNotification;
use App\Notifications\DonationRejectedNotification;
use App\Notifications\DonationRequestApprovedNotification;
use App\Notifications\DonationRequestRejectedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminDonationController extends Controller
{
    const STATUS_PENDING = 'pending';
    const STATUS_UNDER_REVIEW = 'under_review';
    const STATUS_VERIFIED = 'verified';
    const STATUS_REJECTED = 'rejected';
    const STATUS_AVAILABLE = 'available';
    const STATUS_ALLOCATED = 'allocated';
    const STATUS_APPROVED = 'approved';
    const STATUS_COMPLETED = 'completed';

    public function index(Request $request)
    {
        // Initialize variables
        $pendingDonations = collect();
        $approvedDonations = collect();
        $rejectedDonations = collect();
        $locations = collect();
        $tab = $request->get('tab', 'pending');
        $debug = [];

        try {
            // Debug: Get all donations first
            $allDonations = DonationItem::all();
            $debug['total_donations'] = $allDonations->count();
            $debug['status_counts'] = DonationItem::selectRaw('status, count(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();
            
            // Always fetch both pending and approved donations
            $pendingDonations = DonationItem::where('status', 'pending')
                ->with(['user', 'reviewedByUser'])
                ->latest()
                ->get();
            $debug['pending_count'] = $pendingDonations->count();

            // Get approved donations - with debug info
            $approvedQuery = DonationItem::where('status', 'approved');
            $debug['total_approved'] = $approvedQuery->count();
            
            $approvedWithQuantity = $approvedQuery->where('available_quantity', '>', 0);
            $debug['approved_with_quantity'] = $approvedWithQuantity->count();
            
            $approvedDonations = $approvedWithQuantity->with(['user', 'reviewedByUser'])
                ->latest()
                ->get();
            $debug['final_approved_count'] = $approvedDonations->count();

            $rejectedDonations = DonationItem::where('status', 'rejected')
                ->with(['user', 'reviewedByUser'])
                ->latest()
                ->get();
            $debug['rejected_count'] = $rejectedDonations->count();

            $locations = DonationItem::distinct()
                ->whereNotNull('location')
                ->pluck('location')
                ->sort()
                ->values();

        } catch (\Exception $e) {
            \Log::error("Error in AdminDonationController: " . $e->getMessage());
            $debug['error'] = $e->getMessage();
        }

        return view('admin.donations.index', [
            'pendingDonations' => $pendingDonations,
            'approvedDonations' => $approvedDonations,
            'rejectedDonations' => $rejectedDonations,
            'locations' => $locations,
            'tab' => $tab,
            'debug' => $debug
        ]);
    }

    public function show(DonationItem $donation)
    {
        return view('admin.donations.show', compact('donation'));
    }

    public function approveDonation(DonationItem $donation)
    {
        try {
            $donation->status = self::STATUS_APPROVED;
            $donation->reviewedByUser = auth()->id();
            $donation->verified_at = now();
            // Set the available quantity to the total quantity when approving
            if (!isset($donation->available_quantity)) {
                $donation->available_quantity = $donation->quantity;
            }
            $donation->save();
    
            return response()->json([
                'success' => true,
                'message' => 'Donation approved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error approving donation'
            ], 500);
        }
    }
    
    public function rejectDonation(DonationItem $donation)
    {
        try {
            $donation->status = self::STATUS_REJECTED;
            $donation->reviewedByUser = auth()->id();
            $donation->verified_at = now();
            $donation->save();
    
            return response()->json([
                'success' => true,
                'message' => 'Donation rejected successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error rejecting donation'
            ], 500);
        }
    }

    
    public function deleteDonation(DonationItem $donation)
    {
        try {
            DB::beginTransaction();

            // Delete any associated files/images
            if ($donation->images) {
                if (is_string($donation->images)) {
                    $images = json_decode($donation->images, true);
                    foreach ($images as $image) {
                        Storage::delete($image);
                    }
                }
            }

            // Delete the donation
            $donation->delete();

            DB::commit();
            return back()->with('success', 'Donation has been deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting donation: ' . $e->getMessage());
            return back()->with('error', 'An error occurred while deleting the donation.');
        }
    }

    public function removeDonation(DonationItem $donation)
    {
        if ($donation->status !== self::STATUS_APPROVED || !$donation->is_available) {
            return back()->with('error', 'This donation cannot be removed from the available list.');
        }

        try {
            DB::beginTransaction();

            // Update donation to be unavailable
            $donation->update([
                'is_available' => false,
                'removed_at' => now(),
                'removed_by' => Auth::id()
            ]);

            DB::commit();
            return back()->with('success', 'Donation has been removed from the available list.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error removing donation: ' . $e->getMessage());
            return back()->with('error', 'An error occurred while removing the donation.');
        }
    }

    public function requests()
    {
        try {
            $pendingRequests = DonationRequest::whereIn('status', [self::STATUS_PENDING, self::STATUS_UNDER_REVIEW])
                ->with(['donationItem.user', 'user'])
                ->latest()
                ->get();

            $approvedRequests = DonationRequest::where('status', self::STATUS_APPROVED)
                ->with(['donationItem.user', 'user'])
                ->latest()
                ->get();

            $rejectedRequests = DonationRequest::where('status', self::STATUS_REJECTED)
                ->with(['donationItem.user', 'user'])
                ->latest()
                ->get();

            return view('admin.donations.requests', compact('pendingRequests', 'approvedRequests', 'rejectedRequests'));
        } catch (\Exception $e) {
            \Log::error("Error in AdminDonationController@requests: " . $e->getMessage());
            return back()->with('error', 'An error occurred while loading donation requests.');
        }
    }

    public function showRequest(DonationRequest $donationRequest)
    {
        return view('admin.donations.request-details', compact('donationRequest'));
    }

    public function approveRequest(DonationRequest $donationRequest)
    {
        try {
            DB::beginTransaction();

            // Update request status
            $donationRequest->update([
                'status' => self::STATUS_APPROVED,
                'approved_at' => now(),
                'approved_by' => Auth::id()
            ]);

            // Update the donation item's available quantity
            if ($donationRequest->donationItem) {
                $donationRequest->donationItem->decrement('available_quantity', $donationRequest->quantity);
            }

            // Notify the user
            $donationRequest->user->notify(new DonationRequestApprovedNotification($donationRequest));

            DB::commit();
            return back()->with('success', 'Donation request approved successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Error approving donation request: " . $e->getMessage());
            return back()->with('error', 'An error occurred while approving the donation request.');
        }
    }

    public function rejectRequest(DonationRequest $donationRequest, Request $request)
    {
        try {
            DB::beginTransaction();

            // Update request status
            $donationRequest->update([
                'status' => self::STATUS_REJECTED,
                'rejected_at' => now(),
                'rejected_by' => Auth::id(),
                'rejection_reason' => $request->input('reason')
            ]);

            // Notify the user
            $donationRequest->user->notify(new DonationRequestRejectedNotification($donationRequest));

            DB::commit();
            return back()->with('success', 'Donation request rejected successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Error rejecting donation request: " . $e->getMessage());
            return back()->with('error', 'An error occurred while rejecting the donation request.');
        }
    }
}
