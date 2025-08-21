<?php

namespace App\Http\Controllers;

use App\Models\Orders;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Payment;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $cart = Cart::firstOrCreate(['user_id' => $user->id]);

        $cartItems = $cart->items()->with('product')->get();

        return view('user.cart', compact('cartItems'));
    }

    public function add(Request $request, $productId)
    {
        $cart = Cart::firstOrCreate(['user_id' => Auth::id()]);
        $item = $cart->items()->where('product_id', $productId)->first();

        if ($item) {
            $item->quantity += 1;
            $item->save();
        } else {
            $cart->items()->create([
                'product_id' => $productId,
                'quantity' => 1
            ]);
        }

        return redirect()->back()->with('added_to_cart', 'Product added to cart!');
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $cart = Cart::firstOrCreate(['user_id' => $user->id]);

        // Handle mass update from cart page
        if ($request->has('items')) {
            foreach ($request->items as $id => $data) {
                $productId = $data['product_id'] ?? null;
                $quantity = $data['quantity'] ?? 1;

                if (!$productId) continue;

                $product = Product::find($productId);
                if (!$product) continue;

                // Ensure quantity doesn't exceed available stock
                $quantity = min($quantity, $product->quantity);

                $cartItem = CartItem::find($id);
                if ($cartItem && $cartItem->cart_id === $cart->id) {
                    $cartItem->update(['quantity' => $quantity]);
                }
            }
            return response()->json(['success' => true, 'message' => 'Cart updated successfully.']);
        }

        // Handle single item add from product view (AJAX)
        $validated = validator($request->all(), [
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        if ($validated->fails()) {
            return response()->json(['errors' => $validated->errors()], 422);
        }

        $data = $validated->validated();

        $product = Product::find($data['product_id']);
        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        // Ensure quantity doesn't exceed available stock
        $quantity = min($data['quantity'], $product->quantity);

        $cartItem = $cart->items()->where('product_id', $product->id)->first();
        if ($cartItem) {
            // If item exists, just update its quantity (increment by requested quantity)
            $newQuantity = $cartItem->quantity + $quantity;
            $cartItem->update(['quantity' => min($newQuantity, $product->quantity)]);
        } else {
            // If item doesn't exist, create it
            $cart->items()->create([
                'product_id' => $product->id,
                'quantity' => $quantity,
            ]);
        }

        return response()->json([
            'message' => 'Added to cart',
            'cart_count' => $cart->items()->sum('quantity'), // Get total count of all items in cart
        ]);
    }

    public function remove($itemId)
    {
        $item = CartItem::findOrFail($itemId);
        $item->delete();

        return response()->json(['success' => true]);
    }

    // This method handles displaying the checkout page for cart items
    public function form()
    {
        $cart = Cart::where('user_id', Auth::id())->first(); // Use first() as it might not exist
        $items = collect(); // Initialize as an empty collection
        if ($cart) {
            $items = $cart->items()->with('product')->get();
        }

        $minDate = now()->format('Y-m-d');
        $maxDate = now()->addMonth()->format('Y-m-d'); // Increased to one month for flexibility

        return view('user.checkout', compact('items', 'minDate', 'maxDate'));
    }

    // This method handles displaying the checkout page for a single 'Buy Now' product
    public function checkoutNow(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::findOrFail($request->product_id);

        if ($request->quantity > $product->quantity) {
            return back()->with('error', 'Requested quantity exceeds available stock for ' . $product->productName . '.');
        }

        // Create a mock CartItem object for display on the checkout page
        $item = new \stdClass();
        $item->product = $product;
        $item->quantity = $request->quantity;

        $items = collect([$item]); // This will contain only the 'buy now' item

        $minDate = now()->format('Y-m-d');
        $maxDate = now()->addMonth()->format('Y-m-d'); // Increased to one month for flexibility

        // Pass a flag to indicate it's an immediate checkout
        return view('user.checkout', compact('items', 'minDate', 'maxDate'))
                    ->with('checkout_type', 'immediate')
                    ->with('immediate_product_id', $product->id)
                    ->with('immediate_quantity', $request->quantity);
    }

    // This method processes the actual order submission
    public function process(Request $request)
    {
        // Debug 1: Log all request data to see what's coming in
        // \Log::info('Checkout Process Request:', $request->all());
        // dd($request->all()); // Uncomment to stop execution and inspect data

        $request->validate([
            'payment_date' => 'required|date|after_or_equal:today|before_or_equal:' . now()->addMonth()->toDateString(),
            // Only validate product_id and quantity if it's an immediate checkout type
            'product_id' => 'nullable|exists:products,id', // Make nullable as it's not always present
            'quantity' => 'nullable|integer|min:1',       // Make nullable
            'checkout_type' => 'required|in:cart,immediate', // Ensure a type is always present
        ]);

        $user = Auth::user();
        $checkoutType = $request->input('checkout_type'); // Get the checkout type from the form

        $itemsToProcess = collect();
        $total = 0;

        if ($checkoutType === 'immediate') {
            // This is for a single item "Buy Now" checkout
            $product = Product::find($request->product_id);
            $quantity = $request->quantity;

            if (!$product || $quantity === null || $quantity <= 0) {
                return back()->with('error', 'Invalid product or quantity for immediate checkout.');
            }

            if ($quantity > $product->quantity) {
                return back()->with('error', 'Requested quantity exceeds available stock for ' . $product->productName . '.');
            }

            $item = new \stdClass(); // Create a mock item object
            $item->product = $product;
            $item->quantity = $quantity;
            $itemsToProcess->push($item);

            $total = $product->price * $quantity;

        } else { // It's a 'cart' checkout
            $cart = Cart::where('user_id', $user->id)->first();

            if (!$cart) {
                return back()->with('error', 'Your cart is empty.');
            }

            $cartItems = $cart->items()->with('product')->get();

            if ($cartItems->isEmpty()) {
                return back()->with('error', 'Your cart is empty.');
            }

            foreach ($cartItems as $item) {
                $product = $item->product;

                if ($product->quantity < $item->quantity) {
                    return back()->with('error', "Not enough stock for {$product->productName}. Only {$product->quantity} available.");
                }
                $itemsToProcess->push($item);
                $total += $product->price * $item->quantity;
            }
        }

        // If no items were collected (should ideally not happen with proper logic)
        if ($itemsToProcess->isEmpty()) {
            return back()->with('error', 'No items found for checkout.');
        }

        // Create the order
        $order = Orders::create([
            'user_id' => $user->id,
            'type' => $checkoutType, // Use 'cart' or 'immediate'
            'status' => 'new',
            'school_id' => $user->school_id, // Ensure user->school_id exists or is nullable in DB
            'total_price' => $total,
            'payment_method' => 'bank_check',
            'Orderdate' => Carbon::now(),
        ]);

        // Process order items and decrement stock
        foreach ($itemsToProcess as $item) {
            $product = $item->product;

            // Decrement product quantity
            $product->decrement('quantity', $item->quantity);

            $order->items()->create([
                'product_id' => $product->id,
                'quantity' => $item->quantity,
                'price' => $product->price,
            ]);
        }

        // Create payment record
        Payment::create([
            'order_id' => $order->id,
            'order_type' => 'order', // Make sure this matches your payment model's enum/options
            'payment_date' => $request->payment_date,
        ]);

        // Clear the cart ONLY if it was a 'cart' checkout
        if ($checkoutType === 'cart') {
            $cart->items()->delete();
        }

        return redirect()->route('user.order.index')->with('success', 'Order placed successfully!');
    }
}