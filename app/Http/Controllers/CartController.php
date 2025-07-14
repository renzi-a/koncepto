<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
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

}
