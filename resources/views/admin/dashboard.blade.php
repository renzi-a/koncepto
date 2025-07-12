<x-layout>
<div class="container mx-auto px-4 py-6 space-y-8">

    <div class="flex justify-between items-center mb-4">
        <h1 class="text-3xl font-bold text-gray-800">Dashboard</h1>
    </div>

    <form method="GET" action="{{ route('admin.dashboard') }}" class="bg-white rounded-lg shadow p-4 flex flex-wrap gap-4 items-end">
        <div>
            <label for="year" class="block text-sm font-medium text-gray-700">Year</label>
            <select id="year" name="year" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                <option value="">-- Select Year --</option>
                @foreach (range(date('Y'), date('Y') - 3) as $year)
                    <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>{{ $year }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="quarter" class="block text-sm font-medium text-gray-700">Quarter</label>
            <select id="quarter" name="quarter" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                <option value="">-- All Quarters --</option>
                <option value="1" {{ request('quarter') == '1' ? 'selected' : '' }}>Q1 (Jan-Mar)</option>
                <option value="2" {{ request('quarter') == '2' ? 'selected' : '' }}>Q2 (Apr-Jun)</option>
                <option value="3" {{ request('quarter') == '3' ? 'selected' : '' }}>Q3 (Jul-Sep)</option>
                <option value="4" {{ request('quarter') == '4' ? 'selected' : '' }}>Q4 (Oct-Dec)</option>
            </select>
        </div>

        <div>
            <button type="submit"
                class="inline-flex items-center px-4 py-2 bg-[#56AB2F] text-white font-semibold rounded-md shadow-sm hover:bg-green-700 transition">
                Filter
            </button>
        </div>
    </form>

    <div class="bg-white rounded-lg shadow p-6 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
        <div class="text-center">
            <h2 class="text-base text-gray-500">Pending Orders</h2>
            <p class="text-4xl font-bold text-yellow-500">{{ $pendingOrders ?? 0 }}</p>
        </div>
        <div class="text-center">
            <h2 class="text-base text-gray-500">Completed Orders</h2>
            <p class="text-4xl font-bold text-green-600">{{ $completedOrders ?? 0 }}</p>
        </div>
        <div class="text-center">
            <h2 class="text-base text-gray-500">Total Revenue</h2>
            <p class="text-4xl font-bold text-blue-600">₱{{ number_format($totalRevenue ?? 0, 2) }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold text-gray-700 mb-4">📈 Sales Trend</h2>
            <canvas id="salesTrendChart" class="w-full h-[400px]"></canvas>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold text-gray-700 mb-4">🏆 Top 10 Products</h2>
            <canvas id="topProductsChart" class="w-full h-[400px]"></canvas>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-lg shadow p-6 overflow-x-auto">
            <h2 class="text-xl font-semibold text-gray-700 mb-4">🏫 Sales by School</h2>
            <table class="min-w-full text-sm text-left">
                <thead>
                    <tr class="text-gray-700 border-b">
                        <th class="py-2 px-4">School</th>
                        <th class="py-2 px-4">Total Orders</th>
                        <th class="py-2 px-4">Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($salesBySchool as $school)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-2 px-4">{{ $school->name }}</td>
                            <td class="py-2 px-4">{{ $school->total_orders }}</td>
                            <td class="py-2 px-4">₱{{ number_format($school->total_revenue, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-gray-500 py-4">No data available</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

   <div class="bg-white rounded-lg shadow p-6 overflow-x-auto">
        <h2 class="text-xl font-semibold text-gray-700 mb-4">Products</h2>
        <table class="min-w-full text-sm text-left">
            <thead>
                <tr class="text-gray-700 border-b">
                    <th class="py-2 px-4">Product</th>
                    <th class="py-2 px-4">Category</th>
                    <th class="py-2 px-4">Stock</th>
                    <th class="py-2 px-4">Price</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="py-2 px-4">{{ $product->productName }}</td>
                        <td class="py-2 px-4">{{ $product->category->categoryName ?? 'N/A' }}</td>
                        <td class="py-2 px-4">{{ $product->quantity }}</td>
                        <td class="py-2 px-4">₱{{ number_format($product->price, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-gray-500 py-4">No products available</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
</x-layout>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const salesLabels = @json($salesTrendLabels);
    const salesData = @json($salesTrendData);
    const topLabels = @json($topProductsLabels);
    const topData = @json($topProductsData);

    const salesCtx = document.getElementById('salesTrendChart');
    const topCtx = document.getElementById('topProductsChart');

    if (salesLabels.length && salesData.length) {
        new Chart(salesCtx, {
            type: 'line',
            data: {
                labels: salesLabels,
                datasets: [{
                    label: 'Sales',
                    data: salesData,
                    borderColor: '#3B82F6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    } else {
        salesCtx.remove();
        const message = document.createElement('p');
        message.className = "text-center text-gray-500 mt-10";
        message.innerText = "No sales trend data available.";
        salesCtx.parentNode.appendChild(message);
    }

    if (topLabels.length && topData.length) {
        new Chart(topCtx, {
            type: 'bar',
            data: {
                labels: topLabels,
                datasets: [{
                    label: 'Units Sold',
                    data: topData,
                    backgroundColor: '#10B981'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    } else {
        topCtx.remove();
        const message = document.createElement('p');
        message.className = "text-center text-gray-500 mt-10";
        message.innerText = "No top product data available.";
        topCtx.parentNode.appendChild(message);
    }
</script>
