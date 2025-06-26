<x-layout />
<div class="container mx-auto px-4 py-6">
    
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Dashboard</h1>
    </div>

<form method="GET" action="{{ route('admin.dashboard') }}" class="mb-6 flex flex-wrap gap-4 items-end">
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


<div class="p-6 space-y-6">
    <div class="bg-white rounded-lg shadow p-8 grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="text-center">
            <h2 class="text-base text-gray-500">Pending Orders</h2>
            <p class="text-4xl font-bold text-yellow-600">{{ $pendingOrders ?? 0 }}</p>
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
        <div class="bg-white rounded-lg shadow p-8">
            <h2 class="text-xl font-semibold text-gray-700 mb-6">Sales Trend</h2>
            <canvas id="salesTrendChart" class="w-full h-[400px]"></canvas>
        </div>

        <div class="bg-white rounded-lg shadow p-8">
            <h2 class="text-xl font-semibold text-gray-700 mb-6">Top 10 Products</h2>
            <canvas id="topProductsChart" class="w-full h-[400px]"></canvas>
        </div>
    </div>
</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const salesLabels = @json($salesTrendLabels);
    const salesData = @json($salesTrendData);
    const topLabels = @json($topProductsLabels);
    const topData = @json($topProductsData);

    new Chart(document.getElementById('salesTrendChart'), {
        type: 'line',
        data: {
            labels: salesLabels.length ? salesLabels : ['No Data'],
            datasets: [{
                label: 'Sales',
                data: salesData.length ? salesData : [0],
                borderColor: '#3B82F6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                fill: true,
                tension: 0.4
            }]
        }
    });

    new Chart(document.getElementById('topProductsChart'), {
        type: 'bar',
        data: {
            labels: topLabels.length ? topLabels : ['No Data'],
            datasets: [{
                label: 'Units Sold',
                data: topData.length ? topData : [0],
                backgroundColor: '#10B981'
            }]
        }
    });
</script>
