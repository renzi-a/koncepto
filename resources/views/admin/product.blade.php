<x-layout/>
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Products</h1>

        <a href="{{ route('product.create') }}"
           class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition">
            Add Product
        </a>
    </div>

    <form method="GET" action="{{ route('product.index') }}" class="mb-4">
        <label for="category" class="block mb-2 font-semibold">Filter by Category</label>
        <select id="category" name="category_id" onchange="this.form.submit()" 
            class="border border-gray-300 rounded px-3 py-2 w-64">
            <option value="">All Categories</option>
            @foreach($categories as $category)
                <option value="{{ $category['id'] }}" {{ request('category_id') == $category['id'] ? 'selected' : '' }}>
                    {{ $category['categoryName'] }}
                </option>
            @endforeach
        </select>
    </form>

    <div class="overflow-x-auto">
        <table class="min-w-full bg-white border border-gray-200 rounded-md shadow-sm">
            <thead>
                <tr class="bg-gray-100 text-left">
                    <th class="px-4 py-2 border-b">Product Name</th>
                    <th class="px-4 py-2 border-b">Brand</th>
                    <th class="px-4 py-2 border-b">Image</th>
                    <th class="px-4 py-2 border-b">Category</th>
                    <th class="px-4 py-2 border-b">Price</th>
                    <th class="px-4 py-2 border-b">Unit</th>
                    <th class="px-4 py-2 border-b">Quantity</th>
                    <th class="px-4 py-2 border-b">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($products as $product)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-4 py-2">{{ $product->productName }}</td>
                        <td class="px-4 py-2">{{ $product->brandName }}</td>

                        <td class="px-4 py-2">
                            @if ($product->image)
                                <img src="{{ asset('storage/' . $product->image) }}" alt="Product Image" class="w-16 h-16 object-cover rounded">
                            @else
                                <span class="text-gray-400 italic">No image</span>
                            @endif
                        </td>

                        <td class="px-4 py-2">{{ $product->category->categoryName ?? 'N/A' }}</td>
                        <td class="px-4 py-2">₱{{ number_format($product->price, 2) }}</td>
                        <td class="px-4 py-2">{{ $product->unit }}</td>
                        <td class="px-4 py-2">{{ $product->quantity }}</td>
                        <td class="px-4 py-2">
                            <div class="flex items-center justify-center space-x-3">
                                <a href="{{ route('product.edit', $product) }}" class="hover:scale-105 transition-transform">
                                    <img src="{{ asset('images/icons/edit.png') }}" alt="Edit" class="w-5 h-5">
                                </a>
                                <form action="{{ route('product.destroy', $product) }}" method="POST"
                                    onsubmit="return confirm('Delete this product?');" class="inline-block">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="hover:scale-105 transition-transform">
                                        <img src="{{ asset('images/icons/delete.png') }}" alt="Delete" class="w-5 h-5">
                                    </button>
                                </form>
                            </div>
                        </td>

                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-6 text-center text-gray-500">No products found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
