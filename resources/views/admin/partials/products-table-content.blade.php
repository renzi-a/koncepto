<div class="overflow-x-auto bg-white rounded-lg shadow-md">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-100">
            <tr>
                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Product</th>
                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Brand</th>
                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Image</th>
                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Category</th>
                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Price</th>
                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Unit</th>
                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Quantity</th>
                <th class="px-6 py-3 text-center text-sm font-semibold text-gray-700">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 bg-white">
            @forelse ($products as $product)
                @php
                    $rowClass = '';
                    if ($product->quantity <= 5) {
                        $rowClass = 'bg-red-50 hover:bg-red-100';
                    } elseif ($product->quantity <= 10) {
                        $rowClass = 'bg-yellow-50 hover:bg-yellow-100';
                    }
                @endphp
                <tr class="{{ $rowClass }}">
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $product->productName }}</td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $product->brandName }}</td>
                    <td class="px-6 py-4">
                        @if ($product->image)
                            <img src="{{ asset('storage/' . $product->image) }}" alt="Product Image"
                                class="w-14 h-14 object-cover rounded shadow">
                        @else
                            <span class="text-gray-400 text-sm italic">No image</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $product->category->categoryName ?? 'N/A' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-600">₱{{ number_format($product->price, 2) }}</td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $product->unit }}</td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $product->quantity }}</td>
                    <td class="px-6 py-4 text-center text-sm font-medium">
                        <a href="{{ route('product.edit', $product) }}" class="text-blue-600 hover:text-blue-900 inline-block mr-2" title="Edit">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                            </svg>
                        </a>
                        <form action="{{ route('product.destroy', $product) }}" method="POST"
                            onsubmit="return confirm('Are you sure you want to delete this product?');" class="inline-block delete-form">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-900" title="Delete">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="px-6 py-8 text-center text-gray-400 text-lg">No products found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="p-4">
        {{ $products->appends(request()->query())->links() }}
    </div>
</div>