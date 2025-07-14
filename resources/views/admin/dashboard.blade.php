<x-layout>
    <style>
    .leaflet-tooltip.custom-tooltip {
        background-color: white;
        color: #333;
        border: 1px solid #ccc;
        border-radius: 6px;
        padding: 6px 10px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        font-size: 0.85rem;
    }
</style>

<div class="container mx-auto px-4 py-6 space-y-8">

    <div class="flex justify-between items-center mb-4">
        <h1 class="text-3xl font-bold text-gray-800">Dashboard</h1>
        <button
            id="reloadDashboardBtn"
            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white font-semibold rounded-md shadow-sm hover:bg-blue-700 transition">
            Reload Dashboard
        </button>
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

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex justify-between items-center text-sm text-gray-500">
                <span>Revenue</span>
                <span class="{{ $revenueChange >= 0 ? 'text-green-600' : 'text-red-600' }} font-semibold text-xs">
                    {{ $revenueChange >= 0 ? '+' : '' }}{{ $revenueChange ?? '0.00' }}%
                </span>
            </div>
            <div class="text-3xl font-bold mt-2 text-gray-800">
                ₱{{ number_format($totalRevenue ?? 0, 2) }}
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex justify-between items-center text-sm text-gray-500">
                <span>Pending Orders</span>
                <span class="{{ $pendingChange >= 0 ? 'text-red-600' : 'text-green-600' }} font-semibold text-xs">
                    {{ $pendingChange >= 0 ? '+' : '' }}{{ $pendingChange ?? '0.00' }}%
                </span>
            </div>
            <div class="text-3xl font-bold mt-2 text-yellow-500">
                {{ $pendingOrders ?? 0 }}
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex justify-between items-center text-sm text-gray-500">
                <span>Completed Orders</span>
                <span class="{{ $completedChange >= 0 ? 'text-green-600' : 'text-red-600' }} font-semibold text-xs">
                    {{ $completedChange >= 0 ? '+' : '' }}{{ $completedChange ?? '0.00' }}%
                </span>
            </div>
            <div class="text-3xl font-bold mt-2 text-green-600">
                {{ $completedOrders ?? 0 }}
            </div>
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

    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold text-gray-700 mb-4">🗺️ Sales by School</h2>
        <div id="nasugbuMap" class="w-full h-[500px] rounded-md"></div>
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

<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const mapEl = document.getElementById('nasugbuMap');
    if (mapEl) {
        const map = L.map('nasugbuMap').setView([14.0940, 120.6890], 13);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        const schools = @json($salesBySchool);
        schools.forEach(school => {
            if (!school.lat || !school.lng) return;

            const popupContent = `
                <div class="text-sm">
                    <strong>${school.name}</strong><br>
                    🧾 Orders: ${school.total_orders}<br>
                    💰 Revenue: ₱${parseFloat(school.total_revenue).toLocaleString(undefined, { minimumFractionDigits: 2 })}
                </div>
            `;

            const schoolIcon = L.icon({
                iconUrl: '/images/pin.png',
                iconSize: [48, 48],
                iconAnchor: [16, 32],
                popupAnchor: [0, -32]
            });

            const marker = L.marker([school.lat, school.lng], { icon: schoolIcon }).addTo(map);
            marker.bindTooltip(popupContent, {
                direction: 'top',
                permanent: false,
                opacity: 0.9,
                className: 'custom-tooltip'
            });
            marker.on('mouseover', function () { this.openTooltip(); });
            marker.on('mouseout', function () { this.closeTooltip(); });
        });
    }

    const salesLabels = @json($salesTrendLabels);
    const salesData = @json($salesTrendData);
    const salesCtx = document.getElementById('salesTrendChart');

    if (salesCtx && salesLabels.length && salesData.length) {
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
    }

    const topLabels = @json($topProductsLabels);
    const topData = @json($topProductsData);
    const topCtx = document.getElementById('topProductsChart');

    if (topCtx && topLabels.length && topData.length) {
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
    }
});
</script>

<script>
document.getElementById('reloadDashboardBtn')?.addEventListener('click', () => {
    location.reload();
});
</script>

<script>
    window.addEventListener('DOMContentLoaded', () => {
        const reloadBtn = document.getElementById('reloadDashboardBtn');
        if (reloadBtn) {
            reloadBtn.addEventListener('click', () => {
                window.location.reload();
            });
        }
    });
</script>

