<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Orders;
use App\Models\Product;
use App\Models\User;
use App\Models\School;

class AdminController extends Controller
{
public function index(Request $request)
{
    $pendingOrdersQuery = Orders::where('status', 'pending');
    $completedOrdersQuery = Orders::where('status', 'completed');
    $orderDetailQuery = DB::table('order_detail')
        ->join('orders', 'order_detail.order_id', '=', 'orders.id')
        ->where('orders.status', 'completed');

    if ($request->filled('year')) {
        $year = $request->year;

        if ($request->filled('quarter')) {
            $quarter = (int) $request->quarter;
            $startMonth = ($quarter - 1) * 3 + 1;
            $endMonth = $startMonth + 2;

            $startDate = Carbon::create($year, $startMonth, 1)->startOfDay();
            $endDate = Carbon::create($year, $endMonth, 1)->endOfMonth()->endOfDay();

            $pendingOrdersQuery->whereBetween('created_at', [$startDate, $endDate]);
            $completedOrdersQuery->whereBetween('created_at', [$startDate, $endDate]);
            $orderDetailQuery->whereBetween('orders.created_at', [$startDate, $endDate]);
        } else {
            $startDate = Carbon::create($year)->startOfYear();
            $endDate = Carbon::create($year)->endOfYear();

            $pendingOrdersQuery->whereYear('created_at', $year);
            $completedOrdersQuery->whereYear('created_at', $year);
            $orderDetailQuery->whereYear('orders.created_at', $year);
        }
    } else {
        $startDate = Carbon::now()->startOfYear();
        $endDate = Carbon::now()->endOfYear();
    }

    $pendingOrders = $pendingOrdersQuery->count();
    $completedOrders = $completedOrdersQuery->count();
    $totalRevenue = $orderDetailQuery->sum(DB::raw('order_detail.price * order_detail.quantity'));

    $previousStart = $startDate->copy()->subMonths(3);
    $previousEnd = $startDate->copy()->subDay();

    $previousRevenue = DB::table('order_detail')
        ->join('orders', 'order_detail.order_id', '=', 'orders.id')
        ->where('orders.status', 'completed')
        ->whereBetween('orders.created_at', [$previousStart, $previousEnd])
        ->sum(DB::raw('order_detail.price * order_detail.quantity'));

    $previousPending = Orders::where('status', 'pending')
        ->whereBetween('created_at', [$previousStart, $previousEnd])
        ->count();

    $previousCompleted = Orders::where('status', 'completed')
        ->whereBetween('created_at', [$previousStart, $previousEnd])
        ->count();

    $calcChange = function ($current, $previous) {
        if ($previous == 0) return $current > 0 ? 100 : 0;
        return round((($current - $previous) / $previous) * 100, 2);
    };

    $revenueChange = $calcChange($totalRevenue, $previousRevenue);
    $pendingChange = $calcChange($pendingOrders, $previousPending);
   
    $completedChange = $calcChange($completedOrders, $previousCompleted);
    $sales = DB::table('order_detail')
        ->join('orders', 'order_detail.order_id', '=', 'orders.id')
        ->where('orders.status', 'completed')
        ->whereBetween('orders.created_at', [$startDate, $endDate])
        ->selectRaw('DATE(orders.created_at) as date, SUM(order_detail.price * order_detail.quantity) as total')
        ->groupBy('date')
        ->orderBy('date')
        ->get();

    $salesTrendLabels = $sales->pluck('date')->toArray();
    $salesTrendData = $sales->pluck('total')->toArray();

    $topProductsQuery = DB::table('order_detail')
        ->join('products', 'order_detail.product_id', '=', 'products.id')
        ->join('orders', 'order_detail.order_id', '=', 'orders.id')
        ->where('orders.status', 'completed')
        ->whereBetween('orders.created_at', [$startDate, $endDate]);

    $topProducts = $topProductsQuery
        ->select('products.productName', DB::raw('SUM(order_detail.quantity) as total_sold'))
        ->groupBy('products.productName')
        ->orderByDesc('total_sold')
        ->limit(10)
        ->get();

    $topProductsLabels = $topProducts->pluck('productName')->toArray();
    $topProductsData = $topProducts->pluck('total_sold')->toArray();

    $salesBySchoolQuery = DB::table('schools')
    ->leftJoin('users', 'schools.id', '=', 'users.school_id')
    ->leftJoin('orders', function ($join) {
        $join->on('users.id', '=', 'orders.user_id')
            ->where('orders.status', 'completed');
    })
    ->leftJoin('order_detail', 'orders.id', '=', 'order_detail.order_id')
    ->where(function($query) use ($startDate, $endDate) {
    $query->whereBetween('orders.created_at', [$startDate, $endDate])
          ->orWhereNull('orders.id');
    })

    ->select(
        'schools.school_name as name',
        'schools.lat',
        'schools.lng',
        DB::raw('COUNT(DISTINCT orders.id) as total_orders'),
        DB::raw('COALESCE(SUM(order_detail.price * order_detail.quantity), 0) as total_revenue')
    )
    ->groupBy('schools.id', 'schools.school_name', 'schools.lat', 'schools.lng');


    $salesBySchool = $salesBySchoolQuery->get();

    $products = Product::with('category')->latest()->take(10)->get();

    return view('admin.dashboard', compact(
        'pendingOrders',
        'completedOrders',
        'totalRevenue',
        'salesTrendLabels',
        'salesTrendData',
        'topProductsLabels',
        'topProductsData',
        'salesBySchool',
        'products',
        'revenueChange',
        'pendingChange',
        'completedChange'
    ));
}


    public function orders()
    {
        return view('admin.orders');
    }

}