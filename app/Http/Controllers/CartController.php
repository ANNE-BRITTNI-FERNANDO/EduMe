<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\Bundle;
use App\Models\Warehouse; // Assuming Warehouse model is defined
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Delivery; // Assuming Delivery model is defined
use App\Services\DeliveryService;

class CartController extends Controller
{
    protected $deliveryService;

    public function __construct(DeliveryService $deliveryService)
    {
        $this->deliveryService = $deliveryService;
    }

    public function index()
    {
        // First, remove any invalid cart items
        CartItem::where('user_id', Auth::id())
            ->whereDoesntHave('product')
            ->whereDoesntHave('bundle')
            ->delete();

        $user = auth()->user();
        $items = CartItem::where('user_id', $user->id)
            ->with(['product', 'product.user', 'bundle', 'bundle.user'])
            ->get();

        $subtotal = 0;
        foreach ($items as $item) {
            if ($item->item_type === 'product' && $item->product) {
                $subtotal += $item->product->price;
            } elseif ($item->item_type === 'bundle' && $item->bundle) {
                $subtotal += $item->bundle->price;
            }
        }

        $deliveryFee = $this->deliveryService->calculateDeliveryFee(
            $items,
            $user->location ?? 'default',
            $user->province ?? null
        );

        $total = $subtotal + $deliveryFee;

        return view('cart.index', compact('items', 'subtotal', 'deliveryFee', 'total'));
    }

    public function addProductToCart($id)
    {
        try {
            $product = Product::findOrFail($id);
            
            // Check if product already in cart
            $existingCartItem = CartItem::where('user_id', auth()->id())
                ->where('product_id', $id)
                ->where('item_type', 'product')
                ->first();

            if ($existingCartItem) {
                return redirect()->back()->with('error', 'Product is already in your cart');
            }

            CartItem::create([
                'user_id' => auth()->id(),
                'product_id' => $id,
                'item_type' => 'product',
                'quantity' => 1
            ]);

            return redirect()->back()->with('success', 'Product added to cart successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to add product to cart');
        }
    }

    public function addBundleToCart($id)
    {
        try {
            $bundle = Bundle::findOrFail($id);
            
            // Check if bundle already in cart
            $existingCartItem = CartItem::where('user_id', auth()->id())
                ->where('bundle_id', $id)
                ->where('item_type', 'bundle')
                ->first();

            if ($existingCartItem) {
                return redirect()->back()->with('error', 'Bundle is already in your cart');
            }

            CartItem::create([
                'user_id' => auth()->id(),
                'bundle_id' => $id,
                'item_type' => 'bundle',
                'quantity' => 1
            ]);

            return redirect()->back()->with('success', 'Bundle added to cart successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to add bundle to cart');
        }
    }

    public function removeProduct($id)
    {
        try {
            CartItem::where('user_id', auth()->id())
                ->where('product_id', $id)
                ->where('item_type', 'product')
                ->delete();
            
            return redirect()->back()->with('success', 'Product removed from cart');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to remove product from cart');
        }
    }

    public function removeBundle($id)
    {
        try {
            CartItem::where('user_id', auth()->id())
                ->where('bundle_id', $id)
                ->where('item_type', 'bundle')
                ->delete();
            
            return redirect()->back()->with('success', 'Bundle removed from cart');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to remove bundle from cart');
        }
    }

    public function removeProductFromCart($id)
    {
        try {
            CartItem::where('user_id', auth()->id())
                ->where('product_id', $id)
                ->where('item_type', 'product')
                ->delete();

            return redirect()->back()->with('success', 'Product removed from cart successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to remove product from cart');
        }
    }

    public function removeBundleFromCart($id)
    {
        try {
            CartItem::where('user_id', auth()->id())
                ->where('bundle_id', $id)
                ->where('item_type', 'bundle')
                ->delete();

            return redirect()->back()->with('success', 'Bundle removed from cart successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to remove bundle from cart');
        }
    }

    public function checkout()
    {
        $cartItems = CartItem::where('user_id', Auth::id())
            ->with(['product', 'bundle', 'product.user', 'bundle.user'])
            ->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty');
        }

        // Calculate total price and collect items
        $subtotal = 0;
        $items = [];
        
        foreach ($cartItems as $item) {
            if ($item->item_type === 'product' && $item->product) {
                $price = $item->product->price;
                $name = $item->product->product_name;
                $seller = $item->product->user->name;
                $image = $item->product->image_path;
            } elseif ($item->item_type === 'bundle' && $item->bundle) {
                $price = $item->bundle->price;
                $name = $item->bundle->bundle_name;
                $seller = $item->bundle->user->name;
                $image = $item->bundle->bundle_image;
            } else {
                continue; // Skip invalid items
            }
            
            $subtotal += $price;
            $items[] = [
                'name' => $name,
                'price' => $price,
                'seller' => $seller,
                'image' => $image,
                'type' => $item->item_type
            ];
        }

        // Calculate delivery fee
        $user = Auth::user();
        $deliveryFee = $this->deliveryService->calculateDeliveryFee(
            $cartItems,
            $user->location ?? 'default',
            $user->province ?? null
        );
        
        // Calculate total
        $total = $subtotal + $deliveryFee;

        return view('payment.checkout', [
            'items' => $items,
            'subtotal' => $subtotal,
            'deliveryFee' => $deliveryFee,
            'total' => $total
        ]);
    }

    public function updateDelivery(Request $request, CartItem $cartItem)
    {
        if ($cartItem->user_id !== auth()->id()) {
            return redirect()->back()->with('error', 'Unauthorized action');
        }

        $cartItem->delivery_type = $request->delivery_type;
        $cartItem->save();
        
        // Recalculate all delivery fees
        $cartItems = CartItem::with('product.user')->where('user_id', auth()->id())->get();
        $buyerLocation = auth()->user()->location;
        $this->deliveryService->calculateDeliveryFee(
            $cartItems,
            $buyerLocation,
            $request->delivery_method ?? 'delivery'
        );

        return redirect()->back()->with('success', 'Delivery preferences updated successfully');
    }

    public function updateDeliveryDetails(Request $request)
    {
        $user = Auth::user();
        
        $user->update([
            'address' => $request->address,
            'phone' => $request->phone,
            'province' => $request->province,
            'location' => $request->location,
        ]);

        return response()->json(['status' => 'success']);
    }

    public function processCheckout(Request $request)
    {
        // Implement checkout logic
        try {
            $cartItems = CartItem::where('user_id', Auth::id())->get();

            // Calculate total amount including delivery fees
            $totalAmount = $cartItems->sum(function ($item) {
                return $item->price + $item->delivery_fee;
            });

            // Redirect to Stripe payment gateway
            return redirect()->route('stripe.payment', ['amount' => $totalAmount]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Checkout failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function checkoutSuccess()
    {
        return view('payment.success', [
            'message' => 'Payment completed successfully!'
        ]);
    }

    /**
     * Calculate and update delivery fee
     */
    public function updateDeliveryFee(Request $request)
    {
        try {
            $request->validate([
                'province' => 'required|string',
                'location' => 'required|string'
            ]);

            $user = auth()->user();
            $cartItems = CartItem::where('user_id', $user->id)
                ->with(['product', 'product.user', 'bundle', 'bundle.user'])
                ->get();

            // Calculate delivery fee using DeliveryService
            $deliveryFee = $this->deliveryService->calculateDeliveryFee(
                $cartItems,
                $request->location,
                $request->province
            );

            // Store the delivery fee in session for checkout
            session(['delivery_fee' => $deliveryFee]);

            return response()->json([
                'success' => true,
                'delivery_fee' => number_format($deliveryFee, 2, '.', ''),
                'message' => 'Delivery fee calculated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to calculate delivery fee: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get districts for a province
     */
    public function getDistricts($province)
    {
        $districts = [
            'Western' => [
                'Colombo',
                'Gampaha',
                'Kalutara'
            ],
            'Central' => [
                'Kandy',
                'Matale',
                'Nuwara Eliya'
            ],
            'Southern' => [
                'Galle',
                'Matara',
                'Hambantota'
            ],
            'Northern' => [
                'Jaffna',
                'Kilinochchi',
                'Mannar',
                'Mullaitivu',
                'Vavuniya'
            ],
            'Eastern' => [
                'Trincomalee',
                'Batticaloa',
                'Ampara'
            ],
            'North Western' => [
                'Kurunegala',
                'Puttalam'
            ],
            'North Central' => [
                'Anuradhapura',
                'Polonnaruwa'
            ],
            'Uva' => [
                'Badulla',
                'Monaragala'
            ],
            'Sabaragamuwa' => [
                'Ratnapura',
                'Kegalle'
            ]
        ];
        
        // Get major cities for each district
        $citiesByDistrict = [
            // Western Province
            'Colombo' => ['Colombo', 'Dehiwala', 'Moratuwa', 'Kotte', 'Kolonnawa', 'Kaduwela', 'Maharagama'],
            'Gampaha' => ['Gampaha', 'Negombo', 'Wattala', 'Ja-Ela', 'Kandana', 'Kelaniya'],
            'Kalutara' => ['Kalutara', 'Panadura', 'Horana', 'Bandaragama'],
            
            // Central Province
            'Kandy' => ['Kandy', 'Peradeniya', 'Gampola', 'Katugastota'],
            'Matale' => ['Matale', 'Dambulla', 'Galewela'],
            'Nuwara Eliya' => ['Nuwara Eliya', 'Hatton', 'Talawakele'],
            
            // Southern Province
            'Galle' => ['Galle', 'Ambalangoda', 'Hikkaduwa'],
            'Matara' => ['Matara', 'Weligama', 'Dickwella'],
            'Hambantota' => ['Hambantota', 'Tangalle', 'Tissamaharama'],
            
            // Northern Province
            'Jaffna' => ['Jaffna', 'Nallur', 'Chavakachcheri'],
            'Kilinochchi' => ['Kilinochchi'],
            'Mannar' => ['Mannar'],
            'Mullaitivu' => ['Mullaitivu'],
            'Vavuniya' => ['Vavuniya'],
            
            // Eastern Province
            'Trincomalee' => ['Trincomalee', 'Kantale'],
            'Batticaloa' => ['Batticaloa', 'Kattankudy', 'Eravur'],
            'Ampara' => ['Ampara', 'Kalmunai', 'Sammanthurai'],
            
            // North Western Province
            'Kurunegala' => ['Kurunegala', 'Kuliyapitiya', 'Wariyapola'],
            'Puttalam' => ['Puttalam', 'Chilaw', 'Wennappuwa'],
            
            // North Central Province
            'Anuradhapura' => ['Anuradhapura', 'Kekirawa', 'Tambuttegama'],
            'Polonnaruwa' => ['Polonnaruwa', 'Kaduruwela'],
            
            // Uva Province
            'Badulla' => ['Badulla', 'Bandarawela', 'Haputale'],
            'Monaragala' => ['Monaragala', 'Wellawaya'],
            
            // Sabaragamuwa Province
            'Ratnapura' => ['Ratnapura', 'Embilipitiya', 'Balangoda'],
            'Kegalle' => ['Kegalle', 'Mawanella', 'Warakapola']
        ];
        
        return response()->json([
            'success' => true,
            'districts' => $districts[$province] ?? [],
            'cities' => $citiesByDistrict
        ]);
    }

    public function updateDeliveryAddress(Request $request)
    {
        $request->validate([
            'city' => 'required|string|exists:sri_lanka_locations,city',
            'address' => 'required|string',
            'phone' => 'required|string'
        ]);

        $user = auth()->user();
        $user->update([
            'location' => $request->city,
            'address' => $request->address,
            'phone' => $request->phone
        ]);

        // Get updated delivery fee
        $items = CartItem::where('user_id', $user->id)
            ->with(['product', 'product.user', 'bundle', 'bundle.user'])
            ->get();

        $deliveryFee = $this->deliveryService->calculateDeliveryFee(
            $items,
            $user->location,
            null // Let the service lookup the province
        );

        // Store delivery fee in session for Stripe to use
        session(['delivery_fee' => $deliveryFee]);

        return redirect()->back()->with('success', 'Delivery address updated successfully');
    }
}
