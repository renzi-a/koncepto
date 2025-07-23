<x-layout>
    <div class="container mx-auto px-4 py-6 space-y-6">

        <div class="flex items-center justify-between mb-4">
            <h1 class="text-3xl font-bold text-gray-800">Order Management</h1>
        </div>

        <div class="border-b border-gray-200 mb-4">
            <ul class="flex space-x-6">
        <li>
            <a href="?tab=all"
            class="{{ $tab == 'all' ? 'border-b-2 border-green-600 text-green-600' : 'text-gray-600 hover:text-green-600' }} font-semibold pb-2 flex items-center space-x-1">
                <span>All Pending</span>
                <span class="bg-gray-200 text-gray-700 text-xs px-2 py-0.5 rounded-full">{{ $allOrdersCount }}</span>
            </a>
        </li>
        <li>
            <a href="?tab=orders"
            class="{{ $tab == 'orders' ? 'border-b-2 border-green-600 text-green-600' : 'text-gray-600 hover:text-green-600' }} font-semibold pb-2 flex items-center space-x-1">
                <span>Orders</span>
                <span class="bg-gray-200 text-gray-700 text-xs px-2 py-0.5 rounded-full">{{ $normalOrdersCount }}</span>
            </a>
        </li>
        <li>
            <a href="?tab=custom"
            class="{{ $tab == 'custom' ? 'border-b-2 border-green-600 text-green-600' : 'text-gray-600 hover:text-green-600' }} font-semibold pb-2 flex items-center space-x-1">
                <span>Custom Orders</span>
                <span class="bg-gray-200 text-gray-700 text-xs px-2 py-0.5 rounded-full">{{ $customOrdersCount }}</span>
            </a>
        </li>
            <li>
        <a href="?tab=completed"
           class="{{ $tab == 'completed' ? 'border-b-2 border-green-600 text-green-600' : 'text-gray-600 hover:text-green-600' }} font-semibold pb-2 flex items-center space-x-1">
            <span>Completed</span>
            <span class="bg-gray-200 text-gray-700 text-xs px-2 py-0.5 rounded-full">{{ $completedOrdersCount }}</span>
        </a>
    </li>
    </ul>
    
        </div>

        <div class="flex flex-wrap gap-3">
            @php
                $statuses = [];

                if ($tab === 'orders') {
                    $statuses = ['All', 'New', 'To be Delivered', 'Delivered'];
                } elseif ($tab === 'custom') {
                    $statuses = ['All', 'To be Quoted', 'Quoted', 'Approved', 'Gathering', 'To be Delivered', 'Delivered'];
                }
            @endphp

            @if (!empty($statuses))
                <div class="flex flex-wrap gap-3">
                    @foreach($statuses as $status)
                        <button
                            class="px-4 py-1.5 rounded text-sm font-semibold
                                {{ request('status') == $status || (request('status') == null && $status == 'All') ? 'bg-green-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}"
                            onclick="window.location='?tab={{ $tab }}&status={{ urlencode($status) }}'">
                            {{ $status }}
                        </button>
                    @endforeach
                </div>
            @endif

        </div>

        <div class="space-y-4">
    @forelse ($orders as $order)
        <div class="{{ $order->is_custom ? 'bg-yellow-50 border-l-4 border-yellow-400' : 'bg-blue-50 border-l-4 border-blue-400' }} rounded-xl shadow p-4">
            <div class="flex justify-between items-stretch mb-2">
                <div>
                    <h2 class="text-xl font-semibold text-gray-700">
                        Order #{{ $order->is_custom ? 'C' : '' }}{{ $order->id }}
                    </h2>
                    <p class="text-gray-600 mt-1">
                        Items: {{ $order->is_custom ? $order->items_count : ($order->items->count() ?? '-') }}
                    </p>
                    <p class="text-sm text-gray-700">
                        {{ $order->user->first_name ?? '-' }} {{ $order->user->last_name ?? '' }}
                        @if($order->user && $order->user->school)
                            – {{ $order->user->school->school_name }}
                        @endif
                    </p>
                </div>

                <div class="flex flex-col justify-center items-end">
                    <div class="flex flex-col items-end space-y-2">
    <div class="flex space-x-2">
        <span class="inline-block {{ $order->is_custom ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800' }} text-xs px-2 py-1 rounded-full">
            {{ $order->is_custom ? 'Custom' : 'Normal' }}
        </span>
        <span class="inline-block bg-gray-100 text-gray-800 text-xs px-2 py-1 rounded-full">
            {{ ucfirst($order->status) }}
        </span>
    </div>

    @if ($order->is_custom)
        @if (strtolower($order->status) === 'to be quoted')
            <a href="{{ route('admin.custom-orders.quotation', $order->id) }}"
               class="text-xs inline-block mt-1 px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700 transition mt-4">
                Start Quotation
            </a>
        @elseif (strtolower($order->status) === 'approved')
            <p class="text-xs mt-2 text-green-600 italic">Approved. Awaiting gathering.</p>
        @endif
    @endif
</div>


                </div>
            </div>

            <div class="flex justify-between items-center mt-4 text-sm">
<a href="{{ 
    $order->is_custom && in_array(strtolower($order->status), ['approved', 'gathering'])
        ? route('admin.custom-orders.gather', $order->id)
        : ($order->is_custom
            ? route('admin.custom-orders.show', $order->id)
            : route('admin.orders.show', $order->id))
}}"
class="text-blue-500 hover:underline">
    {{ strtolower($order->status) === 'approved' && $order->is_custom ? 'View Approved Order' : 'View Details' }}
</a>


                <span class="text-gray-500">{{ $order->created_at->format('M d, Y') }}</span>
            </div>
        </div>
    @empty
        <p class="text-center text-gray-500 py-8">No orders found.</p>
    @endforelse
</div>


    </div>
</x-layout>
