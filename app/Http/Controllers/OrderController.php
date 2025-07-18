<?php

namespace App\Http\Controllers;

use App\Models\Orders;
use App\Models\CustomOrder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{

public function adminOrders(Request $request)
{
    $tab = $request->get('tab', 'all');
    $status = $request->get('status', 'All');

    $normalOrdersQuery = Orders::with('user.school');
    if ($tab === 'orders' || $tab === 'all') {
        if ($status !== 'All') {
            $normalOrdersQuery->where('status', $status);
        }
    }
    $normalOrders = $normalOrdersQuery->latest()->get();
    foreach ($normalOrders as $order) {
        $order->is_custom = false;
    }

    $customOrdersQuery = CustomOrder::with('user.school')->withCount('items');
    if ($tab === 'custom' || $tab === 'all') {
        if ($status !== 'All') {
            $customOrdersQuery->where('status', $status);
        }
    }
    $customOrders = $customOrdersQuery->latest()->get();
    foreach ($customOrders as $order) {
        $order->is_custom = true;
    }

    if ($tab === 'orders') {
        $orders = $normalOrders;
    } elseif ($tab === 'custom') {
        $orders = $customOrders;
    } else {
        $orders = $normalOrders->merge($customOrders)->sortByDesc('created_at')->values();
    }

    $normalOrdersCount = Orders::count();
    $customOrdersCount = CustomOrder::count();
    $allOrdersCount = $normalOrdersCount + $customOrdersCount;

    return view('admin.orders', compact(
        'orders',
        'normalOrders',
        'customOrders',
        'tab',
        'status',
        'normalOrdersCount',
        'customOrdersCount',
        'allOrdersCount'
    ));
}



    public function fetchOrders(Request $request)
{
    $type = $request->get('type', 'normal');
    $status = $request->get('status', 'all');

    if ($type === 'normal') {
        $orders = Orders::with('user');
        if ($status !== 'all') $orders->where('status', $status);
        $orders = $orders->latest()->get();
    } else {
        $orders = CustomOrder::with(['user', 'items']);
        if ($status !== 'all') $orders->where('status', $status);
        $orders = $orders->latest()->get();
    }

    return view('admin.orders', compact('orders', 'type'));
}

    public function show($id)
    {
        $order = Orders::with('user')->find($id);
        $type = 'normal';

        if (!$order) {
            $order = CustomOrder::with('user')->find($id);
            $type = 'custom';
        }

        if (!$order) {
            abort(404, 'Order not found');
        }

        return view('admin.orders-show', compact('order', 'type'));
    }



    public function updateOrderStatus(Request $request)
    {
        $request->validate([
            'order_id' => 'required|integer',
            'status' => 'required|string',
            'type' => 'required|in:normal,custom',
        ]);

        if ($request->type === 'normal') {
            $order = Orders::findOrFail($request->order_id);
        } else {
            $order = CustomOrder::findOrFail($request->order_id);
        }

        $order->status = $request->status;
        $order->save();

        return response()->json(['success' => true, 'message' => 'Status updated successfully.']);
    }

public function adminShow($id, Request $request)
{
    $search = $request->input('search');

    $order = Orders::with('orderDetails.product')->findOrFail($id);

    $allItems = $order->orderDetails->map(function ($detail) {
        return [
            'name' => $detail->product->name ?? 'Unknown',
            'brand' => $detail->product->brand ?? 'N/A',
            'unit' => $detail->product->unit ?? 'N/A',
            'quantity' => $detail->quantity,
            'description' => $detail->product->description ?? 'N/A',
            'photo' => $detail->product->photo ?? null,
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

    return view('admin.orders-show', compact('order', 'items', 'search'));
}

protected function paginateCollection($items, $perPage = 10)
{
    $page = request()->get('page', 1);
    $offset = ($page - 1) * $perPage;

    return new LengthAwarePaginator(
        $items->slice($offset, $perPage),
        $items->count(),
        $perPage,
        $page,
        ['path' => request()->url(), 'query' => request()->query()]
    );
}

public function adminCustomShow(Request $request, $id)
{
    $order = CustomOrder::with('user.school')->findOrFail($id);


    $search = $request->input('search');
    $items = collect($order->items ?? []);

    if ($search) {
        $searchLower = strtolower($search);
        $items = $items->filter(function ($item) use ($searchLower) {
            return str_contains(strtolower($item['name']), $searchLower) ||
                   str_contains(strtolower($item['brand'] ?? ''), $searchLower) ||
                   str_contains(strtolower($item['description'] ?? ''), $searchLower);
        });
    }

    $perPage = 10;
    $page = $request->input('page', 1);

    $paginatedItems = new LengthAwarePaginator(
        $items->forPage($page, $perPage)->values(),
        $items->count(),
        $perPage,
        $page,
        ['path' => $request->url(), 'query' => $request->query()]
    );

    return view('admin.custom-orders-show', [
        'order' => $order,
        'items' => $paginatedItems,
        'search' => $search,
    ]);
}

public function showQuotation($orderId, Request $request)
{
    $order = CustomOrder::with('items')->findOrFail($orderId);

    return view('admin.quotation', compact('order'));
}

public function saveQuotationPrices(Request $request, $orderId)
{
    $order = CustomOrder::with('items')->findOrFail($orderId);

    $data = $request->input('prices'); 

    foreach ($order->items as $item) {
        if (isset($data[$item->id])) {
            $price = floatval($data[$item->id]);
            $item->price = $price;
            $item->total_price = $price * $item->quantity;
            $item->save();
        }
    }

    $total = $order->items->sum('total_price');
    $order->status = 'quoted';
    $order->save();

    return redirect()->route('admin.custom-orders.show', $orderId)
                     ->with('success', 'Prices saved and order status updated to quoted.');
}


}
