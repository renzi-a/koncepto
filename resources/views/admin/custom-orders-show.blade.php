<x-layout>
    <div class="container mx-auto px-4 py-6 space-y-6">
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-800">Custom Order #{{ $order->id }}</h1>
            <span class="text-sm text-gray-500">{{ $order->created_at->format('F j, Y h:i A') }}</span>
            <a href="{{ route('admin.orders') }}" class="text-blue-600 hover:underline">← Back to Custom Orders</a>
        </div>

        <div class="bg-white p-6 rounded shadow space-y-4">
            <p><strong>Status:</strong> {{ ucfirst($order->status) }}</p>
            <p><strong>Total Price:</strong> ₱{{ number_format($order->total_price ?? 0, 2) }}</p>
            <p><strong>School:</strong> {{ $order->user->school->school_name ?? 'N/A' }}</p>
            @php
                $schoolAdmin = optional($order->user->school)->school_admin;
            @endphp
            <p><strong>Requested By (School Admin):</strong> {{ $schoolAdmin->first_name ?? 'N/A' }} {{ $schoolAdmin->last_name ?? '' }}</p>



            <form method="GET" class="mb-4">
                <input type="text" name="search" value="{{ $search }}" placeholder="Search items..."
                       class="border border-gray-300 rounded-lg px-4 py-2 w-full md:w-1/3">
            </form>

            <div class="overflow-x-auto bg-white rounded-lg shadow">
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
                                <td class="px-4 py-2">{{ $item['unit'] ?? 'N/A' }}</td>
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

            
        </div>
    </div>
</x-layout>
