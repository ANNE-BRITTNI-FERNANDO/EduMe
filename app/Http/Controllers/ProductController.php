<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Order;
use App\Models\ProductImage;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Notifications\ProductSubmittedForReview;

class ProductController extends Controller
{
    // Method to display all products for admin or seller
    public function index()
    {
        $query = Product::query()->where('user_id', auth()->id());

        // Apply status filter
        if (request('status')) {
            switch (request('status')) {
                case 'pending':
                    $query->where('is_approved', false)
                          ->where('is_rejected', false)
                          ->where('status', 'pending');
                    break;
                case 'approved':
                    $query->where('is_approved', true)
                          ->where('is_rejected', false);
                    break;
                case 'rejected':
                    $query->where('is_rejected', true);
                    break;
                case 'resubmitted':
                    $query->where('status', 'resubmitted')
                          ->where('is_approved', false)
                          ->where('is_rejected', false);
                    break;
            }
        }

        // Apply date filters
        if (request('date_from')) {
            $query->whereDate('created_at', '>=', request('date_from'));
        }
        if (request('date_to')) {
            $query->whereDate('created_at', '<=', request('date_to'));
        }

        // Get products with sorting
        $products = $query->latest()->get();

        // Get statistics
        $stats = [
            'total' => Product::where('user_id', auth()->id())->count(),
            'pending' => Product::where('user_id', auth()->id())
                ->where('is_approved', false)
                ->where('is_rejected', false)
                ->where('status', 'pending')
                ->count(),
            'approved' => Product::where('user_id', auth()->id())
                ->where('is_approved', true)
                ->where('is_rejected', false)
                ->count(),
            'rejected' => Product::where('user_id', auth()->id())
                ->where('is_rejected', true)
                ->count()
        ];

        return view('seller.products.index', compact('products', 'stats'));
    }

    // Method to display the product creation form
    public function create()
    {
        // Define categories for the dropdown
        $categories = [
            'Textbooks & Reference Books',
            'Literature & Story Books',
            'Study Materials & Notes',
            'School Bags & Supplies',
            'Educational Technology'
        ];
        
        // Get recent products for the current seller
        $products = Product::where('user_id', auth()->id())
            ->latest()
            ->take(5)
            ->get();

        return view('seller.products.create', compact('categories', 'products'));
    }

    // Store the new product
    public function store(Request $request)
    {
        $request->validate([
            'product_name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'category' => 'required|string|in:Textbooks & Reference Books,Literature & Story Books,Study Materials & Notes,School Bags & Supplies,Educational Technology',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'additional_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        try {
            // Handle main image upload
            $imagePath = $request->file('image')->store('products', 'public');

            // Create the product
            $product = Product::create([
                'product_name' => $request->product_name,
                'description' => $request->description,
                'price' => $request->price,
                'category' => $request->category,
                'image_path' => $imagePath,
                'user_id' => auth()->id()
            ]);

            // Handle additional images
            if ($request->hasFile('additional_images')) {
                $sortOrder = 1;
                foreach ($request->file('additional_images') as $image) {
                    $additionalImagePath = $image->store('products', 'public');
                    
                    $product->images()->create([
                        'image_path' => $additionalImagePath,
                        'is_primary' => false,
                        'sort_order' => $sortOrder++
                    ]);
                }
            }

            // Create a primary image record
            $product->images()->create([
                'image_path' => $imagePath,
                'is_primary' => true,
                'sort_order' => 0
            ]);

            // Notify admin about the new product submission
            $admin = User::where('role', 'admin')->first();
            if ($admin) {
                $admin->notify(new ProductSubmittedForReview($product));
            }

            return redirect()->route('seller.products.index')
                ->with('success', 'Product created successfully!');

        } catch (\Exception $e) {
            \Log::error('Failed to create product: ' . $e->getMessage());
            return back()->with('error', 'Failed to create product. Please try again.');
        }
    }

    // Method to display all products in the admin dashboard
    public function adminIndex()
    {
        // Fetch only pending products (not approved and not rejected)
        $products = Product::where('is_approved', false)
                          ->where('is_rejected', false)
                          ->orderBy('created_at', 'desc')
                          ->get();

        // Get statistics for the dashboard
        $totalOrders = Order::count();
        $pendingOrders = Order::where('status', 'pending')->count();
        $completedOrders = Order::where('status', 'completed')->count();
        $totalRevenue = Order::where('status', 'completed')->sum('total_amount');

        return view('admin.dashboard', compact('products', 'totalOrders', 'pendingOrders', 'completedOrders', 'totalRevenue'));
    }

    // Approve a product
    public function approve($id)
    {
        try {
            $product = Product::findOrFail($id);
            $product->update([
                'is_approved' => true,
                'is_rejected' => false
            ]);

            return redirect()->back()->with('success', 'Product approved successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to approve product.');
        }
    }

    // Reject a product
    public function reject($id)
    {
        try {
            $product = Product::findOrFail($id);
            $product->update([
                'is_approved' => false,
                'is_rejected' => true
            ]);

            return redirect()->back()->with('success', 'Product rejected successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to reject product.');
        }
    }

    // Show all approved products for the logged-in user
    public function showApprovedProducts()
    {
        // Fetch only approved products for the logged-in user
        $approvedProducts = Product::where('user_id', Auth::id())
                                 ->where('is_approved', true)
                                 ->where('is_rejected', false)
                                 ->orderBy('created_at', 'desc')
                                 ->get();

        return view('productlisting', compact('approvedProducts'));
    }

    // Edit product
    public function edit($id)
    {
        $product = Product::findOrFail($id);
        
        // Check if the user is the owner of the product
        if (auth()->id() !== $product->user_id) {
            abort(403, 'Unauthorized action.');
        }

        $categories = [
            'Textbooks & Reference Books',
            'Literature & Story Books',
            'Study Materials & Notes',
            'School Bags & Supplies',
            'Educational Technology'
        ];
        return view('seller.products.edit', compact('product', 'categories'));
    }

    // Update product
    public function update(Request $request, Product $product)
    {
        $request->validate([
            'product_name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'category' => 'required|string|in:Textbooks & Reference Books,Literature & Story Books,Study Materials & Notes,School Bags & Supplies,Educational Technology',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'additional_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        try {
            // Update basic product information
            $product->update([
                'product_name' => $request->product_name,
                'description' => $request->description,
                'price' => $request->price,
                'category' => $request->category,
            ]);

            // Handle main image update if provided
            if ($request->hasFile('image')) {
                // Delete old image
                if ($product->image_path) {
                    Storage::disk('public')->delete($product->image_path);
                }
                
                // Store new image
                $imagePath = $request->file('image')->store('products', 'public');
                $product->update(['image_path' => $imagePath]);

                // Update or create primary image record
                $product->images()->updateOrCreate(
                    ['is_primary' => true],
                    ['image_path' => $imagePath, 'sort_order' => 0]
                );
            }

            // Handle additional images if provided
            if ($request->hasFile('additional_images')) {
                $sortOrder = $product->images->where('is_primary', false)->max('sort_order') ?? 0;
                
                foreach ($request->file('additional_images') as $image) {
                    $imagePath = $image->store('products', 'public');
                    $sortOrder++;
                    
                    $product->images()->create([
                        'image_path' => $imagePath,
                        'is_primary' => false,
                        'sort_order' => $sortOrder
                    ]);
                }
            }

            return redirect()->route('seller.products.index')
                ->with('success', 'Product updated successfully!');

        } catch (\Exception $e) {
            \Log::error('Failed to update product: ' . $e->getMessage());
            return back()->with('error', 'Failed to update product. Please try again.');
        }
    }

    // Add a new method to handle image deletion
    public function deleteImage($imageId)
    {
        try {
            $image = ProductImage::findOrFail($imageId);
            
            // Don't allow deletion of primary images
            if ($image->is_primary) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete primary image'
                ], 400);
            }

            // Delete the file from storage
            Storage::disk('public')->delete($image->image_path);
            
            // Delete the database record
            $image->delete();

            return response()->json([
                'success' => true,
                'message' => 'Image deleted successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to delete image: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete image'
            ], 500);
        }
    }

    // List approved products with advanced filtering
    public function listApprovedProducts(Request $request)
    {
        // Define categories for the dropdown
        $categories = [
            'Textbooks & Reference Books',
            'Literature & Story Books',
            'Study Materials & Notes',
            'School Bags & Supplies',
            'Educational Technology'
        ];
        
        // Start with base query for available products only
        $query = Product::where('is_approved', true)
                       ->where('is_rejected', false)
                       ->where('is_sold', false)
                       ->where('quantity', '>', 0);
        
        // Get the authenticated user's location
        $buyerLocation = Auth::check() ? Auth::user()->location : null;

        // Get all unique locations from users who have products
        $locations = Product::where('is_approved', true)
            ->where('is_sold', false)
            ->where('quantity', '>', 0)
            ->join('users', 'products.user_id', '=', 'users.id')
            ->whereNotNull('users.location')
            ->distinct()
            ->pluck('users.location')
            ->sort()
            ->values();

        // Apply search filter
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('product_name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('description', 'like', '%' . $searchTerm . '%');
            });
        }

        // Apply category filter
        if ($request->filled('category')) {
            $query->where('category', $request->category);
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

        // Get products with their sellers
        $approvedProducts = $query->with('user')->get();

        // Sort by location if buyer location is available and no specific location is selected
        if ($buyerLocation && !$request->filled('location')) {
            $approvedProducts = $approvedProducts->sortBy(function($product) use ($buyerLocation) {
                if (!$product->user || !$product->user->location) {
                    return PHP_FLOAT_MAX;
                }
                return $this->calculateLocationDistance($buyerLocation, $product->user->location);
            });
        }

        return view('productlisting', compact('approvedProducts', 'categories', 'locations'));
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

        // Same country (if provided)
        if (isset($loc1Parts[2]) && isset($loc2Parts[2]) && $loc1Parts[2] === $loc2Parts[2]) {
            return 3;
        }

        return 4; // Different locations entirely
    }

    public function filterApprovedProducts(Request $request)
    {
        // Define categories for the dropdown
        $categories = [
            'Textbooks & Reference Books',
            'Literature & Story Books',
            'Study Materials & Notes',
            'School Bags & Supplies',
            'Educational Technology'
        ];
        
        // Get the selected category from the request
        $selectedCategory = $request->input('category');
        
        // Fetch approved products filtered by the selected category, if any
        $approvedProducts = Product::where('is_approved', true)
            ->when($selectedCategory, function ($query, $category) {
                return $query->where('category', $category);
            })
            ->get();

        // Pass the categories, selected category, and products to the view
        return view('productlisting', compact('approvedProducts', 'categories', 'selectedCategory'));
    }


    public function destroy(Product $product)
    {
        try {
            // Check if the product belongs to the current user
            if ($product->user_id !== auth()->id()) {
                return redirect()->back()->with('error', 'Unauthorized action.');
            }

            // Delete product images from storage
            if ($product->image_path) {
                Storage::disk('public')->delete($product->image_path);
            }

            // Delete additional images
            foreach ($product->images as $image) {
                Storage::disk('public')->delete($image->image_path);
            }

            // Delete the product
            $product->delete();

            return redirect()->route('seller.products.index')
                ->with('success', 'Product deleted successfully.');

        } catch (\Exception $e) {
            \Log::error('Error deleting product: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to delete product. Please try again.');
        }
    }

    public function show(Product $product)
    {
        // For public view, only show approved products
        if ($product->status !== 'approved') {
            abort(404);
        }

        return view('products.show', compact('product'));
    }

    // Advanced product filtering method
    public function advancedFilter(Request $request)
    {
        $query = Product::query()->where('is_approved', true);
        $buyerLocation = auth()->user()->location;

        // Price range filter
        if ($request->filled(['min_price', 'max_price'])) {
            $query->whereBetween('price', [$request->min_price, $request->max_price]);
        }

        // Category filter
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Search term
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('product_name', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        // Sort by date
        if ($request->filled('sort_date')) {
            $query->orderBy('created_at', $request->sort_date);
        }

        // Get all products
        $products = $query->with('user')->get();

        // Sort by location if buyer location is available
        if ($buyerLocation) {
            $products = $products->sort(function($a, $b) use ($buyerLocation) {
                $distanceA = $this->calculateDistance($buyerLocation, $a->user->location);
                $distanceB = $this->calculateDistance($buyerLocation, $b->user->location);
                return $distanceA <=> $distanceB;
            });
        }

        $categories = [
            'Textbooks & Reference Books',
            'Literature & Story Books',
            'Study Materials & Notes',
            'School Bags & Supplies',
            'Educational Technology'
        ];
        return view('productlisting', [
            'approvedProducts' => $products,
            'categories' => $categories,
            'selectedCategory' => $request->category
        ]);
    }

    // Helper function to calculate distance between two locations
    private function calculateDistance($location1, $location2)
    {
        // Simple string comparison for now - can be enhanced with actual geocoding
        if (!$location1 || !$location2) return PHP_FLOAT_MAX;
        
        // Convert locations to lowercase for comparison
        $loc1 = strtolower($location1);
        $loc2 = strtolower($location2);
        
        // If locations are exactly the same, distance is 0
        if ($loc1 === $loc2) return 0;
        
        // If locations share the same city/region (assuming format "City, Region")
        $loc1Parts = explode(',', $loc1);
        $loc2Parts = explode(',', $loc2);
        
        if (isset($loc1Parts[0]) && isset($loc2Parts[0])) {
            if (trim($loc1Parts[0]) === trim($loc2Parts[0])) return 1;
        }
        
        // Different cities but same region
        if (isset($loc1Parts[1]) && isset($loc2Parts[1])) {
            if (trim($loc1Parts[1]) === trim($loc2Parts[1])) return 2;
        }
        
        // Different regions
        return 3;
    }

    public function sellerProducts()
    {
        $query = Product::query()->where('user_id', auth()->id());

        // Apply status filter
        if (request('status')) {
            switch (request('status')) {
                case 'pending':
                    $query->where('is_approved', false)
                          ->where('is_rejected', false)
                          ->where('status', 'pending');
                    break;
                case 'approved':
                    $query->where('is_approved', true)
                          ->where('is_rejected', false);
                    break;
                case 'rejected':
                    $query->where('is_rejected', true);
                    break;
                case 'resubmitted':
                    $query->where('status', 'resubmitted')
                          ->where('is_approved', false)
                          ->where('is_rejected', false);
                    break;
            }
        }

        // Apply date filters
        if (request('date_from')) {
            $query->whereDate('created_at', '>=', request('date_from'));
        }
        if (request('date_to')) {
            $query->whereDate('created_at', '<=', request('date_to'));
        }

        // Get products with sorting and pagination
        $products = $query->latest()->paginate(12);

        // Get statistics
        $stats = [
            'total' => Product::where('user_id', auth()->id())->count(),
            'pending' => Product::where('user_id', auth()->id())
                ->where('is_approved', false)
                ->where('is_rejected', false)
                ->where('status', 'pending')
                ->count(),
            'approved' => Product::where('user_id', auth()->id())
                ->where('is_approved', true)
                ->where('is_rejected', false)
                ->count(),
            'rejected' => Product::where('user_id', auth()->id())
                ->where('is_rejected', true)
                ->count()
        ];

        return view('seller.products.index', compact('products', 'stats'));
    }

    // Show approved products for admin
    public function approvedProducts()
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Access denied. Admin only area.');
        }

        $products = Product::where('is_approved', true)
                          ->where('is_rejected', false)
                          ->orderBy('created_at', 'desc')
                          ->get();

        return view('admin.products.approved', compact('products'));
    }

    public function adminShow(Product $product)
    {
        // Only admin can access this
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        return view('admin.products.show', compact('product'));
    }

    public function resubmitForm(Product $product)
    {
        // Check if the product belongs to the current user
        if ($product->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        // Define categories for the dropdown
        $categories = [
            'Textbooks & Reference Books',
            'Literature & Story Books',
            'Study Materials & Notes',
            'School Bags & Supplies',
            'Educational Technology'
        ];

        return view('seller.products.resubmit', compact('product', 'categories'));
    }

    public function resubmit(Request $request, Product $product)
    {
        try {
            $request->validate([
                'product_name' => 'required|string|max:255',
                'description' => 'required|string',
                'price' => 'required|numeric|min:0',
                'category' => 'required|string',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'additional_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            // Update product details
            $product->product_name = $request->product_name;
            $product->description = $request->description;
            $product->price = $request->price;
            $product->category = $request->category;
            $product->status = 'resubmitted'; // Set status to resubmitted
            $product->is_approved = false;
            $product->is_rejected = false;
            $product->rejection_reason = null; // Clear previous rejection reason
            $product->rejection_note = null;   // Clear previous rejection note

            // Handle main image if provided
            if ($request->hasFile('image')) {
                // Delete old main image
                if ($product->image_path) {
                    Storage::disk('public')->delete($product->image_path);
                }
                $product->image_path = $request->file('image')->store('products', 'public');
            }

            $product->save();

            // Handle additional images if provided
            if ($request->hasFile('additional_images')) {
                // Delete old additional images
                foreach ($product->images as $image) {
                    Storage::disk('public')->delete($image->image_path);
                }
                $product->images()->delete();

                // Add new additional images
                $sortOrder = 1;
                foreach ($request->file('additional_images') as $image) {
                    $imagePath = $image->store('products', 'public');
                    $sortOrder++;
                    
                    $product->images()->create([
                        'image_path' => $imagePath,
                        'is_primary' => false,
                        'sort_order' => $sortOrder
                    ]);
                }
            }

            // Add debug logging
            \Log::info('Product resubmitted:', [
                'product_id' => $product->id,
                'new_status' => $product->status,
                'is_approved' => $product->is_approved,
                'is_rejected' => $product->is_rejected
            ]);

            // Notify admin about the resubmitted product
            $admin = User::where('role', 'admin')->first();
            if ($admin) {
                $admin->notify(new ProductSubmittedForReview($product));
            }

            return redirect()->route('seller.products.index')
                ->with('success', 'Product has been resubmitted for review.');

        } catch (\Exception $e) {
            \Log::error('Error resubmitting product: ' . $e->getMessage());
            return back()->with('error', 'Failed to resubmit product. Please try again.');
        }
    }
}
