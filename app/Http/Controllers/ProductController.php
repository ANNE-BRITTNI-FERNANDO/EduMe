<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Product;

class ProductController extends Controller
{
    // Method to display all products for admin
    public function index()
    {
        $products = Product::orderBy('created_at', 'desc')->get();
        return view('admin.dashboard', compact('products'));
    }

    // Method to display the product creation form
    public function create()
    {
        // Get approved products for the current seller
        $approvedProducts = Product::where('user_id', auth()->id())
            ->where('is_approved', true)
            ->where('is_rejected', false)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('seller', [
            'approvedProducts' => $approvedProducts
        ]);
    }

    // Store the new product
    public function store(Request $request)
    {
        $request->validate([
            'product_name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        // Handle image upload
        $imagePath = $request->file('image')->store('products', 'public');

        // Create product
        $product = Product::create([
            'product_name' => $request->product_name,
            'description' => $request->description,
            'price' => $request->price,
            'image_path' => $imagePath,
            'user_id' => auth()->id(),
            'category' => 'general', // default category
            'is_approved' => false,
            'is_rejected' => false
        ]);

        return redirect()->back()->with('success', 'Product added successfully! It will be reviewed by an admin.');
    }

    // Method to display all products in the admin dashboard
    public function adminIndex()
    {
        // Fetch all products from the database
        $products = Product::all();

        // Return the admin dashboard view with the products data
        return view('admin.dashboard', compact('products'));
    }

    // Approve a product
    public function approve($id)
    {
        $product = Product::findOrFail($id);
        $product->is_approved = true;
        $product->save();

        return redirect()->back()->with('success', 'Product approved successfully.');
    }

    // Reject a product
    public function reject($id)
    {
        $product = Product::find($id);
    
        if ($product) {
            $product->is_approved = false; // Ensure the `is_approved` field is updated if necessary
            $product->is_rejected = true;
            $product->save();
    
            return redirect()->back()->with('success', 'Product approved successfully.');
        }
    
        return redirect()->back()->with('success', 'Product approved successfully.');
    }
    

    // Show all approved products for the logged-in user
    public function showApprovedProducts()
    {
        // Fetch products that are approved and belong to the logged-in user
        $approvedProducts = Product::where('user_id', Auth::id())
                                   ->where('is_approved', true)
                                   ->get();

        // Return the view with the approved products for the user
        return view('productlisting', compact('approvedProducts'));
    }

    // Edit product
    public function edit($id)
    {
        // Fetch the product by ID
        $product = Product::findOrFail($id);

        // Pass the product data to the edit view
        return view('product.edit', compact('product'));
    }

    // Update product
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'product_name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric',
            'category' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $product->product_name = $request->input('product_name');
        $product->description = $request->input('description');
        $product->price = $request->input('price');
        $product->category = $request->input('category');

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('product_images', 'public');
            $product->image_path = $imagePath;
        }

        $product->save();

        return redirect()->route('admin.dashboard')->with('success', 'Product updated successfully');
    }

    // List approved products with advanced filtering
    public function listApprovedProducts(Request $request)
    {
        // Define categories for the dropdown
        $categories = ['Electronics', 'Books', 'Clothing', 'Furniture', 'Toys'];
        
        // Start with base query
        $query = Product::where('is_approved', true);
        
        // Get the authenticated user's location
        $buyerLocation = Auth::check() ? Auth::user()->location : null;

        // Get all unique locations from users who have products
        $locations = Product::where('is_approved', true)
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
        $products = $query->with('user')->get();

        // Sort by location if buyer location is available and no specific location is selected
        if ($buyerLocation && !$request->filled('location')) {
            $products = $products->sortBy(function($product) use ($buyerLocation) {
                if (!$product->user || !$product->user->location) {
                    return PHP_FLOAT_MAX;
                }
                return $this->calculateLocationDistance($buyerLocation, $product->user->location);
            });
        }

        $selectedCategory = $request->category;
        
        // Convert sorted collection back to collection if needed
        $approvedProducts = $products instanceof \Illuminate\Support\Collection 
            ? $products 
            : collect($products);

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
        $categories = ['Electronics', 'Books', 'Clothing', 'Furniture', 'Toys'];
        
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


    public function destroy($id)
{
    // Find the product by its ID
    $product = Product::findOrFail($id);

    // Delete the product
    $product->delete();

    // Flash a success message to the session
    return redirect()->route('seller')->with('success', 'Product deleted successfully');
}

// In ProductController.php
public function show($id)
{
    // Find the product by its ID
    $product = Product::findOrFail($id);

    // Return the 'product.show' view with the product data
    return view('product.show', compact('product'));
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

        $categories = ['Electronics', 'Books', 'Clothing', 'Furniture', 'Toys'];
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
        $products = Product::where('user_id', auth()->id())->get();
        return view('seller.products.index', compact('products'));
    }
}
