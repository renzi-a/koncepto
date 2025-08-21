<?php

namespace App\Http\Controllers;

use App\Models\OrderDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Orders;
use App\Models\CustomOrder;
use App\Models\Product;
use App\Models\School;
use App\Models\CustomOrderItem;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        $year = $request->input('year', '2025');
        $quarter = $request->input('quarter');

        $startDate = $year ? Carbon::createFromDate($year)->startOfYear() : Carbon::parse('2000-01-01')->startOfDay();
        $endDate = $year ? Carbon::createFromDate($year)->endOfYear() : Carbon::now()->endOfDay();

        if ($quarter && $year) {
            $quarterStartMonth = ($quarter - 1) * 3 + 1;
            $quarterEndMonth = $quarter * 3;
            $startDate = Carbon::createFromDate($year, $quarterStartMonth, 1)->startOfDay();
            $endDate = Carbon::createFromDate($year, $quarterEndMonth)->endOfMonth()->endOfDay();
        }

        $pendingOrders = Orders::where('status', 'new')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        $completedOrders = Orders::where('status', 'delivered')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        $orderSales = OrderDetail::whereHas('order', function ($query) use ($startDate, $endDate) {
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

        $customSales = CustomOrderItem::whereHas('customOrder', function ($query) use ($startDate, $endDate) {
            $query->where('status', 'delivered')
                ->whereBetween('created_at', [$startDate, $endDate]);
        })->sum('total_price');

        $totalSales = $orderSales + $customSales;

        $salesTrendLabels = [];
        $salesTrendData = [];
        $ordersTrendData = [];

        if ($year) {
            $chartDataYear = $year;
            $salesTrendLabels = [
                'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
                'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
            ];
            $monthlySales = array_fill(0, 12, 0);
            $monthlyOrders = array_fill(0, 12, 0);

            $startMonth = 1;
            $endMonth = 12;

            if ($quarter) {
                $startMonth = ($quarter - 1) * 3 + 1;
                $endMonth = $quarter * 3;
                $salesTrendLabels = array_slice($salesTrendLabels, $startMonth - 1, 3);
                $monthlySales = array_fill(0, 3, 0);
                $monthlyOrders = array_fill(0, 3, 0);
            }

            $monthIndex = 0;
            for ($month = $startMonth; $month <= $endMonth; $month++) {
                $normalSalesMonth = Orders::where('status', 'delivered')
                    ->whereYear('created_at', $chartDataYear)
                    ->whereMonth('created_at', $month)
                    ->with('orderDetails')
                    ->get()
                    ->sum(function ($order) {
                        return optional($order->orderDetails)->sum(function ($detail) {
                            return ($detail->price ?? 0) * ($detail->quantity ?? 0);
                        }) ?? 0;
                    });

                $customSalesMonth = CustomOrderItem::whereHas('customOrder', function ($query) use ($chartDataYear, $month) {
                    $query->where('status', 'delivered')
                        ->whereYear('created_at', $chartDataYear)
                        ->whereMonth('created_at', $month);
                })->sum('total_price');

                $monthlySales[$monthIndex] = $normalSalesMonth + $customSalesMonth;

                $normalOrdersMonth = Orders::where('status', 'delivered')
                    ->whereYear('created_at', $chartDataYear)
                    ->whereMonth('created_at', $month)
                    ->count();
                $customOrdersMonth = CustomOrder::where('status', 'delivered')
                    ->whereYear('created_at', $chartDataYear)
                    ->whereMonth('created_at', $month)
                    ->count();
                $monthlyOrders[$monthIndex] = $normalOrdersMonth + $customOrdersMonth;

                $monthIndex++;
            }
            $salesTrendData = $monthlySales;
            $ordersTrendData = $monthlyOrders;

        } else {
             $availableYears = Orders::select(DB::raw('YEAR(created_at) as year'))
                                     ->distinct()
                                     ->orderBy('year', 'asc')
                                     ->pluck('year')
                                     ->toArray();
             
            $availableCustomYears = CustomOrder::select(DB::raw('YEAR(created_at) as year'))
                                     ->distinct()
                                     ->orderBy('year', 'asc')
                                     ->pluck('year')
                                     ->toArray();

            $availableYears = array_unique(array_merge($availableYears, $availableCustomYears));
            sort($availableYears);

            $salesTrendLabels = $availableYears;
            $salesTrendData = [];
            $ordersTrendData = [];

            foreach ($availableYears as $chartYear) {
                $yearlySalesNormal = Orders::where('status', 'delivered')
                    ->whereYear('created_at', $chartYear)
                    ->with('orderDetails')
                    ->get()
                    ->sum(function ($order) {
                        return optional($order->orderDetails)->sum(function ($detail) {
                            return ($detail->price ?? 0) * ($detail->quantity ?? 0);
                        }) ?? 0;
                    });

                $yearlySalesCustom = CustomOrder::where('status', 'delivered')
                    ->whereYear('created_at', $chartYear)
                    ->with('items')
                    ->get()
                    ->sum(function ($customOrder) {
                        return optional($customOrder->items)->sum('total_price') ?? 0;
                    });

                $salesTrendData[] = $yearlySalesNormal + $yearlySalesCustom;

                $yearlyOrdersNormal = Orders::where('status', 'delivered')
                    ->whereYear('created_at', $chartYear)
                    ->count();
                $yearlyOrdersCustom = CustomOrder::where('status', 'delivered')
                    ->whereYear('created_at', $chartYear)
                    ->count();

                $ordersTrendData[] = $yearlyOrdersNormal + $yearlyOrdersCustom;
            }
        }

        $monthlySalesChange = 0;
        if ($year && !$quarter) {
            $currentMonth = Carbon::now()->month;
            $currentMonthIndex = $currentMonth - 1;

            if (isset($salesTrendData[$currentMonthIndex]) && $currentMonthIndex > 0) {
                $currentMonthSales = $salesTrendData[$currentMonthIndex];
                $previousMonthSales = $salesTrendData[$currentMonthIndex - 1];
                $monthlySalesChange = $previousMonthSales > 0 ? (($currentMonthSales - $previousMonthSales) / $previousMonthSales) * 100 : ($currentMonthSales > 0 ? 100 : 0);
            } else if (isset($salesTrendData[$currentMonthIndex]) && $currentMonthIndex === 0 && $salesTrendData[$currentMonthIndex] > 0) {
                $monthlySalesChange = 100;
            }
        }

        $previousPeriodStartDate = null;
        $previousPeriodEndDate = null;

        if ($year) {
            $previousPeriodStartDate = (clone $startDate)->subYearNoOverflow();
            $previousPeriodEndDate = (clone $endDate)->subYearNoOverflow();
        } else {
            $previousPeriodStartDate = $startDate;
            $previousPeriodEndDate = $endDate;
        }

        $previousOrderSales = OrderDetail::whereHas('order', function ($query) use ($previousPeriodStartDate, $previousPeriodEndDate) {
            $query->where('status', 'delivered')
                ->whereBetween('created_at', [$previousPeriodStartDate, $previousPeriodEndDate]);
        })->sum(DB::raw('price * quantity'));

        $previousCustomSales = CustomOrderItem::whereHas('customOrder', function ($query) use ($previousPeriodStartDate, $previousPeriodEndDate) {
            $query->where('status', 'delivered')
                ->whereBetween('created_at', [$previousPeriodStartDate, $previousPeriodEndDate]);
        })->sum('total_price');

        $previousSales = $previousOrderSales + $previousCustomSales;

        $previousPendingOrders = Orders::where('status', 'new')
            ->whereBetween('created_at', [$previousPeriodStartDate, $previousPeriodEndDate])
            ->count();
        $previousCustomPending = CustomOrder::whereIn('status', [
            'to_be_quoted', 'quoted', 'approved', 'gathering'
        ])
        ->whereBetween('created_at', [$previousPeriodStartDate, $previousPeriodEndDate])
        ->count();
        $previousPendingTotal = $previousPendingOrders + $previousCustomPending;

        $previousCompletedOrders = Orders::where('status', 'delivered')
            ->whereBetween('created_at', [$previousPeriodStartDate, $previousPeriodEndDate])
            ->count();
        $previousCustomCompleted = CustomOrder::where('status', 'delivered')
            ->whereBetween('created_at', [$previousPeriodStartDate, $previousPeriodEndDate])
            ->count();
        $previousCompletedTotal = $previousCompletedOrders + $previousCustomCompleted;

        $salesChange = $previousSales > 0
            ? (($totalSales - $previousSales) / $previousSales) * 100
            : ($totalSales > 0 ? 100 : 0);

        $currentPendingTotal = ($pendingOrders ?? 0) + ($customPending ?? 0);
        $pendingChange = $previousPendingTotal > 0
            ? (($currentPendingTotal - $previousPendingTotal) / $previousPendingTotal) * 100
            : ($currentPendingTotal > 0 ? 100 : 0);

        $currentCompletedTotal = ($completedOrders ?? 0) + ($customCompleted ?? 0);
        $completedChange = $previousCompletedTotal > 0
            ? (($currentCompletedTotal - $previousCompletedTotal) / $previousCompletedTotal) * 100
            : ($currentCompletedTotal > 0 ? 100 : 0);


        $topProductsQuery = DB::table('order_details')
            ->join('orders', 'order_details.order_id', '=', 'orders.id')
            ->join('products', 'order_details.product_id', '=', 'products.id')
            ->where('orders.status', 'delivered')
            ->whereBetween('orders.created_at', [$startDate, $endDate]);

        $topProducts = $topProductsQuery
            ->select('products.productName as product_name', DB::raw('SUM(order_details.quantity) as total'))
            ->groupBy('products.productName')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $topProductsLabels = $topProducts->pluck('product_name')->toArray();
        $topProductsData = $topProducts->pluck('total')->toArray();

        $schoolSales = School::withCount([
            'orders as total_normal_orders' => function ($query) use ($startDate, $endDate) {
                $query->where('status', 'delivered')
                    ->whereBetween('orders.created_at', [$startDate, $endDate]);
            },
            'customOrder as total_custom_orders' => function ($query) use ($startDate, $endDate) {
                $query->where('status', 'delivered')
                    ->whereBetween('custom_orders.created_at', [$startDate, $endDate]);
            }
        ])->get()->map(function ($school) use ($startDate, $endDate) {
            $school->total_orders = $school->total_normal_orders + $school->total_custom_orders;

            $normalSales = OrderDetail::whereHas('order', function ($query) use ($school, $startDate, $endDate) {
                $query->where('status', 'delivered')
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->whereHas('user', function ($q) use ($school) {
                        $q->where('school_id', $school->id);
                    });
            })->sum(DB::raw('price * quantity'));

            $customSales = CustomOrderItem::whereHas('customOrder', function ($query) use ($school, $startDate, $endDate) {
                $query->where('status', 'delivered')
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->whereHas('user', function ($q) use ($school) {
                        $q->where('school_id', $school->id);
                    });
            })->sum('total_price');

            $school->total_sales = $normalSales + $customSales;
            return $school;
        });

        // The logic for low stock products has been updated.
        $lowStockProducts = Product::where('quantity', '<=', 9)
                                   ->where('quantity', '>', 0)
                                   ->get();

        $products = Product::with('category')
                    ->orderByRaw('CASE WHEN quantity <= 9 THEN 0 WHEN quantity <= 5 THEN 1 ELSE 2 END')
                    ->orderBy('quantity', 'asc')
                    ->paginate(10);
        
        if ($request->ajax()) {
            return view('admin.partials.product-table-content', ['products' => $products]);
        }

        return view('admin.dashboard', [
            'pendingOrders' => $pendingOrders,
            'completedOrders' => $completedOrders,
            'customPending' => $customPending,
            'customCompleted' => $customCompleted,
            'totalSales' => $totalSales,
            'previousSales' => $previousSales,
            'salesChange' => $salesChange,
            'pendingChange' => $pendingChange,
            'completedChange' => $completedChange,
            'salesTrendLabels' => $salesTrendLabels,
            'salesTrendData' => $salesTrendData,
            'monthlySalesChange' => $monthlySalesChange,
            'monthlyOrders' => $ordersTrendData,
            'topProducts' => $topProducts,
            'schoolSales' => $schoolSales,
            'products' => $products,
            'topProductsLabels' => $topProductsLabels,
            'topProductsData' => $topProductsData,
            'selectedYear' => $year,
            'selectedQuarter' => $quarter,
            'lowStockProducts' => $lowStockProducts,
        ]);
    }
}