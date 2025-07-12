<x-layout>
<div class="container mx-auto px-4 py-6">
    <h1 class="text-3xl font-bold mb-6">Add Product</h1>

    @if ($errors->any())
        <div class="bg-red-100 text-red-700 p-4 rounded mb-6">
            <ul class="list-disc pl-6">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('product.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="productName" class="block font-semibold">Product Name</label>
                <input type="text" name="productName" id="productName"
                       placeholder="e.g. A4 Bond Paper"
                       value="{{ old('productName') }}"
                       class="w-full border rounded px-3 py-2"
                       required>
                @error('productName')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="brandName" class="block font-semibold">Brand</label>
                <input type="text" name="brandName" id="brandName"
                       placeholder="e.g. PaperOne"
                       value="{{ old('brandName') }}"
                       class="w-full border rounded px-3 py-2"
                       required>
                @error('brandName')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label for="price" class="block font-semibold">Price</label>
                <input type="number" name="price" id="price"
                       placeholder="e.g. 250"
                       value="{{ old('price') }}"
                       class="w-full border rounded px-3 py-2"
                       required>
                @error('price')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="category_id" class="block font-semibold">Category</label>
                <select name="category_id" id="category_id" class="w-full border rounded px-3 py-2" required>
                    <option value="" disabled {{ old('category_id') ? '' : 'selected' }}>Select Category</option>
                    @foreach($categories as $category)
                        <option value="{{ $category['id'] }}" {{ old('category_id') == $category['id'] ? 'selected' : '' }}>
                            {{ $category['categoryName'] }}
                        </option>
                    @endforeach
                </select>
                @error('category_id')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="unit" class="block font-semibold">Unit</label>
                <input type="text" name="unit" id="unit"
                       placeholder="e.g. ream, piece, box"
                       value="{{ old('unit') }}"
                       class="w-full border rounded px-3 py-2"
                       required>
                @error('unit')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="quantity" class="block font-semibold">Quantity</label>
                <input type="number" name="quantity" id="quantity"
                       placeholder="e.g. 10"
                       value="{{ old('quantity') }}"
                       class="w-full border rounded px-3 py-2"
                       required>
                @error('quantity')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div>
            <label for="photo" class="block font-semibold">Product Photo</label>
            <input type="file" name="photo" id="photo" class="w-full border rounded px-3 py-2">
            @error('photo')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="description" class="block font-semibold">Description</label>
            <textarea name="description" id="description" rows="4"
                      placeholder="Optional description here..."
                      class="w-full border rounded px-3 py-2">{{ old('description') }}</textarea>
            @error('description')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>
        <div class="flex space-x-4">
            <a href="{{ route('product.index') }}" 
               class="bg-gray-300 text-gray-800 px-4 py-2 rounded hover:bg-gray-400 transition">
                Back
            </a>
            <button type="submit" 
                    class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition">
                Save Product
            </button>
        </div>
    </form>
</div>
</x-layout>