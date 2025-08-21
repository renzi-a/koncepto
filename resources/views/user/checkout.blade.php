<x-nav-link />

<div class="bg-gray-100 py-10 min-h-screen flex justify-center items-start">
    <div class="max-w-6xl w-full bg-white p-10 rounded-xl shadow-md">

        <h1 class="text-3xl font-bold text-gray-800 mb-10">Checkout</h1>

        @if (session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Error!</strong>
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif
        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Success!</strong>
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-12">

            <section>
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Order Summary</h2>

                @php $total = 0; @endphp

                @forelse ($items as $item)
                    @php
                        $subtotal = $item->product->price * $item->quantity;
                        $total += $subtotal;
                    @endphp
                    <div class="bg-gray-50 shadow-sm rounded-lg px-6 py-4 mb-4 flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            @if ($item->product->image)
                                <img src="{{ asset('storage/' . $item->product->image) }}" alt="{{ $item->product->productName }}" class="w-20 h-20 object-cover rounded-md shadow-sm">
                            @else
                                <div class="w-20 h-20 bg-gray-200 rounded-md flex items-center justify-center text-gray-400 text-xs text-center p-2">No Image</div>
                            @endif
                            <div>
                                <p class="text-lg font-medium text-gray-900">{{ $item->product->productName }}</p>
                                <p class="text-sm text-gray-600">Qty: {{ $item->quantity }}</p>
                            </div>
                        </div>
                        <div class="text-right text-gray-800 font-semibold">
                            ₱{{ number_format($subtotal, 2) }}
                        </div>
                    </div>
                @empty
                    <p class="text-gray-500 italic">Your selected items will appear here.</p>
                @endforelse

                <div class="mt-8 border-t pt-4 flex justify-between items-center text-xl font-bold text-gray-800">
                    <span>Total:</span>
                    <span class="text-[#56AB2F]">₱{{ number_format($total, 2) }}</span>
                </div>
            </section>

            <section>
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Bank Check Payment</h2>
                <form id="checkoutForm" method="POST" action="{{ route('checkout.process') }}" x-data="{ loading: false }" @submit="loading = true">
                    @csrf

                    {{-- Conditional Hidden Inputs for Immediate Checkout --}}
                    @if(isset($checkout_type) && $checkout_type === 'immediate')
                        <input type="hidden" name="checkout_type" value="immediate">
                        <input type="hidden" name="product_id" value="{{ $immediate_product_id }}">
                        <input type="hidden" name="quantity" value="{{ $immediate_quantity }}">
                    @else
                        <input type="hidden" name="checkout_type" value="cart">
                    @endif

                    <div class="mb-6">
                        <label for="payment_date" class="block mb-2 font-medium text-gray-700">Payment Date</label>
                        <input
                            type="date"
                            name="payment_date"
                            id="payment_date"
                            min="{{ $minDate }}"
                            max="{{ $maxDate }}"
                            required
                            class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-[#56AB2F]"
                        >
                        @error('payment_date')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit"
                        :disabled="loading || {{ $items->isEmpty() ? 'true' : 'false' }}" {{-- Disable if items are empty --}}
                        class="w-full bg-[#56AB2F] text-white text-lg font-semibold px-6 py-3 rounded-lg hover:bg-green-700 transition flex items-center justify-center space-x-2
                        {{ $items->isEmpty() ? 'opacity-50 cursor-not-allowed' : '' }}"
                    >
                        <span x-show="!loading">Place Order</span>
                        <span x-show="loading" class="flex items-center space-x-2">
                            <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                            </svg>
                            <span>Processing...</span>
                        </span>
                    </button>
                </form>
            </section>

        </div>
    </div>
</div>
<x-footer />