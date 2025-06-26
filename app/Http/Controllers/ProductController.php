<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category');

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $products = $query->paginate(10);
        $categories = [
        ['id' => 1, 'categoryName' => 'Writing & Drawing'],
        ['id' => 2, 'categoryName' => 'Paper Products'],
        ['id' => 3, 'categoryName' => 'Tools & Accessories'],
        ['id' => 4, 'categoryName' => 'Filing & Organizing'],
        ['id' => 5, 'categoryName' => 'Cleaning Essentials'],
        ['id' => 6, 'categoryName' => 'Technology & Electronics'],
        ['id' => 7, 'categoryName' => 'Office Supplies'],
        ['id' => 8, 'categoryName' => 'Facility & Utility'],
        ['id' => 9, 'categoryName' => 'Home Economics'],
    ];
        return view('admin.product', compact('products', 'categories'));
    }

   public function create()
{
    
    $categories = [
        ['id' => 1, 'categoryName' => 'Writing & Drawing'],
        ['id' => 2, 'categoryName' => 'Paper Products'],
        ['id' => 3, 'categoryName' => 'Tools & Accessories'],
        ['id' => 4, 'categoryName' => 'Filing & Organizing'],
        ['id' => 5, 'categoryName' => 'Cleaning Essentials'],
        ['id' => 6, 'categoryName' => 'Technology & Electronics'],
        ['id' => 7, 'categoryName' => 'Office Supplies'],
        ['id' => 8, 'categoryName' => 'Facility & Utility'],
        ['id' => 9, 'categoryName' => 'Home Economics']
    ];
    return view('admin.product-create', compact('categories'));

}


public function store(Request $request)
{
    $validated = $request->validate([
        'productName' => 'required',
        'brandName' => 'required',
        'price' => 'required|numeric',
        'category_id' => 'required|integer|min:1|max:9',
        'unit' => 'required',
        'quantity' => 'required|integer',
        'description' => 'nullable|string',
        'photo' => 'nullable|image|max:2048',
    ]);

    if ($request->hasFile('photo')) {
        $validated['image'] = $request->file('photo')->store('products', 'public');
    }

    $validated['user_id'] = Auth::id();

    Product::create($validated);
    return redirect()->route('product.index')->with('success', 'Product added!');

}

   public function edit(Product $product)
{
    $categories = [
        ['id' => 1, 'categoryName' => 'Writing & Drawing'],
        ['id' => 2, 'categoryName' => 'Paper Products'],
        ['id' => 3, 'categoryName' => 'Tools & Accessories'],
        ['id' => 4, 'categoryName' => 'Filing & Organizing'],
        ['id' => 5, 'categoryName' => 'Cleaning Essentials'],
        ['id' => 6, 'categoryName' => 'Technology & Electronics'],
        ['id' => 7, 'categoryName' => 'Office Supplies'],
        ['id' => 8, 'categoryName' => 'Facility & Utility'],
        ['id' => 9, 'categoryName' => 'Home Economics']
    ];

    return view('admin.product-edit', compact('product', 'categories'));
}

public function update(Request $request, Product $product)
{
    $validated = $request->validate([
        'productName' => 'required',
        'brandName' => 'required',
        'price' => 'required|numeric',
        'category_id' => 'required|integer',
        'unit' => 'required',
        'quantity' => 'required|integer',
        'description' => 'nullable|string',
        'photo' => 'nullable|image|max:2048',
    ]);

    if ($request->hasFile('photo')) {
        $validated['image'] = $request->file('photo')->store('products', 'public');
    }

    $product->update($validated);

    return redirect()->route('product.index')->with('success', 'Product updated!');
}


    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('product.index')->with('success', 'Product deleted.');
    }
}
