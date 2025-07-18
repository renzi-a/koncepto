<x-profile-link>
    <div class="container mx-auto px-4 py-6 space-y-6">
        <h1 class="text-3xl font-bold text-gray-800">Order #{{ $order->id }} - Item Details</h1>

        <form method="GET" class="mb-4">
            <input type="text" name="search" value="{{ $search }}" placeholder="Search items..."
                   class="border border-gray-300 rounded-lg px-4 py-2 w-full md:w-1/3">
        </form>

        <div class="overflow-x-auto bg-white shadow rounded-lg">
            <table class="min-w-full table-auto text-sm text-left text-gray-600">
                <thead class="bg-gray-100 text-gray-800 font-semibold">
                    <tr>
                        <th class="px-4 py-3">Name</th>
                        <th class="px-4 py-3">Brand</th>
                        <th class="px-4 py-3">Unit</th>
                        <th class="px-4 py-3">Quantity</th>
                        <th class="px-4 py-3">Description</th>
                        <th class="px-4 py-3">Photo</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $item)
                        <tr class="border-t">
                            <td class="px-4 py-2">{{ $item['name'] }}</td>
                            <td class="px-4 py-2">{{ $item['brand'] ?? 'N/A' }}</td>
                            <td class="px-4 py-2">{{ $item['unit'] }}</td>
                            <td class="px-4 py-2">{{ $item['quantity'] }}</td>
                            <td class="px-4 py-2">{{ $item['description'] ?? 'N/A' }}</td>
                            <td class="px-4 py-2">
                                @if (!empty($item['photo']))
                                    <img src="{{ asset('storage/' . $item['photo']) }}" class="w-16 h-16 object-cover rounded" />
                                @else
                                    <span class="text-gray-400 italic">No photo</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-4 text-center text-gray-500">No items found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $items->withQueryString()->links() }}
        </div>

        <a href="{{ route('user.order.index') }}" class="text-blue-600 hover:underline">← Back to Orders</a>
    </div>
</x-profile-link>