<x-nav-link />

<div class="bg-gray-100 py-10 min-h-screen">
    <div class="max-w-6xl mx-auto bg-white p-6 rounded-xl shadow-md">
        <!-- Cart Header -->
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Your Cart</h2>
            <a href="{{ route('user.home') }}" class="text-[#56AB2F] hover:underline font-medium">
                ← Back to Products
            </a>
        </div>

        @if($cartItems && $cartItems->count())
            <!-- Cart Items -->
            <div class="space-y-6">
                @foreach($cartItems as $item)
                    <div class="flex items-center gap-6 border-b pb-4">
                        <img src="{{ asset('storage/' . $item->product->image) }}" alt="{{ $item->product->productName }}" class="w-24 h-24 object-cover rounded">

                        <div class="flex-1">
                            <h3 class="font-semibold text-lg text-gray-800">{{ $item->product->productName }}</h3>
                            <p class="text-sm text-gray-500">{{ $item->product->brandName }}</p>
                            <p class="text-[#56AB2F] font-semibold mt-1">₱{{ number_format($item->product->price, 2) }}</p>
                        </div>

                        <div class="flex items-center gap-2">
                            <input type="hidden" id="product-{{ $item->id }}" value="{{ $item->product_id }}">
                            <button type="button" onclick="updateQty({{ $item->id }}, -1)" class="px-2 py-1 bg-gray-200 rounded">−</button>
                            <input
                                type="number"
                                id="qty-{{ $item->id }}"
                                value="{{ $item->quantity }}"
                                min="1"
                                max="{{ $item->product->quantity }}"
                                data-max="{{ $item->product->quantity }}"
                                data-price="{{ $item->product->price }}"
                                class="w-14 text-center border rounded" />
                            <button type="button" onclick="updateQty({{ $item->id }}, 1)" class="px-2 py-1 bg-gray-200 rounded">+</button>
                        </div>

                        <!-- Remove Button -->
                        <form action="{{ route('cart.remove', $item->id) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-500 hover:text-red-700 ml-4">Remove</button>
                        </form>
                    </div>
                @endforeach
            </div>

            <!-- Cart Summary -->
            <div class="mt-8 flex justify-between items-center">
                <p class="text-lg font-semibold text-gray-700">
                    Total:
                    <span id="cart-total" class="text-[#56AB2F] text-2xl">
                        ₱{{ number_format($cartItems->sum(fn($i) => $i->product->price * $i->quantity), 2) }}
                    </span>
                </p>

                <div class="flex gap-3">
                    <a href="{{ route('checkout') }}" class="bg-[#56AB2F] text-white font-semibold px-6 py-3 rounded-lg hover:bg-green-700 transition">
                        Checkout
                    </a>
                </div>
            </div>
        @else
            <p class="text-gray-500 text-center py-10">Your cart is empty.</p>
            <div class="text-center">
                <a href="{{ route('user.home') }}" class="inline-block mt-4 bg-[#56AB2F] text-white px-6 py-3 rounded-lg hover:bg-green-700 transition">
                    Continue Shopping
                </a>
            </div>
        @endif
    </div>
</div>

<x-footer />

<!-- Quantity Control + Total Update -->
<script>
function updateQty(id, change) {
    const qtyInput = document.getElementById(`qty-${id}`);
    const productInput = document.getElementById(`product-${id}`);
    const max = parseInt(qtyInput.dataset.max);
    const min = 1;
    let value = parseInt(qtyInput.value);

    if (!isNaN(value)) {
        value = Math.min(max, Math.max(min, value + change));
        qtyInput.value = value;

        fetch("{{ route('cart.update') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name=csrf-token]').getAttribute('content')
            },
            body: JSON.stringify({
                items: {
                    [id]: {
                        product_id: productInput.value,
                        quantity: value
                    }
                }
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                recalculateTotal();
            } else {
                alert(data.message || 'Update failed.');
            }
        })
        .catch(error => console.error('Error:', error));
    }
}

function recalculateTotal() {
    let total = 0;
    document.querySelectorAll('input[id^="qty-"]').forEach(input => {
        const qty = parseInt(input.value);
        const price = parseFloat(input.dataset.price);
        if (!isNaN(qty) && !isNaN(price)) {
            total += qty * price;
        }
    });
    document.getElementById('cart-total').textContent = `₱${total.toLocaleString(undefined, { minimumFractionDigits: 2 })}`;
}
</script>
