<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\Message;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

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

        return view('user.home2', compact('products', 'categories', 'messages', 'admin', 'isAdminActive'));
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
        return view('user.dashboard');
    }

    public function profile()
    {
        return view('user.profile');
    }

    public function orderRequest()
    {
        return view('user.order-request');
    }

    public function trackOrder()
    {
        return view('user.track-order');
    }

    public function orderHistory()
    {
        return view('user.order-history');
    }

    public function users()
{
    $users = User::where('role', '!=', 'admin')->get(); // Or filter as needed
    return view('user.users', compact('users'));
}

}
