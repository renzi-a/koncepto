<x-profile-link>
    <div class="container mx-auto px-4 py-6 space-y-8">
        <div class="flex justify-between items-center mb-4">
            <h1 class="text-3xl font-bold text-gray-800">School Dashboard</h1>
            <a href="{{ route('user.custom-order') }}"
                class="inline-flex items-center px-4 py-2 bg-[#56AB2F] text-white font-semibold rounded-md shadow-sm hover:bg-green-700 transition">
                + Place New Order
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            
            <div class="bg-white rounded-lg shadow p-6 flex flex-col items-start space-y-4">
                <div class="text-sm text-gray-500 font-medium">Total Orders</div>
                <div class="flex items-end justify-between w-full">
                    <div class="flex-1">
                        <div class="text-4xl font-bold text-blue-600">{{ $totalOrders }}</div>
                        <p class="text-xs text-gray-400">All orders placed</p>
                    </div>
                    <div class="flex-1 text-right">
                        <p class="text-sm text-gray-500 font-medium mb-1">Breakdown</p>
                        <p class="text-xs text-gray-400">Regular: <span class="font-bold text-gray-700">{{ $regularOrderCount }}</span></p>
                        <p class="text-xs text-gray-400">Custom: <span class="font-bold text-gray-700">{{ $customOrderCount }}</span></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6 flex flex-col items-start space-y-2">
                <div class="text-sm text-gray-500 font-medium">Delivered Orders</div>
                <div class="text-4xl font-bold mt-2 text-green-600">
                    {{ $deliveredOrders > 0 ? $deliveredOrders : '0' }}
                </div>
                <p class="text-xs text-gray-400">
                    Total delivered, including custom orders
                </p>
            </div>
            
            <div class="bg-white rounded-lg shadow p-6 flex flex-col items-start space-y-2">
                <div class="text-sm text-gray-500 font-medium">Custom Order Items</div>
                <div class="text-4xl font-bold text-purple-600">
                    {{ $customOrderItemsCount > 0 ? $customOrderItemsCount : '0' }}
                </div>
                <p class="text-xs text-gray-400">Total items in custom orders</p>
            </div>
            
            <div class="bg-white rounded-lg shadow p-6 flex flex-col items-start space-y-2">
                <div class="text-sm text-gray-500 font-medium">Total Users</div>
                <div class="text-4xl font-bold text-indigo-600">
                    {{ $studentCount + $teacherCount }}
                </div>
                <p class="text-xs text-gray-400">
                    Teachers: <span class="font-bold text-gray-700">{{ $teacherCount }}</span> / 
                    Students: <span class="font-bold text-gray-700">{{ $studentCount }}</span>
                </p>
            </div>

        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold text-gray-700 mb-4">Monthly Order Trend</h2>
            <canvas id="schoolSalesTrendChart" class="w-full h-[400px]"></canvas>
        </div>

        <div class="bg-white rounded-lg shadow p-6 overflow-x-auto">
            <h2 class="text-xl font-semibold text-gray-700 mb-4">Recent Orders</h2>
            <table class="min-w-full text-sm text-left">
                <thead>
                    <tr class="text-gray-700 border-b">
                        <th class="py-2 px-4">Order ID</th>
                        <th class="py-2 px-4">Type</th>
                        <th class="py-2 px-4">Date</th>
                        <th class="py-2 px-4">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($combinedRecentOrders as $order)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-2 px-4">#{{ $order->id }}</td>
                            <td class="py-2 px-4">
                                <span class="inline-block px-2 py-1 rounded-full text-xs font-semibold {{ $order->type === 'Regular' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                                    {{ $order->type }}
                                </span>
                            </td>
                            <td class="py-2 px-4">{{ $order->created_at->format('M d, Y') }}</td>
                            <td class="py-2 px-4">
                                @if($order->type === 'Regular')
                                <span class="inline-block px-2 py-1 rounded-full text-xs {{ $order->status === 'delivered' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                    {{ ucfirst($order->status) }}
                                </span>
                                @else
                                <span class="inline-block px-2 py-1 rounded-full text-xs bg-gray-100 text-gray-800">
                                    N/A
                                </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-gray-500 py-4">No recent orders found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        const ctx = document.getElementById('schoolSalesTrendChart');
        const labels = @json($salesLabels);
        const data = @json($salesData);

        const hasData = data.some(v => v > 0);

        if (ctx && hasData) {
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Total Orders',
                        data: data,
                        borderColor: '#2563EB',
                        backgroundColor: 'rgba(37, 99, 235, 0.1)',
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        } else if (ctx) {
            ctx.parentNode.innerHTML = '<div class="text-gray-500 text-sm text-center py-10">No sales trend data available</div>';
        }
    </script>
</x-profile-link>