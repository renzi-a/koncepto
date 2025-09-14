<?php

namespace App\Http\Controllers;

use App\Models\CustomOrder;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\Message;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class UserController extends Controller
{
public function index(Request $request)
{
    $categories = Category::all();

    $products = Product::with('category')
        ->when($request->filled('category_id'), fn($q) => $q->where('category_id', $request->category_id))
        ->when($request->filled('search'), fn($q) => $q->where('productName', 'like', '%' . $request->search . '%'))
        ->latest()
        ->paginate(12);

    $user = Auth::user();
    $admin = User::where('role', 'admin')->first();

    $messages = collect();
    $isAdminActive = false;

    if ($user && $admin) {
        $messages = Message::where(function ($q) use ($user, $admin) {
                $q->where('sender_id', $user->id)
                  ->where('receiver_id', $admin->id);
            })
            ->orWhere(function ($q) use ($user, $admin) {
                $q->where('sender_id', $admin->id)
                  ->where('receiver_id', $user->id);
            })
            ->latest()
            ->limit(20)
            ->get()
            ->reverse();

        $isAdminActive = Cache::has("typing_admin_{$admin->id}");
    }

    return view('user.home', compact('products', 'categories', 'messages', 'admin', 'isAdminActive'));
}


    public function viewProduct($id)
    {
        $product = Product::findOrFail($id);
        $similarKeywords = [];
        if (preg_match('/^(.+?)\s+(GI-\d+)/i', $product->productName, $matches)) {
            $similarKeywords[] = trim($matches[1] . ' ' . $matches[2]);
        } else {
            $nameParts = explode(' ', $product->productName);
            if (count($nameParts) >= 2) {
                $similarKeywords[] = $nameParts[0] . ' ' . $nameParts[1];
            } else {
                $similarKeywords[] = $product->productName;
            }
        }

        if (!empty($product->brandName) && !in_array(strtolower($product->brandName), array_map('strtolower', $similarKeywords))) {
            $similarKeywords[] = $product->brandName;
        }

        $similarProducts = Product::query()
            ->where('id', '!=', $product->id)
            ->where(function ($query) use ($similarKeywords) {
                foreach ($similarKeywords as $keyword) {
                    $query->orWhere('productName', 'like', '%' . $keyword . '%')
                          ->orWhere('brandName', 'like', '%' . $keyword . '%');
                }
            })
            ->inRandomOrder()
            ->limit(4)
            ->get();


        $relatedKeywords = collect(explode(' ', strtolower($product->productName . ' ' . $product->brandName)))
            ->filter(function($word) {
                return strlen($word) > 2 && !in_array($word, [
                    'the', 'and', 'for', 'with', 'from', 'a', 'an', 'of', 'to', 'in', 'on', 'is',
                    'ink', 'paper', 'set', 'pack', 'color', 'black', 'white', 'blue', 'red', 'green',
                    'original', 'genuine', 'compatible', 'new', 'old', 'good', 'best', 'high', 'low',
                    'plus', 'pro', 'max', 'mini', 'standard', 'regular', 'premium', 'ultra',
                    'multi', 'single', 'bundle', 'kit', 'case', 'box', 'piece', 'ream', 'sheet',
                    'item', 'product', 'material', 'office', 'school', 'home', 'store', 'shop',
                ]);
            })
            ->unique()
            ->values();

        $relatedProducts = Product::query()
            ->where('id', '!=', $product->id)
            ->where(function ($query) use ($product, $relatedKeywords, $similarProducts) {
                if ($product->category_id) {
                    $query->orWhere('category_id', $product->category_id);
                }

                if ($relatedKeywords->isNotEmpty()) {
                    foreach ($relatedKeywords as $keyword) {
                        $query->orWhere('productName', 'like', '%' . $keyword . '%')
                              ->orWhere('brandName', 'like', '%' . $keyword . '%');
                    }
                }
            })
            ->whereNotIn('id', $similarProducts->pluck('id')->toArray())
            ->inRandomOrder()
            ->limit(4)
            ->get();


        return view('user.view_product', compact('product', 'similarProducts', 'relatedProducts'));
    }


    public function showNotifications()
    {
        $notifications = Notification::where('user_id', auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('user.notification', compact('notifications'));
    }

    public function clearNotifications()
    {
        Notification::where('user_id', auth::id())->delete();
        return redirect()->route('notifications')->with('status', 'All notifications cleared.');
    }

    public function markAsRead()
    {
        if (!auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        Notification::where('user_id', auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['status' => 'marked']);
    }
    
public function dashboard(Request $request)
{
    $school = Auth::user()->school;
    $year = $request->input('year', Carbon::now()->year);

    // Filter orders by the selected year, specifying the table for `created_at`
    $regularOrders = $school ? $school->orders()->whereYear('created_at', $year)->get() : collect();
    $customOrders = $school ? $school->customOrder()->whereYear('custom_orders.created_at', $year)->get() : collect();
        
    $allOrders = $regularOrders->merge($customOrders);
    $totalOrders = $allOrders->count();

    $deliveredRegularOrders = $regularOrders->where('status', 'delivered')->count();
    $deliveredCustomOrders = $customOrders->where('status', 'delivered')->count();
    $deliveredOrders = $deliveredRegularOrders + $deliveredCustomOrders;

    $regularOrderCount = $regularOrders->count();
    $customOrderCount = $customOrders->count();

    $regularItemsCount = $regularOrders->sum('quantity');
    $customItemsCount = $customOrders->flatMap(fn ($order) => $order->items)->count();
    $totalItemsCount = $regularItemsCount + $customItemsCount;

    // Corrected logic for pending orders
    $pendingRegularOrders = $regularOrders->whereIn('status', ['new', 'processing', 'To be delivered'])->count();
    $pendingCustomOrders = $customOrders->whereIn('status', ['to be quoted', 'quoted', 'approved', 'processing', 'delivering', 'To be delivered'])->count();
    $pendingOrdersCount = $pendingRegularOrders + $pendingCustomOrders;

    $recentOrders = $regularOrders->sortByDesc('created_at')->take(5)->map(function ($order) {
        $order->type = 'Regular';
        return $order;
    });

    $recentCustomOrders = $customOrders->sortByDesc('created_at')->take(5)->map(function ($order) {
        $order->type = 'Custom';
        return $order;
    });
        
    $combinedRecentOrders = $recentOrders->merge($recentCustomOrders)->sortByDesc('created_at')->take(5);
    
    $salesLabels = [];
    $salesData = [];

    if ($allOrders->isNotEmpty()) {
        $firstOrderDate = $allOrders->min('created_at');
        $lastOrderDate = $allOrders->max('created_at');

        $startMonth = Carbon::parse($firstOrderDate)->startOfMonth();
        $endMonth = Carbon::parse($lastOrderDate)->startOfMonth();

        for ($date = $startMonth; $date->lte($endMonth); $date->addMonth()) {
            $salesLabels[] = $date->format('M');
            
            $monthlyRegularCount = $regularOrders->filter(fn ($order) => Carbon::parse($order->created_at)->isSameMonth($date))->count();
            $monthlyCustomCount = $customOrders->filter(fn ($order) => Carbon::parse($order->created_at)->isSameMonth($date))->count();
            
            $salesData[] = $monthlyRegularCount + $monthlyCustomCount;
        }
    }

    return view('user.dashboard', compact(
        'totalOrders',
        'deliveredOrders',
        'regularOrderCount',
        'customOrderCount',
        'totalItemsCount',
        'pendingOrdersCount',
        'combinedRecentOrders',
        'salesLabels',
        'salesData',
        'year'
    ));
}
    public function profile()
    {
        return view('user.profile');
    }



    public function updateProfile(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'phone' => 'nullable|string|max:20',
            'current_password' => 'nullable|required_with:new_password',
            'new_password' => 'nullable|string|min:8|confirmed',
        ]);

        $user->first_name = $validated['first_name'];
        $user->last_name = $validated['last_name'];
        $user->email = $validated['email'];
        $user->cp_no = $request->input('phone', '');

        if (!empty($validated['new_password'])) {
            if (!Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'Current password is incorrect.']);
            }

            $user->password = Hash::make($validated['new_password']);
        }

        $user->save();

        return back()->with('success', 'Profile updated successfully.');
    }

public function checkNewNotifications()
{
    if (!Auth::check()) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    $unreadCount = Notification::where('user_id', Auth::id())
                               ->where('is_read', false)
                               ->count();

    return response()->json(['hasNew' => $unreadCount > 0, 'count' => $unreadCount]);
}

    public function showCustomOrder(CustomOrder $order)
    {
        $this->authorize('view', $order); 
        $order->load('items');
        return view('user.custom-order-detail', compact('order'));
    }

}