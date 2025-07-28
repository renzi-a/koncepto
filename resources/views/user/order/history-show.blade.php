<x-profile-link>
    <div class="container mx-auto px-4 py-6 space-y-8">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 space-y-4 md:space-y-0">
            <h1 class="text-2xl md:text-3xl font-bold text-gray-800 flex items-center space-x-3">
                <span>
                    @if ($order instanceof \App\Models\CustomOrder)
                        Custom Order #{{ $order->id }}
                    @else
                        Order #{{ $order->id }}
                    @endif
                </span>
                @php
                    $statusColor = 'text-green-600 bg-green-100';
                    if ($order->status === 'cancelled') {
                        $statusColor = 'text-red-600 bg-red-100';
                    } elseif ($order->status !== 'delivered' && $order->status !== 'cancelled') {
                        $statusColor = 'text-yellow-600 bg-yellow-100';
                    }
                @endphp
                <span class="text-sm font-semibold px-3 py-1 rounded-full {{ $statusColor }}">
                    {{ ucfirst($order->status) }}
                </span>
            </h1>
            <a href="{{ route('user.order-history') }}" class="text-blue-600 hover:text-blue-800 transition text-sm flex items-center space-x-1">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Back to Order History</span>
            </a>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-md grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <p class="text-xs text-gray-500 font-medium">Order ID</p>
                <p class="text-gray-800 font-semibold mt-1">#{{ $order->id }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 font-medium">Date Placed</p>
                <p class="text-gray-800 mt-1">{{ \Carbon\Carbon::parse($order->created_at)->format('F d, Y') }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 font-medium">Total Items</p>
                <p class="text-gray-800 mt-1">{{ $items->count() }}</p>
            </div>
        </div>

        @if ($order->status === 'cancelled' && $order->reason)
            <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded-lg shadow-sm">
                <p class="text-red-700 font-semibold">Reason for Cancellation:</p>
                <p class="text-red-600 mt-1 italic">{{ $order->reason }}</p>
            </div>
        @endif

        <h2 class="text-2xl font-semibold text-gray-800 mt-8">Order Items</h2>
        @if ($items->count())
            <div class="overflow-x-auto bg-white rounded-lg shadow-md">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Photo</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Details</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden md:table-cell">Unit</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden md:table-cell">Quantity</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($items as $item)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if ($item['photo'])
                                        <img src="{{ asset('storage/' . $item['photo']) }}" alt="{{ $item['name'] }}" class="w-16 h-16 object-cover rounded-md shadow-sm">
                                    @else
                                        <div class="w-16 h-16 bg-gray-200 rounded-md flex items-center justify-center text-gray-500 text-xs">N/A</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $item['name'] }}</div>
                                    <div class="text-xs text-gray-500 mt-1">Brand: {{ $item['brand'] ?? 'N/A' }}</div>
                                    <div class="text-xs text-gray-500 md:hidden mt-1">Unit: {{ $item['unit'] }} | Qty: {{ $item['quantity'] }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap hidden md:table-cell">
                                    <div class="text-sm text-gray-900">{{ $item['unit'] }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap hidden md:table-cell">
                                    <div class="text-sm text-gray-900">{{ $item['quantity'] }}</div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $items->links() }}
            </div>
        @else
            <div class="bg-white p-6 rounded-lg shadow-md text-center">
                <p class="text-gray-500">No items found for this order.</p>
            </div>
        @endif
    </div>
</x-profile-link>