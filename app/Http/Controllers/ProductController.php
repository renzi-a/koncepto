<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category')
            ->orderByRaw('CASE WHEN quantity <= 10 THEN 0 ELSE 1 END')
            ->orderBy('quantity', 'asc')
            ->latest();

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('q')) {
            $query->where(function($q) use ($request) {
                $q->where('productName', 'like', '%' . $request->q . '%')
                  ->orWhere('brandName', 'like', '%' . $request->q . '%');
            });
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

        if ($request->ajax()) {
            return view('admin.partials.products-table-content', compact('products'))->render();
        }

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
            'productName' => 'required|string|max:255',
            'brandName' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|integer|exists:categories,id',
            'unit' => 'required|string|max:255',
            'quantity' => 'required|integer|min:0',
            'description' => 'nullable|string|max:1000',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $existingProduct = Product::where('productName', $validated['productName'])
                                ->where('brandName', $validated['brandName'])
                                ->first();

        if ($existingProduct) {
            return response()->json([
                'status' => 'duplicate',
                'message' => 'Product already exists. Do you want to add to its quantity?',
                'product' => [
                    'id' => $existingProduct->id,
                    'productName' => $existingProduct->productName,
                    'brandName' => $existingProduct->brandName,
                    'current_quantity' => $existingProduct->quantity,
                    'unit' => $existingProduct->unit,
                ]
            ], 409);
        }

        if ($request->hasFile('photo')) {
            $validated['image'] = $request->file('photo')->store('products', 'public');
        }

        $validated['user_id'] = Auth::id();

        Product::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Product added successfully!'
        ], 201);
    }

    public function addQuantity(Request $request, Product $product)
    {
        $request->validate([
            'quantity_to_add' => 'required|integer|min:1',
        ]);

        $product->quantity += $request->quantity_to_add;
        $product->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Quantity updated successfully!',
            'new_quantity' => $product->quantity
        ]);
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
            'productName' => 'required|string|max:255',
            'brandName' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|integer',
            'unit' => 'required|string|max:255',
            'quantity' => 'required|integer|min:0',
            'description' => 'nullable|string|max:1000',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $product->update($validated);

        return response()->json(['message' => 'Product updated successfully!']);
    }

public function destroy(Product $product)
{
    $product->delete();
    return response()->json(['success' => 'Product deleted.']);
}


    public function search(Request $request)
    {
        $query = $request->q;
        $categoryId = $request->category_id;

        $products = Product::with('category')
            ->orderByRaw('CASE WHEN quantity <= 10 THEN 0 ELSE 1 END')
            ->orderBy('quantity', 'asc')
            ->when($query, fn($q) => $q->where('productName', 'like', "%$query%")->orWhere('brandName', 'like', "%$query%"))
            ->when($categoryId, fn($q) => $q->where('category_id', $categoryId))
            ->latest()
            ->get();

        return response()->json($products->map(function ($p) {
            return [
                'id' => $p->id,
                'productName' => $p->productName,
                'brandName' => $p->brandName,
                'image' => $p->image,
                'categoryName' => $p->category->categoryName ?? null,
                'price' => $p->price,
                'unit' => $p->unit,
                'quantity' => $p->quantity,
                'created_at' => $p->created_at,
            ];
        }));
    }
}