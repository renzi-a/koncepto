<x-profile-link>
    <div class="container mx-auto px-4 py-6 space-y-8">

        <div class="flex justify-between items-center mb-4">
            <h1 class="text-3xl font-bold text-gray-800">School Dashboard</h1>
            <a href="{{ route('user.custom-order') }}"
            class="inline-flex items-center px-4 py-2 bg-[#56AB2F] text-white font-semibold rounded-md shadow-sm hover:bg-green-700 transition">
                + Place New Order
            </a>
        </div>


        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-sm text-gray-500">Total Orders</div>
                <div class="text-3xl font-bold mt-2 text-blue-600">
                    {{ $totalOrders > 0 ? $totalOrders : '0' }}

                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-sm text-gray-500">Pending Deliveries</div>
                <div class="text-3xl font-bold mt-2 text-yellow-500">
                    {{ $pendingOrders > 0 ? $pendingOrders : '0' }}
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-sm text-gray-500">Delivered Orders</div>
                <div class="text-3xl font-bold mt-2 text-green-600">
                    {{ $deliveredOrders > 0 ? $deliveredOrders : '0' }}
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold text-gray-700 mb-4">Monthly Order Trend</h2>
                <canvas id="schoolSalesTrendChart" class="w-full h-[400px]"></canvas>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 overflow-x-auto">
            <h2 class="text-xl font-semibold text-gray-700 mb-4">Recent Orders</h2>
            <table class="min-w-full text-sm text-left">
                <thead>
                    <tr class="text-gray-700 border-b">
                        <th class="py-2 px-4">Order ID</th>
                        <th class="py-2 px-4">Date</th>
                        <th class="py-2 px-4">Status</th>
                        <th class="py-2 px-4">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentOrders as $order)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-2 px-4">#{{ $order->id }}</td>
                            <td class="py-2 px-4">{{ $order->created_at->format('M d, Y') }}</td>
                            <td class="py-2 px-4">
                                <span class="inline-block px-2 py-1 rounded-full text-xs {{ $order->status === 'delivered' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </td>
                            <td class="py-2 px-4">₱{{ number_format($order->total, 2) }}</td>
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

    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

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
