<x-profile-link>
    <div class="container mx-auto px-4 py-6 space-y-6">
        <div class="flex flex-col md:flex-row md:justify-between md:items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800 mb-4 md:mb-0">Order #{{ $order->id }} - Item Details</h1>
            <a href="{{ route('user.order.index') }}" 
               class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition">
                ← Back to Orders
            </a>
        </div>

        <div class="overflow-x-auto bg-white shadow rounded-lg">
            <table class="min-w-full table-auto text-sm text-left text-gray-600">
                <thead class="bg-gray-100 text-gray-800 font-semibold">
                    <tr>
                        <th class="px-4 py-3">Name</th>
                        <th class="px-4 py-3">Brand</th>
                        <th class="px-4 py-3">Unit</th>
                        <th class="px-4 py-3">Quantity</th>
                        <th class="px-4 py-3 text-right">Price</th>
                        <th class="px-4 py-3 text-right">Total Price</th>
                    </tr>
                </thead>
                <tbody>
                    @php $grandTotal = 0; @endphp
                    @forelse ($items as $item)
                        <tr class="border-t hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-2 font-medium text-gray-800">{{ $item['name'] }}</td>
                            <td class="px-4 py-2">{{ $item['brand'] ?? 'N/A' }}</td>
                            <td class="px-4 py-2">{{ $item['unit'] }}</td>
                            <td class="px-4 py-2">{{ $item['quantity'] }}</td>
                            <td class="px-4 py-2 text-right">₱{{ number_format($item['price'] ?? 0, 2) }}</td>
                            <td class="px-4 py-2 text-right">
                                ₱{{ number_format(($item['price'] ?? 0) * ($item['quantity'] ?? 0), 2) }}
                            </td>
                        </tr>
                        @php $grandTotal += ($item['price'] ?? 0) * ($item['quantity'] ?? 0); @endphp
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-4 text-center text-gray-500">No items found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($items->isNotEmpty())
            <div class="flex justify-end items-center mt-4 p-4 bg-white shadow rounded-lg font-bold text-lg text-gray-800">
                <div class="mr-4">Grand Total:</div>
                <div>₱{{ number_format($grandTotal, 2) }}</div>
            </div>
        @endif

        <div class="mt-4">
            {{ $items->withQueryString()->links() }}
        </div>
    </div>
</x-profile-link>