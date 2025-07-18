<x-profile-link>
    <div class="container mx-auto px-4 py-6 space-y-6">
        <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl md:text-3xl font-bold text-gray-800 text-right">
        @if ($order instanceof \App\Models\CustomOrder)
            Custom Order #{{ $order->id }}
        @else
            Order #{{ $order->id }}
        @endif
    </h1>
    <a href="{{ route('user.order-history') }}" class="text-blue-600 hover:underline text-sm">
        ← Back to Order History
    </a>
</div>


        <div class="bg-white p-6 rounded-lg shadow space-y-4">
            <p><strong>Status:</strong> {{ ucfirst($order->status) }}</p>
            <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($order->created_at)->format('M d, Y') }}</p>
            <p><strong>Reason:</strong> {{ $order->reason ?? 'No description.' }}</p>
        </div>

        @if ($items->count())
            <div class="overflow-x-auto mt-6">
                <table class="min-w-full bg-white rounded-lg shadow">
                    <thead>
                        <tr class="bg-gray-100 text-left text-sm font-semibold text-gray-700">
                            <th class="px-6 py-3">Photo</th>
                            <th class="px-6 py-3">Name</th>
                            <th class="px-6 py-3">Brand</th>
                            <th class="px-6 py-3">Unit</th>
                            <th class="px-6 py-3">Quantity</th>
                            <th class="px-6 py-3">Description</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700 text-sm">
                        @foreach ($items as $item)
                            <tr class="border-t">
                                <td class="px-6 py-4">
                                    @if ($item['photo'])
                                        <img src="{{ asset('storage/' . $item['photo']) }}" alt="Item Photo" class="w-16 h-16 object-cover rounded">
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td class="px-6 py-4">{{ $item['name'] }}</td>
                                <td class="px-6 py-4">{{ $item['brand'] ?? 'N/A' }}</td>
                                <td class="px-6 py-4">{{ $item['unit'] }}</td>
                                <td class="px-6 py-4">{{ $item['quantity'] }}</td>
                                <td class="px-6 py-4">{{ $item['description'] ?? 'No description' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $items->links() }}
            </div>
        @else
            <p class="mt-4 text-gray-500">No items found.</p>
        @endif

        
    </div>
</x-profile-link>
