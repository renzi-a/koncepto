<table class="min-w-full text-sm text-left">
    <thead>
        <tr class="text-gray-700 border-b">
            <th class="py-2 px-4">Product</th>
            <th class="py-2 px-4">Category</th>
            <th class="py-2 px-4">Stock</th>
            <th class="py-2 px-4">Price</th>
        </tr>
    </thead>
    <tbody>
        @forelse($products as $product)
            <tr class="border-b hover:bg-gray-50 
                @if ($product->quantity <= 5) highlight-red
                @elseif ($product->quantity < 10) highlight-yellow
                @endif
                cursor-pointer"
                onclick="window.location='{{ route('product.edit', ['product' => $product->id]) }}'">
                <td class="py-2 px-4">{{ $product->productName }}</td>
                <td class="py-2 px-4">{{ $product->category->categoryName ?? 'N/A' }}</td>
                <td class="py-2 px-4">
                    {{ $product->quantity }}
                </td>
                <td class="py-2 px-4">₱{{ number_format($product->price, 2) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="4" class="text-center text-gray-500 py-4">No products available</td>
            </tr>
        @endforelse
    </tbody>
</table>
<div class="mt-4">
    {{ $products->links() }}
</div>