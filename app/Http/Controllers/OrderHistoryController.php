<?php

namespace App\Http\Controllers;

use App\Models\CustomOrder;
use Illuminate\Http\Request;
use App\Models\Orders;
use App\Models\OrderHistory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;


class OrderHistoryController extends Controller
{
   public function index(Request $request)
    {
        if (Auth::user()->role !== 'school_admin') {
            abort(403, 'Access denied');
        }

        $userId = Auth::id();
        $schoolId = Auth::user()->school_id;
        $search = $request->input('search');
        $perPage = 10;
        $page = LengthAwarePaginator::resolveCurrentPage();

        $deliveredNormalOrders = Orders::where('school_id', $schoolId)
            ->where('status', 'delivered')
            ->when($search, fn ($query, $search) => $query->where('id', 'like', "%$search%"))
            ->get()
            ->each(function ($order) {
                $order->type = 'normal';
                $order->order_status = 'Delivered';
            });

        $deliveredCustomOrders = CustomOrder::with('items')
            ->whereHas('user', fn ($query) => $query->where('school_id', $schoolId))
            ->get()
            ->filter(fn ($customOrder) => $customOrder->items->isNotEmpty() && $customOrder->items->every('gathered'))
            ->when($search, fn ($query) => $query->filter(fn ($order) => str_contains($order->id, $search)))
            ->each(function ($order) {
                $order->type = 'custom';
                $order->order_status = 'Delivered';
            });
            
        $cancelledOrders = OrderHistory::where('user_id', $userId)
            ->when($search, fn ($query, $search) => $query->where('custom_order_id', 'like', "%$search%"))
            ->get()
            ->each(function ($order) {
                $order->type = $order->custom_order_id ? 'custom' : 'normal';
                $order->order_status = 'Cancelled';
            });
        
        $merged = $deliveredNormalOrders
            ->merge($deliveredCustomOrders)
            ->merge($cancelledOrders)
            ->sortByDesc('created_at')
            ->values();

        $paginated = new LengthAwarePaginator(
            $merged->forPage($page, $perPage),
            $merged->count(),
            $perPage,
            $page,
            ['path' => url()->current(), 'query' => $request->query()]
        );
        
        return view('user.order.history', [
            'orders' => $paginated,
            'search' => $search
        ]);
    }

public function show($id)
{
    $userId = Auth::id();

    $order = OrderHistory::with('customOrder')->where('id', $id)->where('user_id', $userId)->first();

    if ($order) {
        $order->type = 'custom';
        $items = collect($order->items);
        $perPage = 10;
        $page = request()->get('page', 1);

        $paginatedItems = new LengthAwarePaginator(
            $items->forPage($page, $perPage),
            $items->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return view('user.order.history-show', [
            'order' => $order,
            'items' => $paginatedItems
        ]);
    }

    $order = Orders::with('orderDetails')->where('id', $id)->where('user_id', $userId)->firstOrFail();
    $order->type = 'normal';

    $items = $order->orderDetails()->paginate(10);

    return view('user.order.history-show', [
        'order' => $order,
        'items' => $items
    ]);
}
public function bulkDelete(Request $request)
{
    $userId = Auth::id();
    $orderIds = $request->input('orders', []);

    if (empty($orderIds)) {
        return redirect()->back()->with('error', 'No orders selected for deletion.');
    }

    Orders::whereIn('id', $orderIds)->where('user_id', $userId)->delete();

    OrderHistory::whereIn('id', $orderIds)->where('user_id', $userId)->delete();

    return redirect()->back()->with('success', 'Selected orders deleted successfully.');
}
public function destroy($id)
{
    $userId = Auth::id();

    $orderDeleted = Orders::where('id', $id)->where('user_id', $userId)->delete();

    if (!$orderDeleted) {
        OrderHistory::where('id', $id)->where('user_id', $userId)->delete();
    }

    return redirect()->back()->with('success', 'Order deleted successfully.');
}


}
