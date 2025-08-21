<x-layout>
    <div class="container mx-auto px-6 py-8 space-y-6"
        x-data="{ totalItems: {{ (int) $totalItems }}, gatheredItems: {{ (int) $gatheredItems }}, showModal: false }">

        <!-- Header -->
        <div class="flex items-center justify-between mb-4">
            <h1 class="text-2xl font-bold text-gray-800">
                Gathering Items for Order #C{{ $order->id }}
            </h1>
            <a href="{{ route('admin.orders') }}"
                class="text-sm text-blue-600 hover:underline">
                ← Back to orders
            </a>
        </div>

        <!-- Counters -->
        <p class="text-sm text-gray-700 mb-4">
            Gathered:
            <strong class="text-green-600" x-text="gatheredItems"></strong> of
            <strong x-text="totalItems"></strong> items
        </p>

        <!-- Main Card -->
        <div class="bg-white p-6 rounded-xl shadow">
            <!-- School Info -->
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

                <a href="{{ route('admin.custom-orders.gather-pdf', $order->id) . '?v=' . now()->timestamp }}"
                    target="_blank"
                    class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-2 rounded">
                    View PDF
                </a>
            </div>

            <!-- Table -->
            <form method="POST" action="{{ route('admin.custom-orders.gather.store', $order->id) }}">
                @csrf
                <div class="overflow-x-auto mt-4">
                    <table class="min-w-full table-auto border text-sm text-gray-700">
                        <thead class="bg-gray-100 text-left">
                            <tr>
                                <th class="px-4 py-2">Item No.</th>
                                <th class="px-4 py-2">Name</th>
                                <th class="px-4 py-2">Brand</th>
                                <th class="px-4 py-2">Unit</th>
                                <th class="px-4 py-2">Quantity</th>
                                <th class="px-4 py-2">Photo</th>
                                <th class="px-4 py-2">Description</th>
                                <th class="px-4 py-2">Price</th>
                                <th class="px-4 py-2">Total Price</th>
                                <th class="px-4 py-2 text-center">Prepared?</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $grandTotal = 0; @endphp
                            @foreach ($items as $index => $item)
                                @php
                                    $price = $item->price ?? 0;
                                    $quantity = $item->quantity ?? 0;
                                    $total = $price * $quantity;
                                    $grandTotal += $total;
                                @endphp
                                <tr class="border-t transition-colors"
                                    :class="{ 'bg-red-100': !{{ $item->gathered ? 'true' : 'false' }}, 
                                              'hover:bg-gray-50': {{ $item->gathered ? 'true' : 'false' }},
                                              'hover:bg-red-200': !{{ $item->gathered ? 'true' : 'false' }} }"
                                    x-data="{ gathered: {{ $item->gathered ? 'true' : 'false' }} }"
                                    :id="'item-row-{{ $item->id }}'">
                                    <td class="px-4 py-2">{{ $index + 1 }}</td>
                                    <td class="px-4 py-2">{{ $item->name }}</td>
                                    <td class="px-4 py-2">{{ $item->brand ?? '-' }}</td>
                                    <td class="px-4 py-2">{{ $item->unit ?? '-' }}</td>
                                    <td class="px-4 py-2">{{ $item->quantity }}</td>
                                    <td class="px-4 py-2">
                                        @if (!empty($item->photo))
                                            <img src="{{ asset('storage/' . $item->photo) }}" 
                                                    alt="Photo" 
                                                    class="w-12 h-12 object-cover rounded border">
                                        @else
                                            <span class="text-gray-400">No Image</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2">{{ $item->description ?? '-' }}</td>
                                    <td class="px-4 py-2">
                                        @isset($item->price)
                                            ₱{{ number_format($item->price, 2) }}
                                        @else
                                            <span class="text-gray-400">Not yet set</span>
                                        @endisset
                                    </td>
                                    <td class="px-4 py-2">
                                        @isset($item->price)
                                            ₱{{ number_format($total, 2) }}
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endisset
                                    </td>
                                    <td class="px-4 py-2 text-center">
                                        <input
                                            type="checkbox"
                                            :checked="gathered"
                                            @change="
                                                gathered = !gathered;
                                                const url = '{{ route('admin.custom-orders.toggle-gathered', $item->id) }}';
                                                fetch(url, {
                                                    method: 'POST',
                                                    headers: {
                                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                                        'Accept': 'application/json',
                                                        'Content-Type': 'application/json'
                                                    },
                                                    body: JSON.stringify({ gathered })
                                                }).then(response => {
                                                    if (response.ok) {
                                                        if (gathered) {
                                                            gatheredItems++;
                                                            $el.closest('tr').classList.remove('bg-red-100');
                                                            $el.closest('tr').classList.remove('hover:bg-red-200');
                                                            $el.closest('tr').classList.add('hover:bg-gray-50');
                                                        } else {
                                                            gatheredItems--;
                                                            $el.closest('tr').classList.add('bg-red-100');
                                                            $el.closest('tr').classList.add('hover:bg-red-200');
                                                            $el.closest('tr').classList.remove('hover:bg-gray-50');
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
                                <td colspan="8" class="px-4 py-2 text-right">Grand Total:</td>
                                <td class="px-4 py-2 text-green-700">₱{{ number_format($grandTotal, 2) }}</td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Save Button -->
                <div class="mt-6 flex justify-end">
                    <button type="button"
                            @click="showModal = true"
                            class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                        Save
                    </button>
                </div>

                <!-- Confirmation Modal -->
                <template x-if="showModal">
                    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
                        <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-sm">
                            <h2 class="text-lg font-semibold mb-4">Confirm Save</h2>
                            <p class="text-gray-600 mb-6">Are you sure you want to save this gathering update?</p>

                            <div class="flex justify-end gap-4">
                                <button @click="showModal = false" type="button"
                                        class="text-gray-600 hover:text-gray-800">
                                    Cancel
                                </button>
                                <button type="submit"
                                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">
                                    Confirm
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
            </form>
        </div>
    </div>
</x-layout>
