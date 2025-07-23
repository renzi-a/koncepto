<?php

namespace App\Http\Controllers;

use App\Models\OrderDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Orders;
use App\Models\CustomOrder;
use App\Models\Product;
use App\Models\User;
use App\Models\School;

class AdminController extends Controller
{
public function index(Request $request)
{
    $year = $request->input('year');

    $startDate = $year ? Carbon::createFromDate($year)->startOfYear() : Carbon::now()->startOfYear();
    $endDate = $year ? Carbon::createFromDate($year)->endOfYear() : Carbon::now()->endOfYear();

    $pendingOrders = Orders::where('status', 'new')
        ->whereBetween('created_at', [$startDate, $endDate])
        ->count();

    $completedOrders = Orders::where('status', 'delivered')
        ->whereBetween('created_at', [$startDate, $endDate])
        ->count();

    $orderRevenue = OrderDetail::whereHas('order', function ($query) use ($startDate, $endDate) {
        $query->where('status', 'delivered')
              ->whereBetween('created_at', [$startDate, $endDate]);
    })->sum(DB::raw('price * quantity'));

    $customPending = CustomOrder::whereIn('status', [
        'to_be_quoted',
        'quoted',
        'approved',
        'gathering'
    ])
    ->whereBetween('created_at', [$startDate, $endDate])
    ->count();


    $customCompleted = CustomOrder::where('status', 'delivered')
        ->whereBetween('created_at', [$startDate, $endDate])
        ->count();

    $customRevenue = \App\Models\CustomOrderItem::whereHas('customOrder', function ($query) use ($startDate, $endDate) {
        $query->where('status', 'delivered')
              ->whereBetween('created_at', [$startDate, $endDate]);
    })->sum('total_price');

    $totalRevenue = $orderRevenue + $customRevenue;

    $monthlyRevenue = collect(range(1, 12))->map(function ($month) use ($startDate) {
        $normal = Orders::where('status', 'delivered')
            ->whereYear('created_at', $startDate->year)
            ->whereMonth('created_at', $month)
            ->with('orderDetails')
            ->get()
            ->sum(function ($order) {
                return optional($order->orderDetails)->sum(function ($detail) {
                    return ($detail->price ?? 0) * ($detail->quantity ?? 0);
                }) ?? 0;
            });

        $custom = \App\Models\CustomOrderItem::whereHas('customOrder', function ($query) use ($startDate, $month) {
            $query->where('status', 'delivered')
                  ->whereYear('created_at', $startDate->year)
                  ->whereMonth('created_at', $month);
        })->sum('total_price');

        return $normal + $custom;
    })->toArray();

    $topProducts = DB::table('order_details')
        ->join('orders', 'order_details.order_id', '=', 'orders.id')
        ->join('products', 'order_details.product_id', '=', 'products.id')
        ->where('orders.status', 'delivered')
        ->whereBetween('orders.created_at', [$startDate, $endDate])
        ->select('products.productName as product_name', DB::raw('SUM(order_details.quantity) as total'))
        ->groupBy('products.productName')
        ->orderByDesc('total')
        ->limit(10)
        ->get();

    $schoolSales = School::withCount([
        'orders as total_normal_orders' => function ($query) use ($startDate, $endDate) {
            $query->whereBetween('orders.created_at', [$startDate, $endDate]);
        },
        'customOrder as total_custom_orders' => function ($query) use ($startDate, $endDate) {
            $query->whereBetween('custom_orders.created_at', [$startDate, $endDate]);
        }
    ])->get()->map(function ($school) {
        $school->total_orders = $school->total_normal_orders + $school->total_custom_orders;
        return $school;
    });

$schoolSales->transform(function ($school) use ($startDate, $endDate) {
    $normalRevenue = OrderDetail::whereHas('order', function ($query) use ($school, $startDate, $endDate) {
        $query->where('status', 'delivered')
              ->whereBetween('created_at', [$startDate, $endDate])
              ->whereHas('user', function ($q) use ($school) {
                  $q->where('school_id', $school->id);
              });
    })->sum(DB::raw('price * quantity'));

    $customRevenue = \App\Models\CustomOrderItem::whereHas('customOrder', function ($query) use ($school, $startDate, $endDate) {
        $query->where('status', 'delivered')
              ->whereBetween('created_at', [$startDate, $endDate])
              ->whereHas('user', function ($q) use ($school) {
                  $q->where('school_id', $school->id);
              });
    })->sum('total_price');

    $school->total_revenue = $normalRevenue + $customRevenue;
    return $school;
});


    $previousStart = (clone $startDate)->subYear();
    $previousEnd = (clone $endDate)->subYear();

    $previousOrderRevenue = OrderDetail::whereHas('order', function ($query) use ($previousStart, $previousEnd) {
        $query->where('status', 'delivered')
              ->whereBetween('created_at', [$previousStart, $previousEnd]);
    })->sum(DB::raw('price * quantity'));

    $previousCustomRevenue = \App\Models\CustomOrderItem::whereHas('customOrder', function ($query) use ($previousStart, $previousEnd) {
        $query->where('status', 'delivered')
              ->whereBetween('created_at', [$previousStart, $previousEnd]);
    })->sum('total_price');

    $previousRevenue = $previousOrderRevenue + $previousCustomRevenue;

    $revenueChange = $previousRevenue > 0
        ? (($totalRevenue - $previousRevenue) / $previousRevenue) * 100
        : 0;

    $previousPendingOrders = Orders::where('status', 'new')
    ->whereBetween('created_at', [$previousStart, $previousEnd])
    ->count();

    
$previousCustomPending = CustomOrder::whereIn('status', [
        'to_be_quoted',
        'quoted',
        'approved',
        'gathering'
    ])
    ->whereBetween('created_at', [$previousStart, $previousEnd])
    ->count();

    $previousPendingTotal = $previousPendingOrders + $previousCustomPending;
    $currentPendingTotal = $pendingOrders + $customPending;

    $pendingChange = $previousPendingTotal > 0
        ? (($currentPendingTotal - $previousPendingTotal) / $previousPendingTotal) * 100
        : 0;
    $previousCompletedOrders = Orders::where('status', 'delivered')
    ->whereBetween('created_at', [$previousStart, $previousEnd])
    ->count();

$previousCustomCompleted = CustomOrder::where('status', 'delivered')
    ->whereBetween('created_at', [$previousStart, $previousEnd])
    ->count();

$previousCompletedTotal = $previousCompletedOrders + $previousCustomCompleted;
$currentCompletedTotal = $completedOrders + $customCompleted;

$completedChange = $previousCompletedTotal > 0
    ? (($currentCompletedTotal - $previousCompletedTotal) / $previousCompletedTotal) * 100
    : 0;
$products = Product::with('category')->get();
$salesTrendLabels = [
    'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
    'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
];
$salesTrendData = $monthlyRevenue;

$topProductsLabels = $topProducts->pluck('product_name')->toArray();
$topProductsData = $topProducts->pluck('total')->toArray();

return view('admin.dashboard', [
    'pendingOrders' => $pendingOrders,
    'completedOrders' => $completedOrders,
    'customPending' => $customPending,
    'customCompleted' => $customCompleted,
    'totalRevenue' => $totalRevenue,
    'previousRevenue' => $previousRevenue,
    'revenueChange' => $revenueChange,
    'pendingChange' => $pendingChange,
    'completedChange' => $completedChange,
    'monthlyRevenue' => $monthlyRevenue,
    'topProducts' => $topProducts,
    'schoolSales' => $schoolSales,
    'products' => $products,
    'salesTrendLabels' => $salesTrendLabels,
    'salesTrendData' => $salesTrendData,
    'topProductsLabels' => $topProductsLabels,
    'topProductsData' => $topProductsData,
]);

}

}
