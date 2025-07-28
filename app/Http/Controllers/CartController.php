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
        $user = auth::user();

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
    if ($request->has('items')) {
        foreach ($request->items as $id => $data) {
            $productId = $data['product_id'] ?? null;
            $quantity = $data['quantity'] ?? 1;

            if (!$productId) continue;

            $product = Product::find($productId);
            if (!$product) continue;

            $quantity = min($quantity, $product->quantity);

            $cartItem = CartItem::find($id);
            if ($cartItem && $cartItem->cart_id === $cart->id) {
                $cartItem->update(['quantity' => $quantity]);
            }
        }

        return response()->json(['success' => true]);
    }
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

    $quantity = min($data['quantity'], $product->quantity);

$cartItem = $cart->items()->where('product_id', $product->id)->first();
if ($cartItem) {
    $cartItem->update(['quantity' => $cartItem->quantity + $quantity]); 
} else {
    $cart->items()->create([
        'product_id' => $product->id,
        'quantity' => $quantity,
    ]);
}
    return response()->json([
    'message' => 'Added to cart',
    'cart_count' => $cart->items()->sum('quantity'),
]);

}
    public function remove($itemId)
    {
        $item = CartItem::findOrFail($itemId);
        $item->delete();

        return response()->json(['success' => true]);
    }

public function form()
{
    $cart = Cart::where('user_id', Auth::id())->firstOrFail();
    $items = $cart->items()->with('product')->get();

    $minDate = now()->format('Y-m-d');
    $maxDate = now()->addMonth()->format('Y-m-d');

    return view('user.checkout', compact('items', 'minDate', 'maxDate'));
}


public function process(Request $request)
    {
        $request->validate([
            'payment_date' => 'required|date|after_or_equal:today|before_or_equal:' . now()->addMonth()->toDateString(),
        ]);

        $user = Auth::user();
        $cart = Cart::where('user_id', $user->id)->first();

        if (!$cart) return back()->with('error', 'Your cart is empty.');

        $cartItems = $cart->items()->with('product')->get();

        if ($cartItems->isEmpty()) {
            return back()->with('error', 'Your cart is empty.');
        }

        $total = 0;

        foreach ($cartItems as $item) {
            $product = $item->product;

            if ($product->quantity < $item->quantity) {
                return back()->with('error', "Not enough stock for {$product->productName}.");
            }

            $total += $product->price * $item->quantity;
        }

            $order = Orders::create([
                'user_id' => $user->id,
                'type' => 'normal',
                'status' => 'new',
                'school_id' => $user->school_id, 
                'total_price' => $total,
                'payment_method' => 'bank_check',
                'Orderdate' => Carbon::now(),
            ]);

        foreach ($cartItems as $item) {
            $product = $item->product;

            $product->decrement('quantity', $item->quantity);

            $order->items()->create([
                'product_id' => $product->id,
                'quantity' => $item->quantity,
                'price' => $product->price,
            ]);
        }
        Payment::create([
            'order_id' => $order->id,
            'order_type' => 'order',
            'payment_date' => $request->payment_date,
        ]);

        $cart->items()->delete();

        return redirect()->route('user.order.index')->with('success', 'Order placed successfully!');
    }


}