<?php

namespace App\Http\Controllers;

use App\Models\Orders;
use App\Models\CustomOrder;
use App\Models\CustomOrderItem;
use Illuminate\Http\Request;
use App\Models\OrderDetail;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function adminOrders(Request $request)
    {
        $section = $request->get('section', 'schools'); // default to schools
        $tab = $request->get('tab', 'all');
        $status = strtolower($request->get('status', 'all'));

        // Define the pending statuses for each order type
        $normalPendingStatuses = ['new', 'processing', 'to be delivered'];
        $customPendingStatuses = ['to be quoted', 'quoted', 'approved', 'processing', 'to be delivered'];

        $normalOrdersQuery = Orders::with('user.school');
        $customOrdersQuery = CustomOrder::with('user.school')
            ->withCount('items')
            ->withCount(['items as gathered_items_count' => function ($query) {
                $query->where('gathered', true);
            }]);

        if ($section === 'schools') {
            if ($tab === 'orders') {
                if ($status !== 'all') {
                    $normalOrdersQuery->where('status', $status);
                } else {
                    $normalOrdersQuery->whereIn('status', $normalPendingStatuses);
                }
            } elseif ($tab === 'all') {
                // For 'all' tab, we handle a specific status filter
                if ($status !== 'all') {
                    $normalOrdersQuery->where('status', $status);
                    $customOrdersQuery->where('status', $status);
                } else {
                    $normalOrdersQuery->whereIn('status', $normalPendingStatuses);
                    $customOrdersQuery->whereIn('status', $customPendingStatuses);
                }
            } elseif ($tab === 'custom') {
                if ($status !== 'all') {
                    $customOrdersQuery->where('status', $status);
                } else {
                    $customOrdersQuery->whereIn('status', $customPendingStatuses);
                }
            } elseif ($tab === 'completed') {
                $normalOrdersQuery->where('status', 'delivered');
                $customOrdersQuery->where('status', 'delivered');
            }
        }

        $normalOrders = $normalOrdersQuery->latest()->get()->each(fn($o) => $o->is_custom = false);
        $customOrders = $customOrdersQuery->latest()->get()->each(fn($o) => $o->is_custom = true);

        if ($tab === 'orders') {
            $orders = $normalOrders;
        } elseif ($tab === 'custom') {
            $orders = $customOrders;
        } else {
            // ✅ FIX: Use concat() to combine the collections,
            // which prevents data loss from duplicate keys.
            $orders = $normalOrders->concat($customOrders)->sortByDesc('created_at')->values();
        }
        
        // Count the orders for each tab
        $normalOrdersCount = Orders::whereIn('status', $normalPendingStatuses)->count();
        $customOrdersCount = CustomOrder::whereIn('status', $customPendingStatuses)->count();
        $allOrdersCount = $normalOrdersCount + $customOrdersCount;

        $completedNormalCount = Orders::where('status', 'delivered')->count();
        $completedCustomCount = CustomOrder::where('status', 'delivered')->count();
        $completedOrdersCount = $completedNormalCount + $completedCustomCount;

        return view('admin.orders', compact(
            'orders',
            'normalOrders',
            'customOrders',
            'tab',
            'status',
            'section',
            'normalOrdersCount',
            'customOrdersCount',
            'allOrdersCount',
            'completedOrdersCount'
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

    /**
     * Corrected show method to handle regular orders.
     * It now uses the logic from the previously correct 'adminShow'.
     */
    public function show($id)
    {
        $order = Orders::with('orderDetails.product', 'user.school')->findOrFail($id);

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
    
        $items = $this->paginateCollection($allItems->values());
    
        return view('admin.orders-show', compact('order', 'items'));
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
        $order = CustomOrder::with('user.school', 'items')->findOrFail($id);
    
        // Change 'gathering' to 'processing'
        if (strtolower($order->status) === 'processing') {
            return redirect()->route('admin.custom-orders.gather', $order->id);
        }
    
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

        return redirect()->route('admin.orders', $orderId)
                          ->with('success', 'Prices saved and order status updated to quoted.');
    }
    
    public function gather($id)
    {
        $order = CustomOrder::with('items')->findOrFail($id);

        // Change 'approved' and 'gathering' to 'processing'
        if (strtolower($order->status) === 'approved') {
            $order->status = 'processing';
            $order->save();
        }
        
        $items = $order->items;
        $totalItems = $items->count();
        $gatheredItems = $items->where('gathered', true)->count();
        
        return view('admin.gather', compact('order', 'items', 'totalItems', 'gatheredItems'));
    }

    public function gatherPdf($id)
    {
        $order = CustomOrder::with(['items', 'user.school'])->findOrFail($id);
        $items = $order->items;

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.gather-pdf', compact('order', 'items'));

        return $pdf->stream("CustomOrder-{$order->id}-Gathered.pdf");
    }

    public function toggleGathered(Request $request, $id)
    {
        $item = CustomOrderItem::findOrFail($id);
        $item->gathered = $request->input('gathered', false);
        $item->save();

        return response()->json(['success' => true]);
    }

    public function saveGatheringInfo(Request $request, $orderId)
    {
        $order = CustomOrder::with('items')->findOrFail($orderId);
        
        $gatheredItemsCount = $order->items->where('gathered', true)->count();
        
        $totalItemsCount = $order->items->count();

        if ($gatheredItemsCount === $totalItemsCount) {
            $order->status = 'to be delivered';
            $order->save();
            return redirect()->route('admin.orders')->with('success', 'Gathering information saved and order status updated to "To be Delivered".');
        }

        return redirect()->route('admin.orders')->with('warning', 'Not all items have been gathered yet. The order status has not been changed.');
    }

    public function gatherNormal($id)
    {
        $order = Orders::with(['orderDetails.product', 'user.school'])->findOrFail($id);

        if (strtolower($order->status) === 'new') {
            $order->status = 'processing';
            $order->save();
        }
        
        $items = $order->orderDetails;
        $totalItems = $items->count();
        $gatheredItems = $items->where('gathered', true)->count();
        
        return view('admin.gather-normal', compact('order', 'items', 'totalItems', 'gatheredItems'));
    }
    public function toggleNormalGathered(Request $request, $id)
    {
        $orderDetail = OrderDetail::findOrFail($id);
        $orderDetail->gathered = $request->input('gathered');
        $orderDetail->save();

        return response()->json(['success' => true]);
    }

    public function saveNormalGatheringInfo(Request $request, $orderId)
    {
        $order = Orders::with('orderDetails')->findOrFail($orderId);

        $allGathered = $order->orderDetails->every(function ($item) {
            return $item->gathered;
        });

        if ($allGathered) {
            $order->status = 'To be delivered';
            $order->save();
            $message = 'Order status updated to "To be Delivered" and progress saved.';
        } else {
            $message = 'Processing progress saved.';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'redirect' => route('admin.orders')
        ]);
    }
}
