<?php

namespace App\Http\Controllers;

use App\Models\Orders;
use App\Models\CustomOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{

public function adminOrders(Request $request)
{
    $tab = $request->get('tab', 'all');
    $status = $request->get('status', 'All');

    $normalOrders = Orders::with('user');
    if ($status !== 'All') {
        $normalOrders->where('status', $status);
    }
    $normalOrders = $normalOrders->latest()->get();
    foreach ($normalOrders as $order) {
        $order->is_custom = false;
    }

    $customOrders = CustomOrder::with('user')->withCount('items');
    if ($status !== 'All') {
        $customOrders->where('status', $status);
    }
    $customOrders = $customOrders->latest()->get();
    foreach ($customOrders as $order) {
        $order->is_custom = true;
    }

    if ($tab === 'orders') {
        $orders = $normalOrders;
    } elseif ($tab === 'custom') {
        $orders = $customOrders;
    } else {
        $orders = $normalOrders->merge($customOrders)->sortByDesc('created_at');
    }

    return view('admin.orders', compact('orders', 'normalOrders', 'customOrders', 'tab', 'status'));
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
}
