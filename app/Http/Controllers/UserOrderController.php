<?php

namespace App\Http\Controllers;

use App\Models\CustomOrder;
use App\Models\Orders;
use App\Models\OrderHistory;
use App\Models\OrderDetail;
use App\Models\School;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use App\Models\Payment;

class UserOrderController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $currentYear = $request->input('year', date('Y'));

        $availableYears = Orders::where('user_id', $user->id)
            ->selectRaw('YEAR(created_at) as year')
            ->distinct()
            ->pluck('year')
            ->merge(
                CustomOrder::where('user_id', $user->id)
                    ->selectRaw('YEAR(created_at) as year')
                    ->distinct()
                    ->pluck('year')
            )
            ->unique()
            ->sortDesc();

        $normalOrders = Orders::where('user_id', $user->id)
            ->whereYear('created_at', $currentYear)
            ->latest()
            ->get();

        $customOrders = CustomOrder::withCount('items')
            ->where('user_id', $user->id)
            ->whereYear('created_at', $currentYear)
            ->latest()
            ->get();

        return view('user.order.index', compact('normalOrders', 'customOrders', 'currentYear', 'availableYears'));
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
    $orders = collect();

    // Normal orders
    $normalOrders = Orders::with('school')
        ->where('user_id', Auth::id())
        ->where('status', 'delivering')
        ->get()
        ->map(function ($order) {
            $order->type = 'normal';
            return $order;
        });

    // Custom orders
    $customOrders = CustomOrder::where('user_id', Auth::id())
        ->where('status', 'delivering')
        ->get()
        ->map(function ($order) {
            $order->type = 'custom';
            $school = School::find(optional($order->user)->school_id);
            $order->school = $school;
            return $order;
        });

    $orders = $normalOrders->merge($customOrders);

    return view('user.order.track', compact('orders'));
}


public function getOrderLocation($type, $id)
{
    if ($type === 'order') {
        return DB::table('orders')->where('id', $id)->select('driver_latitude', 'driver_longitude')->first();
    } else {
        return DB::table('custom_orders')->where('id', $id)->select('driver_latitude', 'driver_longitude')->first();
    }
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
                'price' => $detail->price,
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
    // Check if the authenticated user owns the order
    if ($order->user_id !== Auth::id()) {
        abort(403);
    }

    // Check if the order can be canceled based on its status
    if (in_array($order->status, ['to be delivered', 'delivered'])) {
        return redirect()->back()->with('error', 'You cannot cancel this order at this stage.');
    }

    // Validate the reason for cancellation
    $request->validate([
        'reason' => 'nullable|string|max:500',
    ]);

    // Update the original order's status to 'cancelled'
    $order->update(['status' => 'cancelled']);

    // Get the products and restore their quantities
    $items = [];
    foreach ($order->orderDetails as $detail) {
        $product = $detail->product;
        if ($product) {
            // Restore the product quantity
            $product->increment('quantity', $detail->quantity);

            // Populate the items array for the order history
            $items[] = [
                'id' => $product->id, 
                'name' => $product->productName,
                'quantity' => $detail->quantity,
                'price' => $product->price,
            ];
        }
    }

    // Create a new entry in the OrderHistory table
    OrderHistory::create([
        'original_order_id' => $order->id,
        'user_id' => $order->user_id,
        'status' => 'cancelled',
        'order_type' => 'normal',
        'reason' => $request->reason,
        'items' => json_encode($items), // This is the most crucial part.
    ]);

    return redirect()->route('user.order-history')->with('success', 'Order cancelled and stock restored.');
}
}