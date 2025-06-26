<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::all();

        $products = Product::with('category')
            ->when($request->filled('category_id'), function ($query) use ($request) {
                $query->where('category_id', $request->category_id);
            })
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where('productName', 'like', '%' . $request->search . '%');
            })
            ->latest()
            ->paginate(12);

        return view('home', compact('products', 'categories'));
    }

    

}
