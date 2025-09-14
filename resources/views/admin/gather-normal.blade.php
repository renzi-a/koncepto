<x-layout>
    <div class="container mx-auto px-6 py-8 space-y-6"
         x-data="{
            totalItems: {{ (int) $totalItems }},
            gatheredItems: {{ (int) $gatheredItems }},
            showModal: false,
            finishGathering() {
                const url = '{{ route('admin.orders.gather.store', $order->id) }}';
                fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({})
                })
                .then(response => response.json())
                .then(data => {
                    if (data.redirect) {
                        window.location.href = data.redirect;
                    } else {
                        this.showModal = false;
                        alert(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred. Please try again.');
                });
            }
        }">

        <div class="flex items-center justify-between mb-4">
            <h1 class="text-2xl font-bold text-gray-800">
                Processing Items for Order #{{ $order->order_code ?? $order->id ?? 'N/A' }}
            </h1>
            <a href="{{ route('admin.orders') }}"
                class="text-sm text-blue-600 hover:underline">
                ← Back to orders
            </a>
        </div>

        <p class="text-sm text-gray-700 mb-4">
            Gathered:
            <strong class="text-green-600" x-text="gatheredItems"></strong> of
            <strong x-text="totalItems"></strong> items
        </p>

        <div class="bg-white p-6 rounded-xl shadow">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-4">
                    @if($order->user && $order->user->school && $order->user->school->image)
                        <img src="{{ asset('storage/' . $order->user->school->image) }}"
                                alt="School Logo"
                                class="w-16 h-16 object-cover rounded-full border">
                    @else
                        <div class="w-16 h-16 rounded-full bg-gray-200 flex items-center justify-center text-gray-400 text-sm">
                            No Logo
                        </div>
                    @endif

                    <div>
                        <p class="text-sm text-gray-600">
                            <strong>School:</strong>
                            {{ $order->user->school->school_name ?? 'N/A' }}
                        </p>
                        <p class="text-sm text-gray-600">
                            <strong>School Admin:</strong>
                            {{ $order->user->first_name }} {{ $order->user->last_name }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto mt-4">
                <table class="min-w-full table-auto border text-sm text-gray-700">
                    <thead class="bg-gray-100 text-left">
                        <tr>
                            <th class="px-4 py-2">Item No.</th>
                            <th class="px-4 py-2">Name</th>
                            <th class="px-4 py-2">Unit</th>
                            <th class="px-4 py-2">Quantity</th>
                            <th class="px-4 py-2">Price</th>
                            <th class="px-4 py-2">Total Price</th>
                            <th class="px-4 py-2 text-center">Prepared?</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $grandTotal = 0; @endphp
                        @foreach ($items as $index => $item)
                            @php
                                $price = $item->product->price ?? 0;
                                $quantity = $item->quantity ?? 0;
                                $total = $price * $quantity;
                                $grandTotal += $total;
                            @endphp
                            <tr class="border-t transition-colors"
                                x-data="{ isGathered: {{ $item->gathered ? 'true' : 'false' }} }"
                                :class="{ 'bg-gray-100': isGathered, 'bg-red-50': !isGathered }">
                                <td class="px-4 py-2">{{ $index + 1 }}</td>
                                <td class="px-4 py-2">{{ $item->product->productName ?? 'Unknown' }}</td>
                                <td class="px-4 py-2">{{ $item->product->unit ?? '-' }}</td>
                                <td class="px-4 py-2">{{ $item->quantity }}</td>
                                <td class="px-4 py-2">
                                    ₱{{ number_format($item->product->price ?? 0, 2) }}
                                </td>
                                <td class="px-4 py-2">
                                    ₱{{ number_format(($item->product->price ?? 0) * $item->quantity, 2) }}
                                </td>
                                <td class="px-4 py-2 text-center">
                                    <input
                                        type="checkbox"
                                        x-model="isGathered"
                                        @change="
                                            if (isGathered) {
                                                $root.gatheredItems++;
                                            } else {
                                                $root.gatheredItems--;
                                            }
                                            const url = '{{ route('admin.orders.toggle-gathered', $item->id) }}';
                                            fetch(url, {
                                                method: 'POST',
                                                headers: {
                                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                                    'Accept': 'application/json',
                                                    'Content-Type': 'application/json'
                                                },
                                                body: JSON.stringify({ gathered: isGathered })
                                            }).then(response => {
                                                if (!response.ok) {
                                                    console.error('Failed to update item status on the server.');
                                                    isGathered = !isGathered;
                                                    if (isGathered) {
                                                        $root.gatheredItems++;
                                                    } else {
                                                        $root.gatheredItems--;
                                                    }
                                                }
                                            });
                                        "
                                        class="form-checkbox text-green-600"
                                    />
                                </td>
                            </tr>
                        @endforeach
                        <tr class="bg-gray-100 font-semibold border-t">
                            <td colspan="5" class="px-4 py-2 text-right">Grand Total:</td>
                            <td class="px-4 py-2 text-green-700">₱{{ number_format($grandTotal, 2) }}</td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="mt-6 flex justify-end">
                <button type="button"
                        @click="showModal = true"
                        class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                    Finish Gathering
                </button>
            </div>

            <template x-if="showModal">
                <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
                    <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-sm">
                        <h2 class="text-lg font-semibold mb-4">Confirm Save</h2>
                        <p class="text-gray-600 mb-6">Do you want to save the gathering progress?</p>
                        <div class="flex justify-end gap-4">
                            <button @click="showModal = false" type="button"
                                    class="text-gray-600 hover:text-gray-800">
                                Cancel
                            </button>
                            <button type="button"
                                    @click="finishGathering()"
                                    class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">
                                Confirm
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>
</x-layout>
