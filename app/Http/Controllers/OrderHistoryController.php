<?php

namespace App\Http\Controllers;

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
        $search = $request->input('search');
        $perPage = 10;
        $page = LengthAwarePaginator::resolveCurrentPage();

        $completedOrders = Orders::where('user_id', $userId)
            ->where('status', 'delivered')
            ->when($search, function ($query, $search) {
                $query->where('id', 'like', "%$search%");
            })
            ->get()
            ->each(function ($order) {
                $order->type = 'normal';
            });

        $cancelledCustomOrders = OrderHistory::where('user_id', $userId)
            ->when($search, function ($query, $search) {
                $query->where('custom_order_id', 'like', "%$search%");
            })
            ->get()
            ->each(function ($order) {
                $order->type = 'custom';
            });

        $merged = $completedOrders->merge($cancelledCustomOrders)->sortByDesc('created_at')->values();

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
