<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Orders;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Products;
use App\Models\Category;

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
                $pendingOrdersQuery->whereYear('created_at', $year);
                $completedOrdersQuery->whereYear('created_at', $year);
                $orderDetailQuery->whereYear('orders.created_at', $year);
            }
        }

        $pendingOrders = $pendingOrdersQuery->count();
        $completedOrders = $completedOrdersQuery->count();

        $totalRevenue = $orderDetailQuery
            ->sum(DB::raw('order_detail.price * order_detail.quantity'));

        $sales = DB::table('order_detail')
            ->join('orders', 'order_detail.order_id', '=', 'orders.id')
            ->where('orders.status', 'completed');

        if ($request->filled('year')) {
            if ($request->filled('quarter')) {
                $sales->whereBetween('orders.created_at', [$startDate, $endDate]);
            } else {
                $sales->whereYear('orders.created_at', $year);
            }
        }

        $sales = $sales
            ->selectRaw('DATE(orders.created_at) as date, SUM(order_detail.price * order_detail.quantity) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $salesTrendLabels = $sales->pluck('date')->toArray();
        $salesTrendData = $sales->pluck('total')->toArray();

        $topProductsQuery = DB::table('order_detail')
            ->join('products', 'order_detail.product_id', '=', 'products.id')
            ->join('orders', 'order_detail.order_id', '=', 'orders.id')
            ->where('orders.status', 'completed');

        if ($request->filled('year')) {
            if ($request->filled('quarter')) {
                $topProductsQuery->whereBetween('orders.created_at', [$startDate, $endDate]);
            } else {
                $topProductsQuery->whereYear('orders.created_at', $year);
            }
        }

        $topProducts = $topProductsQuery
            ->select('products.productName', DB::raw('SUM(order_detail.quantity) as total_sold'))
            ->groupBy('products.productName')
            ->orderByDesc('total_sold')
            ->limit(10)
            ->get();

        $topProductsLabels = $topProducts->pluck('productName')->toArray();
        $topProductsData = $topProducts->pluck('total_sold')->toArray();

        return view('admin.dashboard', compact(
            'pendingOrders',
            'completedOrders',
            'totalRevenue',
            'salesTrendLabels',
            'salesTrendData',
            'topProductsLabels',
            'topProductsData'
        ));
    }

    public function orders()
    {
        return view('admin.orders');
    }

    public function chat($userId = null)
    {
        $users = User::where('role', 'school_admin')->with('school')->get();

        $activeUser = null;
        $messages = collect();

        if ($userId) {
            $activeUser = $users->firstWhere('id', $userId);
            $messages = $activeUser
                ? $activeUser->messages()->with('sender')->latest()->get()->reverse()
                : collect();
        }

        return view('admin.admin_chat', compact('users', 'activeUser', 'messages'));
    }
}
