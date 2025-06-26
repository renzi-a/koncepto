<x-layout />
<div class="container mx-auto px-4 py-6">
    <h1 class="text-3xl font-bold mb-6">Edit Product</h1>

        @if ($errors->any())
        <div class="bg-red-100 text-red-700 p-4 rounded">
            <ul class="list-disc pl-6">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('product.update', $product->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="productName" class="block font-semibold">Product Name</label>
                <input type="text" name="productName" value="{{ $product->productName }}" class="w-full border rounded px-3 py-2" required>
            </div>
            <div>
                <label for="brandName" class="block font-semibold">Brand</label>
                <input type="text" name="brandName" value="{{ $product->brandName }}" class="w-full border rounded px-3 py-2" required>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label for="price" class="block font-semibold">Price</label>
                <input type="number" name="price" value="{{ $product->price }}" class="w-full border rounded px-3 py-2" required>
            </div>
            <div>
                <label for="category_id" class="block font-semibold">Category</label>
                <select name="category_id" class="w-full border rounded px-3 py-2" required>
                    @foreach($categories as $category)
                        <option value="{{ $category['id'] }}" {{ $product->category_id == $category['id'] ? 'selected' : '' }}>
                            {{ $category['categoryName'] }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="unit" class="block font-semibold">Unit</label>
                <input type="text" name="unit" value="{{ $product->unit }}" class="w-full border rounded px-3 py-2" required>
            </div>
            <div>
                <label for="quantity" class="block font-semibold">Quantity</label>
                <input type="number" name="quantity" value="{{ $product->quantity }}" class="w-full border rounded px-3 py-2" required>
            </div>
        </div>

        <div>
            <label for="photo" class="block font-semibold">Product Image</label>
            @if ($product->image)
                <img src="{{ asset('storage/' . $product->image) }}" class="w-20 h-20 object-cover rounded mb-2">
            @endif
            <input type="file" name="photo" class="w-full border rounded px-3 py-2">
        </div>


        <div>
            <label for="description" class="block font-semibold">Description</label>
            <textarea name="description" rows="4" class="w-full border rounded px-3 py-2">{{ $product->description }}</textarea>
        </div>

        <div class="flex space-x-4">
            <a href="{{ route('product.index') }}" class="bg-gray-300 text-gray-800 px-4 py-2 rounded hover:bg-gray-400 transition">Back</a>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">Update Product</button>
        </div>
    </form>
</div>
