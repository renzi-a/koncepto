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
                $q->where('sender_id', $user->id)->where('receiver_id', $admin->id);
            })->orWhere(function ($q) use ($user, $admin) {
                $q->where('sender_id', $admin->id)->where('receiver_id', $user->id);
            })->latest()->limit(20)->get()->reverse();

            $isAdminActive = Cache::has("typing_admin_{$admin->id}");
        }

        return view('user.home', compact('products', 'categories', 'messages', 'admin', 'isAdminActive'));
    }

    public function viewProduct($id)
    {
        $product = Product::findOrFail($id);
        return view('user.view_product', compact('product'));
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
public function dashboard()
    {
    $school = Auth::user()->school;

    $regularOrders = $school ? $school->orders : collect();
    $customOrders = $school ? $school->customOrder : collect();
        
        $allOrders = $regularOrders->merge($customOrders);
        $totalOrders = $allOrders->count();

        $deliveredRegularOrders = $regularOrders->where('status', 'delivered')->count();
        $deliveredCustomOrders = $customOrders->where('status', 'delivered')->count();
        $deliveredOrders = $deliveredRegularOrders + $deliveredCustomOrders;

        $regularOrderCount = $regularOrders->count();
        $customOrderCount = $customOrders->count();

        // Add new counts for the dashboard
        $studentCount = $school ? $school->students()->count() : 0;
        $teacherCount = $school ? $school->teachers()->count() : 0;
        $customOrderItemsCount = $customOrders->flatMap(fn ($order) => $order->items)->count();

        // Fetch recent orders by merging and sorting the collections
        $recentOrders = $regularOrders->take(5)->map(function ($order) {
            $order->type = 'Regular';
            return $order;
        });

        $recentCustomOrders = $customOrders->take(5)->map(function ($order) {
            $order->type = 'Custom';
            return $order;
        });
        
        $combinedRecentOrders = $recentOrders->merge($recentCustomOrders)->sortByDesc('created_at')->take(5);

        // Prepare monthly sales data by combining counts from both order types
        $salesLabels = ['Jan', 'Feb', 'Mar', 'Apr'];
        $salesData = [];
        foreach ($salesLabels as $index => $month) {
            $monthNumber = $index + 1;
            $regularCount = $regularOrders->where('created_at', '>=', now()->subMonths(4))->where('created_at', 'like', "%-{$monthNumber}-%")->count();
            $customCount = $customOrders->where('created_at', '>=', now()->subMonths(4))->where('created_at', 'like', "%-{$monthNumber}-%")->count();
            $salesData[] = $regularCount + $customCount;
        }

        return view('user.dashboard', compact(
            'totalOrders',
            'deliveredOrders',
            'regularOrderCount',
            'customOrderCount',
            'studentCount',
            'teacherCount',
            'customOrderItemsCount',
            'combinedRecentOrders',
            'salesLabels',
            'salesData'
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

public function users(Request $request)
{
    $school = Auth::user()->school;

    if (!$school) {
        abort(403, 'No school associated with this user.');
    }

    $query = User::where('school_id', $school->id)
        ->where('role', '!=', 'admin');

    if ($request->filled('search')) {
        $search = $request->input('search');
        $query->where(function ($q) use ($search) {
            $q->where('first_name', 'like', "%$search%")
              ->orWhere('last_name', 'like', "%$search%")
              ->orWhere('email', 'like', "%$search%");
        });
    }

    if ($request->filled('role')) {
        $query->where('role', $request->role);
    }

    $users = $query->latest()->paginate(10);

    if ($request->ajax()) {
        $html = view('components.user_table', compact('users'))->render();
        return response()->json(['html' => $html]);
    }

    return view('user.users', compact('users', 'school'));
}

public function showCustomOrder(CustomOrder $order)
{
    $this->authorize('view', $order); 
    $order->load('items');
    return view('user.custom-order-detail', compact('order'));
}

}
