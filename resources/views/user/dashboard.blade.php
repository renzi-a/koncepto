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
            
            <div class="bg-white rounded-lg shadow p-6 flex flex-col hover:shadow-lg transition-shadow duration-300">
                <div class="text-sm text-gray-500 font-medium mb-4">Total Orders</div>
                <div class="flex items-center justify-between w-full">
                    <div>
                        <div class="text-4xl font-bold text-blue-600">{{ $totalOrders }}</div>
                        <p class="text-xs text-gray-400">All orders placed</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-500 font-medium mb-1">Breakdown</p>
                        <p class="text-xs text-gray-600">Regular: <span class="font-bold">{{ $regularOrderCount }}</span></p>
                        <p class="text-xs text-gray-600">Custom: <span class="font-bold">{{ $customOrderCount }}</span></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6 flex flex-col justify-between hover:shadow-lg transition-shadow duration-300">
                <div class="text-sm text-gray-500 font-medium">Delivered Orders</div>
                <div class="text-4xl font-bold text-green-600 mt-2">
                    {{ $deliveredOrders > 0 ? $deliveredOrders : '0' }}
                </div>
                <p class="text-xs text-gray-400 mt-2">Total delivered, including custom orders</p>
            </div>
            
            <div class="bg-white rounded-lg shadow p-6 flex flex-col justify-between hover:shadow-lg transition-shadow duration-300">
                <div class="text-sm text-gray-500 font-medium">Items Ordered</div>
                <div class="text-4xl font-bold text-purple-600 mt-2">
                    {{ $totalItemsCount > 0 ? $totalItemsCount : '0' }}
                </div>
                <p class="text-xs text-gray-400 mt-2">Total items across all orders</p>
            </div>
            
            <div class="bg-white rounded-lg shadow p-6 flex flex-col justify-between hover:shadow-lg transition-shadow duration-300">
                <div class="text-sm text-gray-500 font-medium">Pending Orders</div>
                <div class="text-4xl font-bold text-orange-600 mt-2">
                    {{ $pendingOrdersCount > 0 ? $pendingOrdersCount : '0' }}
                </div>
                <p class="text-xs text-gray-400 mt-2">Regular and custom orders not delivered</p>
            </div>

        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold text-gray-700">Monthly Order Trend</h2>
                <form method="GET" action="{{ route('user.dashboard') }}">
                    <select name="year" onchange="this.form.submit()" class="border rounded-md py-1 px-2 text-sm">
                        @php
                            $currentYear = \Carbon\Carbon::now()->year;
                            $earliestYear = 2020;
                        @endphp
                        @for ($y = $currentYear; $y >= $earliestYear; $y--)
                            <option value="{{ $y }}" @if($y == ($year ?? $currentYear)) selected @endif>{{ $y }}</option>
                        @endfor
                    </select>
                </form>
            </div>
            <div class="relative h-96">
                <canvas id="schoolSalesTrendChart"></canvas>
            </div>
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
                        @php
                            $status = ucfirst($order->status);
                            $statusColor = 'bg-gray-100 text-gray-800';
                            switch($status) {
                                case 'New':
                                case 'To be qouted':
                                    $statusColor = 'bg-red-100 text-red-800';
                                    break;
                                case 'To be delivered':
                                case 'Approved':
                                case 'Gathering':
                                case 'Delivering':
                                    $statusColor = 'bg-yellow-100 text-yellow-800';
                                    break;
                                case 'Delivered':
                                    $statusColor = 'bg-green-100 text-green-800';
                                    break;
                            }
                        @endphp
                        <span class="inline-block px-2 py-1 rounded-full text-xs font-medium {{ $statusColor }}">
                            {{ $status }}
                        </span>
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
                    backgroundColor: 'rgba(37, 99, 235, 0.7)',
                    borderWidth: 1,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        stepSize: 1
                    }
                }
            }
        });
    } else if (ctx) {
        ctx.parentNode.innerHTML = '<div class="text-gray-500 text-sm text-center py-10">No sales trend data available</div>';
    }
</script>
</x-profile-link>