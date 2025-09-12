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
            ->when($request->filled('category_id'), fn($q) => $q->where('category_id', $request->category_id))
            ->when($request->filled('search'), fn($q) => $q->where('productName', 'like', '%' . $request->search . '%'))
            ->latest()
            ->paginate(12)
            ->appends($request->only(['category_id', 'search'])); // keep filters on pagination

        return view('home', compact('products', 'categories'));
    }
}
