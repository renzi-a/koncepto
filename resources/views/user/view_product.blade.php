<x-nav-link />
<div class="bg-gray-100 py-10">
    <div class="max-w-6xl mx-auto bg-white p-6 rounded-xl shadow-md">
        <div class="flex flex-col lg:flex-row gap-10">
            <!-- Product Image -->
            <div class="w-full lg:w-1/2">
                <img src="{{ asset('storage/' . $product->image) }}"
                     alt="{{ $product->productName }}"
                     class="w-full aspect-square object-cover rounded-lg border">
            </div>

            <!-- Product Details -->
            <div class="w-full lg:w-1/2 flex flex-col justify-between">
                <div class="space-y-4">
                    <a href="{{ route('user.home') }}" class="text-[#56AB2F] hover:underline font-medium mb-2 block">← Back to Products</a>
                    <h1 class="text-2xl font-bold text-gray-800">{{ $product->productName }}</h1>
                    <p class="text-gray-600 text-sm">Brand: <span class="font-medium">{{ $product->brandName }}</span></p>
                    <p class="text-3xl font-bold text-[#56AB2F] mt-4">₱{{ number_format($product->price, 2) }}</p>

                    <div class="space-y-2">
                        <p class="text-gray-700">Category:
                            <span class="font-semibold">{{ $product->category->categoryName ?? 'N/A' }}</span>
                        </p>
                        <p class="text-gray-700">Stocks Available:
                            <span class="font-semibold text-gray-900">{{ $product->quantity }}</span>
                        </p>
                    </div>

                    <!-- Quantity Input -->
                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Quantity:</label>
                        <div class="flex items-center gap-2">
                            <button type="button" onclick="decreaseQty()" class="px-3 py-1 border rounded text-lg">−</button>
                            <input id="qtyInput"
                            name="quantity"
                            type="number"
                            value="1"
                            min="1"
                            max="{{ $product->quantity }}"
                            data-price="{{ $product->price }}"
                            data-max="{{ $product->quantity }}"
                            class="w-16 text-center border rounded py-1">
                            <button type="button" onclick="increaseQty({{ $product->quantity }})" class="px-3 py-1 border rounded text-lg">+</button>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <form method="POST" action="{{ route('cart.update') }}" class="mt-6 flex flex-col sm:flex-row gap-4">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <input type="hidden" name="quantity" id="formQtyInput" value="1">

                    <button type="button"
                        onclick="buyNow({{ $product->id }})"
                        class="w-full border border-[#56AB2F] text-[#56AB2F] font-semibold px-6 py-3 rounded-lg hover:bg-green-50 transition">
                        Buy Now
                    </button>

                    <button type="submit"
                        class="w-full bg-[#56AB2F] hover:bg-green-700 text-white font-semibold px-6 py-3 rounded-lg transition transform hover:scale-105 duration-150">
                        Add to Cart
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Related Products -->
    <div class="max-w-6xl mx-auto mt-10">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Related Products</h2>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @php
                $related = \App\Models\Product::where('category_id', $product->category_id)
                    ->where('id', '!=', $product->id)
                    ->take(4)
                    ->get();
            @endphp

            @forelse ($related as $item)
                <a href="{{ route('view_product', $item->id) }}" target="_blank"
                   class="block bg-white p-2 rounded-lg shadow hover:shadow-md transition h-full">
                    <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->productName }}"
                         class="aspect-square w-full rounded-md bg-gray-200 object-cover" />
                    <div class="mt-4 flex justify-between items-start">
                        <div>
                            <h3 class="text-sm font-medium text-gray-800">{{ $item->productName }}</h3>
                            <p class="text-sm text-gray-500">{{ $item->brandName }}</p>
                        </div>
                        <p class="text-sm font-bold text-gray-900 whitespace-nowrap">₱{{ number_format($item->price, 2) }}</p>
                    </div>
                </a>
            @empty
                <p class="text-gray-500 col-span-full">No related products found.</p>
            @endforelse
        </div>
    </div>
</div>

@if(session('added_to_cart'))
<div
    x-data="{ show: true }"
    x-init="setTimeout(() => show = false, 3000)"
    x-show="show"
    x-transition
    class="fixed bottom-6 right-6 bg-green-600 text-white px-4 py-3 rounded-lg shadow-lg z-50"
>
    ✅ {{ session('added_to_cart') }}
</div>
@endif

<x-footer />

<!-- JS: Quantity Control -->
<script>
    function increaseQty(max) {
        const qtyInput = document.getElementById('qtyInput');
        let current = parseInt(qtyInput.value) || 1;
        if (current < max) {
            qtyInput.value = current + 1;
        }
    }

    function decreaseQty() {
        const qtyInput = document.getElementById('qtyInput');
        let current = parseInt(qtyInput.value) || 1;
        if (current > 1) {
            qtyInput.value = current - 1;
        }
    }

    const qtyInput = document.getElementById('qtyInput');
    const formQtyInput = document.getElementById('formQtyInput');

    qtyInput.addEventListener('input', () => {
        formQtyInput.value = qtyInput.value;
    });

    function increaseQty(max) {
        let current = parseInt(qtyInput.value) || 1;
        if (current < max) {
            qtyInput.value = current + 1;
            formQtyInput.value = qtyInput.value;
        }
    }

    function decreaseQty() {
        let current = parseInt(qtyInput.value) || 1;
        if (current > 1) {
            qtyInput.value = current - 1;
            formQtyInput.value = qtyInput.value;
        }
    }

    function buyNow(productId) {
        // You can handle Buy Now with a different route if needed
        alert("Buy Now functionality not yet implemented.");
    }
</script>
@if(session('added_to_cart'))
<script>
    const badge = document.getElementById('cartBadge');
    if (badge) {
        badge.classList.add('animate-bounce');
        setTimeout(() => {
            badge.classList.remove('animate-bounce');
        }, 1000);
    }
</script>
@endif
