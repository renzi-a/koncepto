<x-layout>
    <div class="container mx-auto px-4 py-8">
        <!-- Top-level Header & Tabs -->
        <div class="flex items-center justify-between mb-8 pb-4 border-b border-gray-200">
            <h1 class="text-3xl font-bold text-gray-800">Order Management</h1>
            <div class="flex space-x-4">
                <a href="?section=schools"
                    class="px-4 py-2 rounded-lg text-sm font-medium transition-all duration-300
                           {{ $section == 'schools' ? 'bg-indigo-600 text-white shadow-lg transform scale-105' : 'text-gray-600 hover:bg-gray-100' }}">
                    Schools
                </a>
                <a href="?section=users"
                    class="px-4 py-2 rounded-lg text-sm font-medium transition-all duration-300
                           {{ $section == 'users' ? 'bg-indigo-600 text-white shadow-lg transform scale-105' : 'text-gray-600 hover:bg-gray-100' }}">
                    Users
                </a>
            </div>
        </div>

        @if ($section == 'schools')
            <!-- Sub-tabs for Schools -->
            <div class="mb-6 flex overflow-x-auto space-x-4 pb-2 -mx-4 px-4 sm:mx-0 sm:px-0">
                <ul class="flex-shrink-0 flex items-center space-x-4">
                    <li>
                        <a href="?section=schools&tab=all"
                           class="flex items-center space-x-2 px-4 py-2 rounded-full transition-colors duration-200
                                  {{ $tab == 'all' ? 'bg-green-500 text-white shadow-md' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                            <span class="font-semibold">All Pending</span>
                            <span class="bg-white text-green-600 text-xs px-2 py-0.5 rounded-full font-bold">
                                {{ $allOrdersCount }}
                            </span>
                        </a>
                    </li>
                    <li>
                        <a href="?section=schools&tab=orders"
                           class="flex items-center space-x-2 px-4 py-2 rounded-full transition-colors duration-200
                                  {{ $tab == 'orders' ? 'bg-green-500 text-white shadow-md' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                            <span class="font-semibold">Orders</span>
                            <span class="bg-white text-green-600 text-xs px-2 py-0.5 rounded-full font-bold">
                                {{ $normalOrdersCount }}
                            </span>
                        </a>
                    </li>
                    <li>
                        <a href="?section=schools&tab=custom"
                           class="flex items-center space-x-2 px-4 py-2 rounded-full transition-colors duration-200
                                  {{ $tab == 'custom' ? 'bg-green-500 text-white shadow-md' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                            <span class="font-semibold">Custom Orders</span>
                            <span class="bg-white text-green-600 text-xs px-2 py-0.5 rounded-full font-bold">
                                {{ $customOrdersCount }}
                            </span>
                        </a>
                    </li>
                    <li>
                        <a href="?section=schools&tab=completed"
                           class="flex items-center space-x-2 px-4 py-2 rounded-full transition-colors duration-200
                                  {{ $tab == 'completed' ? 'bg-green-500 text-white shadow-md' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                            <span class="font-semibold">Completed</span>
                            <span class="bg-white text-green-600 text-xs px-2 py-0.5 rounded-full font-bold">
                                {{ $completedOrdersCount }}
                            </span>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Status Filter Buttons -->
            @php
                $statuses = [];
                if ($tab === 'orders') {
                    $statuses = ['All', 'New', 'Processing', 'To be Delivered', 'Delivered'];
                } elseif ($tab === 'custom') {
                    $statuses = ['All', 'To be Quoted', 'Quoted', 'Approved', 'Processing', 'To be Delivered', 'Delivered'];
                }
            @endphp
            @if (!empty($statuses))
                <div class="flex flex-wrap gap-2 mb-6">
                    @foreach($statuses as $status)
                        <button
                            class="px-4 py-2 rounded-full text-sm font-semibold transition-all duration-300
                                   {{ request('status') == $status || (request('status') == null && $status == 'All')
                                       ? 'bg-blue-600 text-white shadow-md transform scale-105'
                                       : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}"
                            onclick="window.location='?section=schools&tab={{ $tab }}&status={{ urlencode($status) }}'">
                            {{ $status }}
                        </button>
                    @endforeach
                </div>
            @endif

            <!-- Orders List Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @forelse ($orders as $order)
                    <div class="bg-white rounded-2xl shadow-xl p-4 transition-all duration-300 hover:shadow-2xl hover:scale-105
                                {{ $order->is_custom ? 'border-l-8 border-yellow-400' : 'border-l-8 border-blue-400' }}">
                        <div class="flex flex-col h-full">
                            <div class="flex-grow">
                                <h2 class="text-lg font-bold text-gray-800 mb-1">
                                    Order #{{ $order->order_code ?? $order->id }}
                                </h2>
                                <p class="text-gray-600 text-xs mt-1">
                                    <span class="font-semibold text-sm">{{ $order->user->first_name ?? '-' }} {{ $order->user->last_name ?? '' }}</span>
                                    @if($order->user && $order->user->school)
                                        <span class="text-gray-500 block mt-1"> – {{ $order->user->school->school_name }}</span>
                                    @endif
                                </p>
                                <div class="mt-2">
                                    <div class="flex items-center space-x-2 mb-1">
                                        <span class="inline-block text-xs px-2 py-0.5 rounded-full font-semibold
                                                      {{ $order->is_custom ? 'bg-yellow-200 text-yellow-800' : 'bg-blue-200 text-blue-800' }}">
                                            {{ $order->is_custom ? 'Custom' : 'Normal' }}
                                        </span>
                                        @php
                                            $statusClass = '';
                                            switch(strtolower($order->status)) {
                                                case 'new':
                                                    $statusClass = 'bg-red-200 text-red-800';
                                                    break;
                                                case 'processing':
                                                    $statusClass = 'bg-blue-200 text-blue-800';
                                                    break;
                                                case 'to be delivered':
                                                    $statusClass = 'bg-purple-200 text-purple-800';
                                                    break;
                                                case 'delivered':
                                                    $statusClass = 'bg-green-200 text-green-800';
                                                    break;
                                                case 'to be quoted':
                                                    $statusClass = 'bg-orange-200 text-orange-800';
                                                    break;
                                                case 'quoted':
                                                    $statusClass = 'bg-indigo-200 text-indigo-800';
                                                    break;
                                                case 'approved':
                                                    $statusClass = 'bg-teal-200 text-teal-800';
                                                    break;
                                                default:
                                                    $statusClass = 'bg-gray-200 text-gray-800';
                                                    break;
                                            }
                                        @endphp
                                        <span class="inline-block text-xs px-2 py-0.5 rounded-full font-semibold {{ $statusClass }}">
                                            {{ ucfirst($order->status) }}
                                        </span>
                                    </div>

                                    @if ($order->is_custom && strtolower($order->status) === 'processing')
                                        @php
                                            $progress = $order->items_count > 0 ? ($order->gathered_items_count / $order->items_count) * 100 : 0;
                                        @endphp
                                        <div class="mt-2 w-full bg-gray-200 rounded-full h-2">
                                            <div class="h-2 rounded-full transition-all duration-500 {{ $progress == 100 ? 'bg-green-500' : 'bg-blue-500' }}" style="width: {{ $progress }}%"></div>
                                        </div>
                                        <span class="block text-xs font-medium text-gray-700 mt-2">
                                            Processing: {{ $order->gathered_items_count }} of {{ $order->items_count }} items
                                        </span>
                                    @elseif (in_array(strtolower($order->status), ['new', 'processing']))
                                        @php
                                            $gatheredCount = $order->items->where('gathered', true)->count();
                                            $totalCount = $order->items->count();
                                            $progress = $totalCount > 0 ? ($gatheredCount / $totalCount) * 100 : 0;
                                        @endphp
                                        <div class="mt-2 w-full bg-gray-200 rounded-full h-2">
                                            <div class="h-2 rounded-full transition-all duration-500 {{ $progress == 100 ? 'bg-green-500' : 'bg-blue-500' }}" style="width: {{ $progress }}%"></div>
                                        </div>
                                        <span class="block text-xs font-medium text-gray-700 mt-2">
                                            Processing: {{ $gatheredCount }} of {{ $totalCount }} items
                                        </span>
                                    @else
                                        <p class="text-xs text-gray-700">Items: {{ $order->is_custom ? $order->items_count : ($order->items->count() ?? '-') }}</p>
                                    @endif
                                </div>
                            </div>

                            <div class="mt-4 flex flex-col items-center">
                                @if ($order->is_custom)
                                    @if (strtolower($order->status) === 'to be quoted')
                                        <a href="{{ route('admin.custom-orders.quotation', $order->id) }}"
                                           class="w-full text-center py-1.5 bg-green-600 text-white font-semibold rounded-lg shadow-md hover:bg-green-700 transition">
                                            Start Quotation
                                        </a>
                                    @elseif (strtolower($order->status) === 'approved')
                                        <p class="text-xs mt-1 text-green-600 italic">
                                            Approved. Awaiting processing.
                                        </p>
                                    @endif
                                @endif
                                <a href="{{
                                    $order->is_custom
                                        ? (in_array(strtolower($order->status), ['approved', 'processing'])
                                            ? route('admin.custom-orders.gather', $order->id)
                                            : route('admin.custom-orders.show', $order->id))
                                        : (in_array(strtolower($order->status), ['new', 'processing'])
                                            ? route('admin.orders.gather', $order->id)
                                            : route('admin.orders.show', $order->id))
                                }}"
                                   class="w-full text-center py-1.5 mt-2 text-white font-semibold rounded-lg shadow-md transition-all duration-300
                                       {{ $order->is_custom
                                           ? (in_array(strtolower($order->status), ['approved', 'processing'])
                                               ? 'bg-blue-600 hover:bg-blue-700'
                                               : 'bg-indigo-600 hover:bg-indigo-700')
                                           : (in_array(strtolower($order->status), ['new', 'processing'])
                                               ? 'bg-blue-600 hover:bg-blue-700'
                                               : 'bg-indigo-600 hover:bg-indigo-700')
                                       }}">
                                    @if ($order->is_custom)
                                        {{ in_array(strtolower($order->status), ['approved', 'processing']) ? 'Start Processing' : 'View Details' }}
                                    @else
                                        {{ in_array(strtolower($order->status), ['new', 'processing']) ? 'Start Processing' : 'View Details' }}
                                    @endif
                                </a>
                            </div>

                            <div class="border-t border-gray-200 pt-2 mt-2 text-xs text-gray-500 text-right">
                                <p>Created: {{ $order->created_at->format('M d, Y') }}</p>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-1 md:col-span-2 lg:col-span-3 bg-white rounded-2xl shadow-xl p-8 text-center text-gray-500">
                        <p>No orders found for this section.</p>
                    </div>
                @endforelse
            </div>
        @else
            <!-- Placeholder for Users tab content -->
            <div class="bg-white rounded-2xl shadow-xl p-8 text-center text-gray-500">
                <p>User management features will be available here.</p>
            </div>
        @endif
    </div>
</x-layout>
