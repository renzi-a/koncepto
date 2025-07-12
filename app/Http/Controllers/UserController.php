<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\Message;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Auth;




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
    if ($user && $admin) {
        $messages = Message::where(function ($q) use ($user, $admin) {
            $q->where('sender_id', $user->id)->where('receiver_id', $admin->id);
        })->orWhere(function ($q) use ($user, $admin) {
            $q->where('sender_id', $admin->id)->where('receiver_id', $user->id);
        })->latest()->limit(20)->get()->reverse();
    }

    return view('user.home2', compact('products', 'categories', 'messages', 'admin'));
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

}
