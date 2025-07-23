<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller; // Extend the base Controller
use App\Models\Orders;
use App\Models\CustomOrder;
use App\Models\CustomOrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\Log;

class OrderApiController extends Controller
{
    /**
     * Display a listing of orders for the admin panel, with tab and status filtering,
     * returning data as JSON.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    
         public function adminOrders(Request $request)
    {
        Log::info('--- OrderApiController@adminOrders Debug ---');
        Log::info('Full Request URL:', ['url' => $request->fullUrl()]);
        Log::info('Request Method:', ['method' => $request->method()]);
        Log::info('Request Headers:', $request->headers->all());

        $sanctumUser = Auth::guard('sanctum')->user();

        if ($sanctumUser) {
            Log::info('AUTHENTICATED via Sanctum! Attempting to dump user object.');
            // DUMP AND DIE HERE to see what $sanctumUser actually is
            dd($sanctumUser); // THIS LINE WILL HALT EXECUTION AND SHOW YOU THE OBJECT
        } else {
            $defaultUser = Auth::user();
            if ($defaultUser) {
                Log::info('AUTHENTICATED via default guard! Attempting to dump user object.');
                dd($defaultUser); // THIS LINE WILL HALT EXECUTION AND SHOW YOU THE OBJECT
            } else {
                Log::warning('*** NOT AUTHENTICATED in OrderApiController@adminOrders! Request will likely fail. ***');
                // If it's still unauthenticated, we'll see the 401 message from React Native
                // and no dd() output. This means we need to debug earlier in the middleware chain.
            }
        }

        Log::info('--- End OrderApiController@adminOrders Debug ---');

        $tab = $request->get('tab', 'all');
        $status = $request->get('status', 'All');

        $normalOrdersQuery = Orders::with('user.school');
        $customOrdersQuery = CustomOrder::with('user.school')->withCount('items');

        if ($tab === 'orders') {
            if ($status !== 'All') {
                $normalOrdersQuery->where('status', $status);
            } else {
                $normalOrdersQuery->where('status', '!=', 'delivered');
            }
        } elseif ($tab === 'custom') {
            if ($status !== 'All') {
                $customOrdersQuery->where('status', $status);
            } else {
                $customOrdersQuery->where('status', '!=', 'delivered');
            }
        } elseif ($tab === 'all') {
            $normalOrdersQuery->where('status', '!=', 'delivered');
            $customOrdersQuery->where('status', '!=', 'delivered');
        }

        $normalOrders = $normalOrdersQuery->latest()->get();
        foreach ($normalOrders as $order) {
            $order->is_custom = false;
        }

        $customOrders = $customOrdersQuery->latest()->get();
        foreach ($customOrders as $order) {
            $order->is_custom = true;
        }

        $orders = collect();

        if ($tab === 'completed') {
            $normalOrders = Orders::with('user.school')
                ->where('status', 'delivered')
                ->latest()->get();
            foreach ($normalOrders as $order) {
                $order->is_custom = false;
            }

            $customOrders = CustomOrder::with('user.school')->withCount('items')
                ->where('status', 'delivered')
                ->latest()->get();
            foreach ($customOrders as $order) {
                $order->is_custom = true;
            }
            $orders = $normalOrders->merge($customOrders)->sortByDesc('created_at')->values();
        } elseif ($tab === 'orders') {
            $orders = $normalOrders;
        } elseif ($tab === 'custom') {
            $orders = $customOrders;
        } else { // 'all' tab
            $orders = $normalOrders->merge($customOrders)->sortByDesc('created_at')->values();
        }

        $normalOrdersCount = Orders::where('status', '!=', 'delivered')->count();
        $customOrdersCount = CustomOrder::where('status', '!=', 'delivered')->count();
        $allOrdersCount = $normalOrdersCount + $customOrdersCount;

        $completedNormalCount = Orders::where('status', 'delivered')->count();
        $completedCustomCount = CustomOrder::where('status', 'delivered')->count();
        $completedOrdersCount = $completedNormalCount + $completedCustomCount;

        return response()->json([
            'orders' => $orders,
            'tab' => $tab,
            'status' => $status,
            'normalOrdersCount' => $normalOrdersCount,
            'customOrdersCount' => $customOrdersCount,
            'allOrdersCount' => $allOrdersCount,
            'completedOrdersCount' => $completedOrdersCount,
        ]);
    }
    /**
     * Fetches orders based on type (normal/custom) and status, returning data as JSON.
     * This method might be redundant if adminOrders handles all filtering for the main view.
     * Consider consolidating or clarifying its purpose.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchOrders(Request $request)
    {
        $type = $request->get('type', 'normal');
        $status = $request->get('status', 'all');

        if ($type === 'normal') {
            $orders = Orders::with('user');
            if ($status !== 'all') {
                $orders->where('status', $status);
            }
            $orders = $orders->latest()->get();
        } else {
            $orders = CustomOrder::with(['user', 'items']);
            if ($status !== 'all') {
                $orders->where('status', $status);
            }
            $orders = $orders->latest()->get();
        }

        return response()->json([
            'orders' => $orders,
            'type' => $type,
        ]);
    }

    /**
     * Display the specified normal or custom order, returning data as JSON.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $order = Orders::with('user')->find($id);
        $type = 'normal';

        if (!$order) {
            $order = CustomOrder::with('user')->find($id);
            $type = 'custom';
        }

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        return response()->json([
            'order' => $order,
            'type' => $type,
        ]);
    }

    /**
     * Update the status of a given order (normal or custom).
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
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

    /**
     * Display details of a normal order with its items, including search,
     * returning all filtered data as JSON (pagination removed).
     *
     * @param int $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function adminShow($id, Request $request)
    {
        $search = $request->input('search');

        $order = Orders::with('orderDetails.product')->findOrFail($id);

        $allItems = $order->orderDetails->map(function ($detail) {
            // Ensure proper structure for gathered status, even if not stored in normal order items
            // Assuming default to false if not explicitly set
            return [
                'id' => $detail->id, // Important for identifying items for toggleGathered
                'name' => $detail->product->name ?? 'Unknown',
                'brand' => $detail->product->brand ?? 'N/A',
                'unit' => $detail->product->unit ?? 'N/A',
                'quantity' => $detail->quantity,
                'price' => $detail->product->price ?? 0, // Include price for total calculation
                'description' => $detail->product->description ?? 'N/A',
                'photo' => $detail->product->photo ?? null,
                'gathered' => false, // Default to false for normal order items
            ];
        });

        if ($search) {
            $filteredItems = $allItems->filter(function ($item) use ($search) {
                $searchLower = strtolower($search);
                return str_contains(strtolower($item['name']), $searchLower) ||
                       str_contains(strtolower($item['brand'] ?? ''), $searchLower) ||
                       str_contains(strtolower($item['description'] ?? ''), $searchLower);
            });
        } else {
            $filteredItems = $allItems;
        }

        // Return all filtered items directly, no pagination
        return response()->json([
            'order' => $order,
            'items' => $filteredItems->values()->all(), // Convert to array for consistent JSON output
        ]);
    }

    /**
     * Display details of a custom order with its items, including search,
     * returning all filtered data as JSON (pagination removed).
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
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

        // Return all filtered items directly, no pagination
        return response()->json([
            'order' => $order,
            'items' => $items->values()->all(), // Convert to array for consistent JSON output
        ]);
    }

    /**
     * Display the quotation page for a custom order, returning data as JSON.
     *
     * @param int $orderId
     * @return \Illuminate\Http\JsonResponse
     */
    public function showQuotation($orderId)
    {
        $order = CustomOrder::with('items')->findOrFail($orderId);

        return response()->json([
            'order' => $order,
        ]);
    }

    /**
     * Save the quotation prices for custom order items and update the order status,
     * returning a JSON response.
     *
     * @param Request $request
     * @param int $orderId
     * @return \Illuminate\Http\JsonResponse
     */
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

        // Recalculate total after updating item prices
        $order->total_price = $order->items->sum('total_price');
        $order->status = 'quoted';
        $order->save();

        return response()->json(['success' => true, 'message' => 'Prices saved and order status updated to quoted.']);
    }

    /**
     * Update custom order status to 'gathering' and display items for gathering,
     * returning data as JSON.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function gather($id)
    {
        $order = CustomOrder::with('items')->findOrFail($id);

        if (strtolower($order->status) === 'approved') {
            $order->status = 'gathering';
            $order->save();
        }

        $items = $order->items;

        return response()->json([
            'order' => $order,
            'items' => $items,
        ]);
    }

    /**
     * Toggle the 'gathered' status of a custom order item.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggleGathered(Request $request, $id)
    {
        $item = CustomOrderItem::findOrFail($id);
        $item->gathered = $request->input('gathered', false);
        $item->save();

        return response()->json(['success' => true]);
    }
}