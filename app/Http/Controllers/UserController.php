<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;

class UserController extends Controller
{
    public function index()
    {
        $products = Product::latest()->paginate(10);
        $categories = Category::all();

        return view('user.home2', compact('products', 'categories'));
    }
}
