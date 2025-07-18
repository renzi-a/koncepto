@props(['orders', 'type'])

<div class="overflow-x-auto bg-white border rounded-lg shadow-sm">
    <table class="min-w-full table-auto">
        <thead class="bg-gray-100">
            <tr>
                <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">ID</th>
                <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Customer</th>
                <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Status</th>
                <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Type</th>
                <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Created</th>
                <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($orders as $order)
                <tr class="border-t">
                    <td class="px-4 py-2">{{ $order->id }}</td>
                    <td class="px-4 py-2">{{ optional($order->user)->name ?? 'N/A' }}</td>
                    <td class="px-4 py-2 capitalize">{{ $order->status ?? 'N/A' }}</td>
                    <td class="px-4 py-2">
                        @if($order->is_custom ?? false)
                            <span class="text-yellow-700 bg-yellow-100 px-2 py-1 rounded text-xs font-medium">Custom</span>
                        @else
                            <span class="text-gray-700 bg-gray-100 px-2 py-1 rounded text-xs font-medium">Normal</span>
                        @endif
                    </td>
                    <td class="px-4 py-2 text-sm text-gray-600">{{ $order->created_at->format('M d, Y') }}</td>
                    <td class="px-4 py-2">
                        <a href="{{ route('admin.orders.show', $order->id) }}" class="bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700 text-sm">View</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center text-gray-500 py-6">No orders found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
