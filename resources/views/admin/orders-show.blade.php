<x-layout>
    <div class="container mx-auto px-4 py-6 space-y-6">
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-800">Order #{{ $order->id }}</h1>
            <span class="text-sm text-gray-500">{{ $order->created_at->format('F j, Y h:i A') }}</span>
            <a href="{{ route('admin.orders') }}" class="text-blue-600 hover:underline">← Back to Orders</a>
        </div>

        <div class="bg-white p-6 rounded shadow space-y-4">
            <p><strong>Status:</strong> {{ ucfirst($order->status) }}</p>
            <p><strong>Customer:</strong> {{ $order->user->first_name ?? 'N/A' }} {{ $order->user->last_name ?? '' }}</p>

            <hr>

            <h2 class="font-semibold text-lg mb-2">Order Details</h2>

            <div class="overflow-x-auto bg-white rounded-lg shadow">
                <table class="min-w-full table-auto text-sm text-left text-gray-600">
                    <thead class="bg-gray-100 text-gray-800 font-semibold">
                        <tr>
                            <th class="px-4 py-3">Name</th>
                            <th class="px-4 py-3">Brand</th>
                            <th class="px-4 py-3">Unit</th>
                            <th class="px-4 py-3">Quantity</th>
                            <th class="px-4 py-3">Description</th>
                            <th class="px-4 py-3">Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($order->orderDetails as $detail)
                            <tr class="border-t">
                                <td class="px-4 py-2">{{ $detail->product->productName ?? 'Deleted Product' }}</td>
                                <td class="px-4 py-2">{{ $detail->product->brandName ?? 'N/A' }}</td>
                                <td class="px-4 py-2">{{ $detail->product->unit ?? 'N/A' }}</td>
                                <td class="px-4 py-2">{{ $detail->quantity }}</td>
                                <td class="px-4 py-2">{{ $detail->product->description ?? 'No description' }}</td>
                                <td class="px-4 py-2 font-semibold">₱{{ number_format($detail->price, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-4 text-center text-gray-500">No items found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layout>
