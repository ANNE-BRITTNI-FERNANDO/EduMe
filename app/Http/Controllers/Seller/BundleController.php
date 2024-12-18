<?php
namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Bundle;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class BundleController extends Controller
{
    public function store(Request $request)
    {
        // Validate the request
        $request->validate([
            'bundleName' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'bundleImage' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'categories' => 'required|array|min:2|max:5',
            'categories.*' => 'required|string',
            'categoryImages' => 'required|array|min:2|max:5',
            'categoryImages.*' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        DB::beginTransaction();

        try {
            // Handle Bundle Image upload
            $bundleImagePath = $request->file('bundleImage')->store('bundles', 'public');

            // Create a new bundle entry
            $bundle = new Bundle();
            $bundle->bundle_name = $request->bundleName;
            $bundle->description = $request->description;
            $bundle->price = $request->price;
            $bundle->bundle_image = $bundleImagePath;
            $bundle->status = 'pending';
            $bundle->user_id = auth()->id();
            $bundle->quantity = 1; // Default quantity
            $bundle->save();

            // Handle Category Images
            foreach ($request->categories as $index => $categoryName) {
                $categoryImagePath = $request->file('categoryImages')[$index]->store('categories', 'public');

                // Create category for each bundle
                $bundle->categories()->create([
                    'category' => $categoryName,
                    'category_image' => $categoryImagePath,
                    'status' => 'pending'
                ]);
            }

            // Notify all admin users about the new bundle
            $admins = User::where('role', 'admin')->get();
            foreach ($admins as $admin) {
                try {
                    $admin->notify(new \App\Notifications\BundleSubmittedForReview($bundle));
                } catch (\Exception $e) {
                    \Log::error('Failed to send bundle submission notification to admin: ' . $e->getMessage());
                }
            }

            DB::commit();

            // Redirect back with success message
            return redirect()->back()->with('success', 'Bundle created successfully and sent for review!');
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Failed to create bundle: ' . $e->getMessage(), [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Failed to create bundle. Please try again.');
        }
    }

    public function index()
    {
        $user = auth()->user();
        $bundles = Bundle::where('user_id', $user->id)
            ->with(['categories']) // Eager load categories
            ->orderBy('created_at', 'desc')
            ->get();

        return view('seller.bundles.index', [
            'bundles' => $bundles
        ]);
    }

    public function approved()
    {
        // Fetch only approved bundles
        $approvedBundles = Bundle::where('status', 'approved')->get();

        // Return a view with the approved bundles
        return view('admin.bundles.approved', compact('approvedBundles'));
    }

    // Method to show individual bundle details
    public function show($id)
    {
        // Find the bundle by its ID and load the user relationship
        $bundle = Bundle::with('user')->findOrFail($id);
    
        // Return a view with the bundle details
        return view('bundles.show', compact('bundle'));
    }

    // Update the approval status (Approve/Reject)
    public function updateStatus(Request $request, $id)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:approved,rejected,pending',
            'rejection_reason' => 'required_if:status,rejected',
            'category_status' => 'required|array',
            'category_status.*.status' => 'required|in:approved,rejected,pending',
            'category_status.*.rejection_reason' => 'required_if:category_status.*.status,rejected',
            'category_status.*.rejection_details' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        try {
            DB::beginTransaction();

            // Find the bundle
            $bundle = Bundle::with('categories')->findOrFail($id);

            // Check if trying to approve bundle with rejected or pending categories
            if ($request->status === 'approved') {
                $rejectedCategories = collect($request->category_status)
                    ->filter(function ($category) {
                        return $category['status'] === 'rejected';
                    });

                $pendingCategories = collect($request->category_status)
                    ->filter(function ($category) {
                        return $category['status'] === 'pending';
                    });

                if ($rejectedCategories->isNotEmpty()) {
                    DB::rollback();
                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot approve bundle because one or more categories are rejected. Please update the rejected categories first.'
                    ], 422);
                }

                if ($pendingCategories->isNotEmpty()) {
                    DB::rollback();
                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot approve bundle because one or more categories are still pending review. Please review all categories first.'
                    ], 422);
                }
            }

            // Update bundle status
            $bundle->status = $request->status;
            if ($request->status === 'rejected') {
                $bundle->rejection_reason = $request->rejection_reason;
                $bundle->rejection_details = $request->rejection_details;
            } else {
                $bundle->rejection_reason = null;
                $bundle->rejection_details = null;
            }
            $bundle->save();

            // Update category statuses
            foreach ($bundle->categories as $category) {
                $categoryData = $request->category_status[$category->id] ?? null;
                
                if (!$categoryData) {
                    continue;
                }

                // Update category using direct query to ensure it's saved
                DB::table('bundle_categories')
                    ->where('id', $category->id)
                    ->where('bundle_id', $bundle->id)
                    ->update([
                        'status' => $categoryData['status'],
                        'rejection_reason' => $categoryData['status'] === 'rejected' ? $categoryData['rejection_reason'] : null,
                        'rejection_details' => $categoryData['status'] === 'rejected' ? ($categoryData['rejection_details'] ?? null) : null,
                        'updated_at' => now()
                    ]);
            }

            DB::commit();

            // Log the successful update
            \Log::info('Bundle and categories updated successfully', [
                'bundle_id' => $bundle->id,
                'bundle_status' => $bundle->status,
                'categories' => $bundle->categories->map(function ($cat) {
                    return [
                        'id' => $cat->id,
                        'status' => $cat->status,
                        'rejection_reason' => $cat->rejection_reason
                    ];
                })
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Bundle status updated successfully',
                'bundle' => $bundle->fresh(['categories'])
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Error updating bundle status: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update bundle status: ' . $e->getMessage()
            ], 500);
        }
    }

    public function create()
    {
        return view('seller.bundles.create');
    }

    // Method to edit bundle
    public function edit($id)
    {
        $bundle = Bundle::with('categories')->where('user_id', auth()->id())->findOrFail($id);
        return view('seller.bundles.edit', compact('bundle'));
    }

    // Method to update bundle
    public function update(Request $request, $id)
    {
        $bundle = Bundle::with('categories')->where('user_id', auth()->id())->findOrFail($id);
        
        // Validate the request
        $request->validate([
            'bundle_name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric',
            'bundle_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'categories' => 'required|array',
            'categories.*.category' => 'required|string',
            'categories.*.image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Handle Bundle Image upload if new image is provided
        if ($request->hasFile('bundle_image')) {
            $bundleImagePath = $request->file('bundle_image')->store('bundles', 'public');
            $bundle->bundle_image = $bundleImagePath;
        }

        // Update bundle details
        $bundle->bundle_name = $request->bundle_name;
        $bundle->description = $request->description;
        $bundle->price = $request->price;
        $bundle->status = 'pending'; // Reset to pending when edited
        $bundle->is_edited = true;
        $bundle->last_edited_at = now();
        $bundle->save();

        // Update categories
        foreach ($request->categories as $categoryId => $categoryData) {
            $category = $bundle->categories()->find($categoryId);
            if ($category) {
                $category->category = $categoryData['category'];
                if (isset($categoryData['image']) && $categoryData['image']) {
                    $categoryImagePath = $categoryData['image']->store('categories', 'public');
                    $category->category_image = $categoryImagePath;
                }
                $category->status = 'pending'; // Reset category status to pending
                $category->save();
            }
        }

        // Notify seller about the update
        auth()->user()->notify(new \App\Notifications\BundleStatusUpdated($bundle, 'edited'));

        return redirect()->route('seller.bundles.index')
            ->with('success', 'Bundle updated successfully and sent for review.');
    }

    // Method to show seller's bundles
    public function myBundles()
    {
        $bundles = Bundle::with('categories')
            ->where('user_id', auth()->id())
            ->latest()
            ->get();

        return view('seller.bundles.index', compact('bundles'));
    }

    public function destroy(Bundle $bundle)
    {
        // Check if user owns this bundle
        if ($bundle->user_id !== auth()->id()) {
            return redirect()->back()->with('error', 'Unauthorized action.');
        }

        try {
            // Delete the bundle image from storage
            if ($bundle->bundle_image) {
                Storage::disk('public')->delete($bundle->bundle_image);
            }

            // Delete category images
            foreach ($bundle->categories as $category) {
                if ($category->category_image) {
                    Storage::disk('public')->delete($category->category_image);
                }
            }

            // Delete the bundle (this will also delete related categories due to cascade)
            $bundle->delete();

            return redirect()->route('seller.bundles.index')
                ->with('success', 'Bundle deleted successfully.');
        } catch (\Exception $e) {
            \Log::error('Failed to delete bundle: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to delete bundle. Please try again.');
        }
    }
}
