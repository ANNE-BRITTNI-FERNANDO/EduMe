<?php
namespace App\Http\Controllers;

use App\Models\Bundle;
use App\Models\User;
use Illuminate\Http\Request;

class BundleController extends Controller
{
    public function store(Request $request)
    {
        // Validate the request
        $request->validate([
            'bundleName' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric',
            'bundleImage' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'categories' => 'required|array',
            'categories.*' => 'required|string',
            'categoryImages' => 'required|array',
            'categoryImages.*' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Handle Bundle Image upload
        $bundleImagePath = $request->file('bundleImage')->store('bundles', 'public');

        // Create a new bundle entry
        $bundle = Bundle::create([
            'bundle_name' => $request->bundleName,
            'description' => $request->description,
            'price' => $request->price,
            'bundle_image' => $bundleImagePath,
            'status' => 'pending', // Set to 'pending' by default
            'user_id' => auth()->id(), // Add the user_id of the seller
        ]);

        // Handle Category Images
        foreach ($request->categories as $index => $categoryName) {
            $categoryImagePath = $request->file('categoryImages')[$index]->store('categories', 'public');

            // Create category for each bundle
            $bundle->categories()->create([
                'category' => $categoryName,
                'category_image' => $categoryImagePath,
            ]);
        }

        // Redirect back with success message
        return redirect()->back()->with('success', 'Bundle created successfully!');
    }

    public function index(Request $request)
    {
        // Start with base query
        $query = Bundle::with('user')->where('status', 'approved');
        
        // Get the authenticated user's location
        $buyerLocation = auth()->check() ? auth()->user()->location : null;

        // Get all unique locations from users who have bundles
        $locations = Bundle::where('status', 'approved')
            ->join('users', 'bundles.user_id', '=', 'users.id')
            ->whereNotNull('users.location')
            ->distinct()
            ->pluck('users.location')
            ->sort()
            ->values()
            ->toArray();

        // Apply search filter
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            \Log::info('Search term: ' . $searchTerm); // Add logging
            $query->where(function($q) use ($searchTerm) {
                $q->where('bundle_name', 'LIKE', '%' . $searchTerm . '%')
                  ->orWhere('description', 'LIKE', '%' . $searchTerm . '%');
            });
            // Log the SQL query
            \Log::info('SQL Query: ' . $query->toSql());
            \Log::info('SQL Bindings: ' . json_encode($query->getBindings()));
        }

        // Apply location filter
        if ($request->filled('location')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('location', $request->location);
            });
        }

        // Apply price range filter
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Apply date sorting
        if ($request->filled('sort_date')) {
            $query->orderBy('created_at', $request->sort_date);
        }

        // Get bundles with their sellers
        $bundles = $query->with('user')->get();

        // Sort by location if buyer location is available and no specific location is selected
        if ($buyerLocation && !$request->filled('location')) {
            $bundles = $bundles->sortBy(function($bundle) use ($buyerLocation) {
                if (!$bundle->user || !$bundle->user->location) {
                    return PHP_FLOAT_MAX;
                }
                return $this->calculateLocationDistance($buyerLocation, $bundle->user->location);
            });
        }

        // Convert sorted collection back to collection if needed
        $bundles = $bundles instanceof \Illuminate\Support\Collection 
            ? $bundles 
            : collect($bundles);

        return view('shop.bundles', compact('bundles', 'locations'));
    }

    // Helper function to calculate distance between locations
    private function calculateLocationDistance($location1, $location2)
    {
        if (!$location1 || !$location2) {
            return PHP_FLOAT_MAX;
        }

        $loc1 = strtolower(trim($location1));
        $loc2 = strtolower(trim($location2));

        // Exact match
        if ($loc1 === $loc2) {
            return 0;
        }

        // Split locations into parts (assuming format: "City, Region" or "City, State, Country")
        $loc1Parts = array_map('trim', explode(',', $loc1));
        $loc2Parts = array_map('trim', explode(',', $loc2));

        // Same city
        if (isset($loc1Parts[0]) && isset($loc2Parts[0]) && $loc1Parts[0] === $loc2Parts[0]) {
            return 1;
        }

        // Same region/state
        if (isset($loc1Parts[1]) && isset($loc2Parts[1]) && $loc1Parts[1] === $loc2Parts[1]) {
            return 2;
        }

        // Different locations
        return 3;
    }

    // Display Approved Bundles
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
        // Find the bundle by ID
        $bundle = Bundle::findOrFail($id);

        // Update the approval status
        if ($request->status == 'approved') {
            $bundle->status = 'approved';
        } else {
            $bundle->status = 'rejected';
        }

        // Save the updated status
        $bundle->save();

        // Redirect to the approved bundles page if approved, or back otherwise
        if ($bundle->status == 'approved') {
            return redirect()->route('admin.bundles.approved')->with('success', 'Bundle approved successfully');
        }

        return redirect()->back()->with('success', 'Bundle status updated');
    }

    public function create()
{
    return view('sell-bundle'); // Replace 'bundles.create' with your actual view path
}

}
