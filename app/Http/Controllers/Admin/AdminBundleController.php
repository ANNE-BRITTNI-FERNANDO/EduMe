<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bundle;
use App\Notifications\BundleStatusUpdated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminBundleController extends Controller
{
    public function index(Request $request)
    {
        $query = Bundle::with(['categories', 'user']);

        // Handle status filter
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Handle sorting
        switch ($request->sort_by) {
            case 'oldest':
                $query->oldest();
                break;
            case 'price-high':
                $query->orderBy('price', 'desc');
                break;
            case 'price-low':
                $query->orderBy('price', 'asc');
                break;
            default:
                $query->latest();
                break;
        }

        $bundles = $query->paginate(10);
        return view('admin.bundles.index', compact('bundles'));
    }

    public function updateStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:approved,rejected,pending',
            'rejection_reason' => 'required_if:status,rejected|string|nullable',
            'rejection_details' => 'required_if:status,rejected|string|nullable',
            'category_status' => 'required|array',
            'category_status.*.status' => 'required|in:approved,rejected,pending',
            'category_status.*.rejection_reason' => 'required_if:category_status.*.status,rejected|string|nullable',
            'category_status.*.rejection_details' => 'string|nullable'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        DB::beginTransaction();
        
        try {
            $bundle = Bundle::with(['categories', 'user'])->findOrFail($id);
            
            // Check if any category is rejected before processing
            $rejectedCategories = collect($request->category_status)
                ->filter(function ($category) {
                    return $category['status'] === 'rejected';
                });

            if ($request->status === 'approved' && $rejectedCategories->isNotEmpty()) {
                DB::rollback();
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot approve bundle because one or more categories are rejected.'
                ], 422);
            }

            // Update all bundle categories first
            foreach ($bundle->categories as $category) {
                $categoryId = $category->id;
                $categoryData = $request->category_status[$categoryId] ?? null;
                
                if (!$categoryData || !isset($categoryData['status'])) {
                    DB::rollback();
                    return response()->json([
                        'success' => false,
                        'message' => "Status not provided for category: {$category->category}"
                    ], 422);
                }

                // Validate rejection reason for rejected categories
                if ($categoryData['status'] === 'rejected' && empty($categoryData['rejection_reason'])) {
                    DB::rollback();
                    return response()->json([
                        'success' => false,
                        'message' => "Rejection reason is required for rejected category: {$category->category}"
                    ], 422);
                }

                // Update category status and details using the model
                $category->status = $categoryData['status'];
                $category->rejection_reason = $categoryData['status'] === 'rejected' ? $categoryData['rejection_reason'] : null;
                $category->rejection_details = $categoryData['status'] === 'rejected' ? ($categoryData['rejection_details'] ?? null) : null;
                
                // Force save the category
                $saved = $category->save();
                
                if (!$saved) {
                    DB::rollback();
                    return response()->json([
                        'success' => false,
                        'message' => "Failed to update category: {$category->category}"
                    ], 500);
                }

                // Double-check if the update was successful
                $category->refresh();
                if ($category->status !== $categoryData['status']) {
                    DB::rollback();
                    return response()->json([
                        'success' => false,
                        'message' => "Failed to verify category update: {$category->category}"
                    ], 500);
                }
            }

            // Update bundle status and details
            $bundle->status = $request->status;
            if ($request->status === 'rejected') {
                $bundle->rejection_reason = $request->rejection_reason;
                $bundle->rejection_details = $request->rejection_details;
            } else {
                $bundle->rejection_reason = null;
                $bundle->rejection_details = null;
            }
            
            // Save the bundle
            if (!$bundle->save()) {
                DB::rollback();
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update bundle status'
                ], 500);
            }

            DB::commit();

            // Send notification to the bundle owner
            try {
                $bundle->user->notify(new BundleStatusUpdated($bundle, $request->status));
            } catch (\Exception $e) {
                \Log::error('Failed to send bundle status notification: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Bundle status updated successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Bundle status update error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update bundle status: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show(Bundle $bundle)
    {
        return view('admin.bundles.show', compact('bundle'));
    }

    public function showApprovedBundles()
    {
        $bundles = Bundle::with(['categories', 'user'])
            ->where('status', 'approved')
            ->latest()
            ->get();
        return view('shop.bundles', compact('bundles'));
    }
}
