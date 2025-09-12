<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
    @forelse ($products as $product)
        <a href="{{ route('view_product', $product->id) }}"
           class="group relative block bg-white border border-gray-200 p-4 rounded-xl shadow-lg hover:shadow-xl transition-transform duration-220 ease-in-out transform hover:scale-103">
            <div class="aspect-square w-full rounded-lg bg-gray-100 overflow-hidden">
                <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->productName }}"
                     class="h-full w-full object-contain group-hover:opacity-85 transition-opacity duration-200" />
            </div>
            <div class="mt-4 text-center">
                <h3 class="text-lg font-semibold text-gray-800 truncate">{{ $product->productName }}</h3>
                <p class="mt-1 text-sm text-gray-600">{{ $product->brandName }}</p>
                <p class="mt-2 text-xl font-extrabold text-green-700">₱{{ number_format($product->price, 2) }}</p>
            </div>
        </a>
    @empty
        <div class="col-span-full bg-gray-100 p-8 rounded-lg shadow-inner text-center">
            <p class="text-gray-600 text-lg font-medium mb-4">No products found.</p>
        </div>
    @endforelse
</div>

<div class="mt-12">
    {{ $products->withQueryString()->links() }}
</div>
 