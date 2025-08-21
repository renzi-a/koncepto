<?php

namespace App\Http\Controllers;

use App\Models\CustomOrder;
use App\Models\Orders;
use App\Models\OrderHistory;
use App\Models\OrderDetail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\Payment;

class UserOrderController extends Controller
{
        public function index()
    {
        $user = Auth::user();

        $normalOrders = Orders::where('user_id', $user->id)
            ->latest()
            ->get();

        $customOrders = CustomOrder::withCount('items')
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        return view('user.order.index', compact('normalOrders', 'customOrders'));
    }
    function paginateCollection($items, $perPage = 10, $page = null)
{
    $page = $page ?: (LengthAwarePaginator::resolveCurrentPage() ?: 1);
    $items = collect($items);
    return new LengthAwarePaginator(
        $items->forPage($page, $perPage),
        $items->count(),
        $perPage,
        $page,
        ['path' => request()->url(), 'query' => request()->query()]
    );
}
    public function orderRequest()
    {
        return view('user.order.request');
    }

    public function trackOrder()
    {
        return view('user.order.track');
    }
public function show(Request $request, Orders $order)
{
    $search = $request->input('search');

    $orderDetails = $order->orderDetails()->with('product')->get();
    
    $allItems = $orderDetails->map(function ($detail) {
        $product = $detail->product;
        return [
            'name' => $product->productName ?? 'N/A',
            'brand' => $product->brandName ?? 'N/A',
            'unit' => $product->unit ?? 'N/A',
            'description' => $product->description ?? 'N/A',
            'photo' => $product->image ?? null,
            'quantity' => $detail->quantity,
            'price' => $detail->price, // <-- ADDED THIS LINE
        ];
    });

    if ($search) {
        $filteredItems = $allItems->filter(function ($item) use ($search) {
            return str_contains(strtolower($item['name']), strtolower($search));
        });
    } else {
        $filteredItems = $allItems;
    }

    $items = $this->paginateCollection($filteredItems->values());

    return view('user.order.show', compact('order', 'items', 'search'));
}


public function cancel(Request $request, Orders $order)
{
    if ($order->user_id !== Auth::id()) {
        abort(403);
    }

    if (in_array($order->status, ['gathering', 'to be delivered', 'delivered'])) {
        return redirect()->back()->with('error', 'You cannot cancel this order at this stage.');
    }

    $request->validate([
        'reason' => 'nullable|string|max:500',
    ]);

    $order->loadMissing('orderDetails.product');

    $items = $order->orderDetails->map(function ($item) {
        return [
            'product_id' => $item->product_id,
            'name' => optional($item->product)->name,
            'quantity' => $item->quantity,
            'price' => $item->price,
        ];
    });

    foreach ($order->orderDetails as $detail) {
        if ($detail->product) {
            $detail->product->increment('quantity', $detail->quantity);
        }
    }

    OrderHistory::create([
        'original_order_id' => $order->id,
        'user_id' => $order->user_id,
        'status' => 'cancelled',
        'order_type' => 'normal',
        'reason' => $request->reason,
        'items' => $items,
    ]);

    $order->orderDetails()->delete();  
    $order->delete();

    return redirect()->route('user.order-history')->with('success', 'Order cancelled and stock restored.');
}


}
