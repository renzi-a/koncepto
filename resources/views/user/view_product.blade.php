<x-nav-link />

<div class="bg-gray-50 py-10 px-4 sm:px-6 lg:px-8">
    <div class="max-w-6xl mx-auto bg-white p-8 rounded-lg shadow-md">
        <div class="flex flex-col lg:flex-row gap-8">
            {{-- Product Image Section --}}
            <div class="w-full lg:w-1/2 flex items-center justify-center p-4 bg-gray-100 rounded-md">
                <img src="{{ asset('storage/' . $product->image) }}"
                     alt="{{ $product->productName }}"
                     class="w-full h-80 object-contain rounded-md">
            </div>

            {{-- Product Details Section --}}
            <div class="w-full lg:w-1/2 flex flex-col">
                <div class="space-y-3">
                    <a href="{{ route('user.home') }}" class="inline-flex items-center text-sm font-medium text-[#56AB2F] hover:text-green-700 transition duration-200">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                        Back to Products
                    </a>
                    <h1 class="text-3xl font-extrabold text-gray-900 leading-tight">{{ $product->productName }}</h1>
                    <p class="text-gray-600">Brand: <span class="font-semibold text-gray-800">{{ $product->brandName }}</span></p>
                    <p class="text-4xl font-bold text-[#56AB2F] mt-2">₱{{ number_format($product->price, 2) }}</p>

                    <p class="text-gray-700 leading-relaxed text-base pt-2">
                        {{ $product->description }}
                    </p>

                    <div class="pt-4 space-y-2">
                        <p class="text-gray-700 flex items-center">
                            <svg class="w-4 h-4 mr-2 text-gray-500" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM11 13a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                            Category:
                            <span class="font-semibold text-gray-900 ml-1">{{ $product->category->categoryName ?? 'N/A' }}</span>
                        </p>
                        <p class="text-gray-700 flex items-center">
                            <svg class="w-4 h-4 mr-2 text-gray-500" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M5.5 3A2.5 2.5 0 003 5.5v2.293l3.546 3.547A1 1 0 007 11.414V15a1 1 0 001 1h4a1 1 0 001-1v-3.586a1 1 0 00-.293-.707L12 7.793V5.5A2.5 2.5 0 009.5 3h-4zM2 15h16v1a1 1 0 01-1 1H3a1 1 0 01-1-1v-1z" clip-rule="evenodd"></path></svg>
                            Stocks Available:
                            <span class="font-semibold text-gray-900 ml-1">{{ $product->quantity }} {{ $product->unit }}</span>
                        </p>
                    </div>

                    <div class="mt-6">
                        <label for="qtyInput" class="block text-base font-medium text-gray-800 mb-2">Quantity:</label>
                        <div class="flex items-center gap-2">
                            <button type="button" onclick="decreaseQty()"
                                class="w-8 h-8 flex items-center justify-center border border-gray-300 rounded-md text-lg text-gray-700 hover:bg-gray-100 transition duration-150">
                                −
                            </button>
                            <input id="qtyInput"
                                       name="quantity"
                                       type="number"
                                       value="1"
                                       min="1"
                                       max="{{ $product->quantity }}"
                                       data-price="{{ $product->price }}"
                                       data-max="{{ $product->quantity }}"
                                       class="w-16 text-center border border-gray-300 rounded-md py-1 text-base font-medium focus:ring-[#56AB2F] focus:border-[#56AB2F]">
                            <button type="button" onclick="increaseQty()"
                                class="w-8 h-8 flex items-center justify-center border border-gray-300 rounded-md text-lg text-gray-700 hover:bg-gray-100 transition duration-150">
                                +
                            </button>
                        </div>
                        @if($product->quantity === 0)
                            <p class="text-red-600 text-sm mt-2 font-semibold">Out of Stock!</p>
                        @endif
                    </div>
                </div>

                {{-- Action Buttons --}}
                <form id="addToCartForm" class="mt-8 flex flex-col sm:flex-row gap-3">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <input type="hidden" name="quantity" id="formQtyInput" value="1">

                    <button type="button"
                        onclick="buyNow({{ $product->id }})"
                        @if($product->quantity === 0) disabled @endif
                        class="w-full flex-1 border border-[#56AB2F] text-[#56AB2F] font-semibold px-6 py-3 rounded-lg hover:bg-green-50 transition duration-200
                        @if($product->quantity === 0) opacity-50 cursor-not-allowed @endif">
                        Buy Now
                    </button>

                    <button type="submit"
                        @if($product->quantity === 0) disabled @endif
                        class="w-full flex-1 bg-[#56AB2F] hover:bg-green-700 text-white font-bold px-6 py-3 rounded-lg shadow-sm transition transform hover:scale-103 duration-150
                        @if($product->quantity === 0) opacity-50 cursor-not-allowed @endif">
                        Add to Cart
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Similar Products Section - Made Even Smaller --}}
    @if($similarProducts->isNotEmpty())
        <div class="max-w-6xl mx-auto mt-12 p-6 bg-white rounded-lg shadow-md">
            <h2 class="text-xl font-bold text-gray-800 mb-5 border-b pb-3">Similar Products</h2>
            <div class="grid grid-cols-3 sm:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3">
                @foreach ($similarProducts as $item)
                    <a href="{{ route('view_product', $item->id) }}"
                       class="group relative block bg-white border border-gray-100 p-2 rounded-md shadow-sm hover:shadow-md transition-transform duration-200 ease-in-out transform hover:scale-105">
                        <div class="aspect-square w-full rounded-sm bg-gray-100 overflow-hidden">
                            <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->productName }}"
                                 class="h-full w-full object-contain group-hover:opacity-85 transition-opacity duration-200" />
                        </div>
                        <div class="mt-1.5 text-center">
                            <h3 class="text-xs font-semibold text-gray-800 truncate">{{ $item->productName }}</h3>
                            <p class="mt-0.5 text-[0.65rem] text-gray-600">{{ $item->brandName }}</p>
                            <p class="mt-1 text-sm font-bold text-green-700">₱{{ number_format($item->price, 2) }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Related Products Section - Carousel with Arrows --}}
    @if($relatedProducts->isNotEmpty())
        <div class="max-w-6xl mx-auto mt-12 p-6 bg-white rounded-lg shadow-md">
            <h2 class="text-xl font-bold text-gray-800 mb-5 border-b pb-3">Related Products</h2>

            <div x-data="{
                products: {{ json_encode($relatedProducts->toArray()) }},
                currentOffset: 0,
                get responsiveItemsPerPage() {
                    if (window.innerWidth >= 1280) return 4;
                    if (window.innerWidth >= 1024) return 3;
                    if (window.innerWidth >= 768) return 2;
                    return 1;
                },
                get maxOffset() {
                    return Math.max(0, this.products.length - this.responsiveItemsPerPage);
                },
                next() {
                    this.currentOffset = Math.min(this.currentOffset + 1, this.maxOffset);
                },
                prev() {
                    this.currentOffset = Math.max(0, this.currentOffset - 1);
                },
                init() {
                    const updateOffset = () => {
                        const newItemsPerPage = this.responsiveItemsPerPage;
                        this.currentOffset = Math.min(this.currentOffset, this.maxOffset);
                    };
                    updateOffset();
                    window.addEventListener('resize', updateOffset);
                }
            }" x-init="init()" class="relative">
                <div class="relative overflow-hidden">
                    <div class="flex transition-transform duration-300 ease-out"
                         :style="`transform: translateX(-${(currentOffset * 100) / responsiveItemsPerPage}%)`">
                        <template x-for="item in products" :key="item.id">
                            <a :href="'{{ url('user/view_product') }}/' + item.id"
                               :class="{
                                    'w-full': responsiveItemsPerPage === 1,
                                    'sm:w-1/2': responsiveItemsPerPage === 2,
                                    'md:w-1/2': responsiveItemsPerPage === 2,
                                    'lg:w-1/3': responsiveItemsPerPage === 3,
                                    'xl:w-1/4': responsiveItemsPerPage === 4,
                                    'flex-shrink-0': true,
                                    'px-2': true
                                }"
                               class="group relative block bg-white border border-gray-200 p-3 rounded-lg shadow-sm hover:shadow-md transition-transform duration-220 ease-in-out transform hover:scale-103 min-w-0">
                                <div class="aspect-square w-full rounded-md bg-gray-100 overflow-hidden">
                                    <img :src="'{{ asset('storage') }}/' + item.image" :alt="item.productName"
                                         class="h-full w-full object-contain group-hover:opacity-85 transition-opacity duration-200" />
                                </div>
                                <div class="mt-2 text-center">
                                    <h3 class="text-sm font-semibold text-gray-800 truncate" x-text="item.productName"></h3>
                                    <p class="mt-0.5 text-xs text-gray-600" x-text="item.brandName"></p>
                                    <p class="mt-1 text-base font-bold text-green-700">₱<span x-text="parseFloat(item.price).toFixed(2)"></span></p>
                                </div>
                            </a>
                        </template>
                    </div>
                </div>

                {{-- Navigation Arrows (Left) --}}
                <button @click="prev()" :disabled="currentOffset === 0"
                    class="absolute -left-5 top-1/2 -translate-y-1/2 bg-white p-2 rounded-full shadow-lg transition-all duration-200 z-20
                            focus:outline-none focus:ring-2 focus:ring-[#56AB2F] disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                </button>

                {{-- Navigation Arrows (Right) --}}
                <button @click="next()" :disabled="currentOffset >= maxOffset"
                    class="absolute -right-5 top-1/2 -translate-y-1/2 bg-white p-2 rounded-full shadow-lg transition-all duration-200 z-20
                            focus:outline-none focus:ring-2 focus:ring-[#56AB2F] disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </button>
            </div>
        </div>
    @endif

    {{-- Fallback message if no similar or related products --}}
    @if($similarProducts->isEmpty() && $relatedProducts->isEmpty())
        <div class="max-w-6xl mx-auto mt-12 p-6 bg-gray-100 rounded-lg shadow-inner text-center">
            <p class="text-gray-600 font-medium">No similar or related products found at this time.</p>
        </div>
    @endif

</div>

{{-- Toast Notification --}}
<div
    x-data="{ show: false, message: 'Added to cart!' }"
    x-init="
        window.addEventListener('show-cart-toast', (event) => {
            show = true;
            message = event.detail.message || 'Added to cart!';
            setTimeout(() => show = false, 2500);
        });
    "
    x-show="show"
    x-transition:enter="ease-out duration-300"
    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
    x-transition:leave="ease-in duration-200"
    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
    class="fixed inset-x-0 bottom-6 z-50 flex items-center justify-center pointer-events-none"
    style="display: none;"
>
    <div class="bg-gray-800 text-white px-6 py-3 rounded-lg shadow-xl text-base font-semibold flex items-center space-x-2">
        <svg class="w-5 h-5 text-green-400" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
        <span x-text="message"></span>
    </div>
</div>

<x-footer />

{{-- JavaScript --}}
<script>
document.getElementById('addToCartForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const form = e.target;
    const data = new FormData(form);

    fetch("{{ route('cart.update') }}", {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: data
    })
    .then(async res => {
        if (!res.ok) {
            const errorData = await res.json();
            console.error('Add to Cart Error:', errorData);
            window.dispatchEvent(new CustomEvent('show-cart-toast', { detail: { message: errorData.message || 'Failed to add to cart!' } }));
            throw new Error(errorData.message || 'Failed to add to cart.');
        }
        return res.json();
    })
    .then(data => {
        window.dispatchEvent(new CustomEvent('show-cart-toast', { detail: { message: data.message || 'Added to cart!' } }));

        let badge = document.getElementById('cartBadge');
        const cartLink = document.querySelector('a[href="{{ route('cart.index') }}"]');

        if (!badge && cartLink) {
            badge = document.createElement('span');
            badge.id = 'cartBadge';
            badge.className = 'absolute -top-2 -right-2 bg-red-500 text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center';
            cartLink.classList.add('relative');
            cartLink.appendChild(badge);
        }

        if (badge) {
            badge.textContent = data.cart_count;
            badge.classList.add('animate-bounce');
            setTimeout(() => badge.classList.remove('animate-bounce'), 1000);
        }
    })
    .catch(error => {
        console.error('Fetch Error:', error);
        if (!error.message.includes('Failed to add to cart')) {
            window.dispatchEvent(new CustomEvent('show-cart-toast', { detail: { message: 'Network error. Please try again.' } }));
        }
    });
});
</script>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const qtyInput = document.getElementById('qtyInput');
        const formQtyInput = document.getElementById('formQtyInput');
        const maxQuantity = parseInt(qtyInput.dataset.max);

        formQtyInput.value = qtyInput.value;

        window.increaseQty = function() {
            let current = parseInt(qtyInput.value);
            if (current < maxQuantity) {
                qtyInput.value = current + 1;
            }
            formQtyInput.value = qtyInput.value;
        };

        window.decreaseQty = function() {
            let current = parseInt(qtyInput.value);
            if (current > 1) {
                qtyInput.value = current - 1;
            }
            formQtyInput.value = qtyInput.value;
        };

        qtyInput.addEventListener('input', () => {
            let value = parseInt(qtyInput.value);
            if (isNaN(value) || value < 1) {
                qtyInput.value = 1;
            } else if (value > maxQuantity) {
                qtyInput.value = maxQuantity;
            }
            formQtyInput.value = qtyInput.value;
        });
    });

    function buyNow(productId) {
        const qty = document.getElementById('qtyInput').value;

        const form = document.createElement('form');
        form.method = 'GET';
        form.action = "{{ route('checkout.now') }}";

        const productInput = document.createElement('input');
        productInput.type = 'hidden';
        productInput.name = 'product_id';
        productInput.value = productId;

        const qtyInput = document.createElement('input');
        qtyInput.type = 'hidden';
        qtyInput.name = 'quantity';
        qtyInput.value = qty;

        form.appendChild(productInput);
        form.appendChild(qtyInput);
        document.body.appendChild(form);
        form.submit();
    }
</script>

@if(session('added_to_cart'))
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const badge = document.getElementById('cartBadge');
        if (badge) {
            badge.classList.add('animate-bounce');
            setTimeout(() => {
                badge.classList.remove('animate-bounce');
            }, 1000);
        }
    });
</script>
@endif