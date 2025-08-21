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
    .alert-container {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 1000;
        max-width: 300px;
    }
    .alert-item-red {
        background-color: #fef2f2;
        border-color: #ef4444;
        color: #b91c1c;
    }
    .alert-item-yellow {
        background-color: #fffbeb;
        border-color: #f59e0b;
        color: #92400e;
    }
    .highlight-red {
        background-color: #fef2f2;
        color: #b91c1c;
    }
    .highlight-yellow {
        background-color: #fffbeb;
        color: #92400e;
    }
    .dismissible-alert {
        transition: transform 0.5s ease-in-out, opacity 0.5s ease-in-out;
    }
    .dismissible-alert.closing {
        transform: translateX(110%);
        opacity: 0;
    }
    </style>

    <div class="container mx-auto px-4 py-6 space-y-8">

        @if ($lowStockProducts->count() > 0)
        <div class="alert-container space-y-2">
            @if ($lowStockProducts->count() === 1)
                @php
                    $product = $lowStockProducts->first();
                    $alertClass = $product->quantity <= 5 ? 'alert-item-red' : 'alert-item-yellow';
                    $alertHover = $product->quantity <= 5 ? 'hover:bg-red-100' : 'hover:bg-yellow-100';
                    $svgColor = $product->quantity <= 5 ? 'text-red-500' : 'text-yellow-500';
                    $notice = $product->quantity <= 5 ? 'Please replenish immediately.' : 'Please replenish soon.';
                @endphp
                <div class="relative {{ $alertClass }} p-4 rounded-lg shadow-md border-l-4 transition {{ $alertHover }} dismissible-alert cursor-pointer" data-scroll-to="#product-table-container">
                    <button class="absolute top-2 right-2 text-gray-400 hover:text-gray-600 focus:outline-none close-btn">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                    <div class="flex items-center">
                        <svg class="h-6 w-6 {{ $svgColor }} mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <span class="font-bold">Low Stock Notice</span>
                    </div>
                    <p class="mt-1 text-sm">{{ $product->productName }} has a quantity of {{ $product->quantity }}. {{ $notice }}</p>
                </div>
            @else
                <div class="relative alert-item-yellow p-4 rounded-lg shadow-md border-l-4 dismissible-alert cursor-pointer" data-scroll-to="#product-table-container">
                    <button class="absolute top-2 right-2 text-gray-400 hover:text-gray-600 focus:outline-none close-btn">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                    <div class="flex items-center">
                        <svg class="h-6 w-6 text-yellow-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <span class="font-bold">Low Stock Notice</span>
                    </div>
                    <p class="mt-1 text-sm">{{ $lowStockProducts->first()->productName }} and {{ $lowStockProducts->count() - 1 }} other items have low stock. Check the table below for details.</p>
                </div>
            @endif
        </div>
        @endif

        <div class="flex justify-between items-center mb-4">
            <h1 class="text-3xl font-bold text-gray-800">
                @if ($selectedYear)
                    {{ $selectedYear }} Sales Dashboard
                @else
                    All Years Sales Dashboard
                @endif
            </h1>
        </div>

        <form method="GET" action="{{ route('admin.dashboard') }}" class="bg-white rounded-lg shadow p-4 flex flex-wrap gap-4 items-end">
            <div>
                <label for="year" class="block text-sm font-medium text-gray-700">Year</label>
                <select id="year" name="year" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    <option value="" {{ request('year') == '' ? 'selected' : '' }}>-- All Years --</option>
                    @foreach (range(date('Y'), 2020) as $year)
                        <option value="{{ $year }}" {{ request('year', '2025') == $year ? 'selected' : '' }}>{{ $year }}</option>
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
        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex justify-between items-center text-sm text-gray-500">
                    <span>Total Sales ({{ $selectedYear ? $selectedYear : 'All Years' }})</span>
                    <span class="{{ $salesChange >= 0 ? 'text-green-600' : 'text-red-600' }} font-semibold text-xs">
                        {{ $salesChange >= 0 ? '+' : '' }}{{ number_format($salesChange ?? 0, 2) }}%
                    </span>
                </div>
                <div class="text-3xl font-bold mt-2 text-gray-800">
                    ₱{{ number_format($totalSales ?? 0, 2) }}
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex justify-between items-center text-sm text-gray-500">
                    <span>Monthly Sales Change</span>
                    <span class="{{ $monthlySalesChange >= 0 ? 'text-green-600' : 'text-red-600' }} font-semibold text-xs">
                        {{ $monthlySalesChange >= 0 ? '+' : '' }}{{ number_format($monthlySalesChange ?? 0, 2) }}%
                    </span>
                </div>
                <div class="text-3xl font-bold mt-2 text-gray-800">
                    @if(isset($salesTrendData))
                        @if($selectedYear)
                            ₱{{ number_format($salesTrendData[Carbon\Carbon::now()->month - 1] ?? 0, 2) }}
                        @else
                            ₱N/A
                        @endif
                    @else
                        ₱0.00
                    @endif
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex justify-between items-center text-sm text-gray-500">
                    <span>Pending Orders ({{ $selectedYear ? $selectedYear : 'All Years' }})</span>
                    <span class="{{ $pendingChange >= 0 ? 'text-red-600' : 'text-green-600' }} font-semibold text-xs">
                        {{ $pendingChange >= 0 ? '+' : '' }}{{ number_format($pendingChange ?? 0, 2) }}%
                    </span>
                </div>
                <div class="text-3xl font-bold mt-2 text-yellow-500">
                    {{ ($pendingOrders ?? 0) + ($customPending ?? 0) }}
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex justify-between items-center text-sm text-gray-500">
                    <span>Completed Orders ({{ $selectedYear ? $selectedYear : 'All Years' }})</span>
                    <span class="{{ $completedChange >= 0 ? 'text-green-600' : 'text-red-600' }} font-semibold text-xs">
                        {{ $completedChange >= 0 ? '+' : '' }}{{ number_format($completedChange ?? 0, 2) }}%
                    </span>
                </div>
                <div class="text-3xl font-bold mt-2 text-green-600">
                    {{ ($completedOrders ?? 0) + ($customCompleted ?? 0) }}
                </div>
            </div>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="bg-white rounded-lg shadow p-6 lg:col-span-2">
                <h2 class="text-xl font-semibold text-gray-700 mb-4">Sales Trend ({{ $selectedYear ? $selectedYear : 'All Years' }})</h2>
                <canvas id="salesTrendChart" class="w-full max-h-72"></canvas>
            </div>

            <div class="bg-white rounded-lg shadow p-6 lg:col-span-1">
                <h2 class="text-xl font-semibold text-gray-700 mb-4">Orders Trend ({{ $selectedYear ? $selectedYear : 'All Years' }})</h2>
                <canvas id="ordersTrendChart" class="w-full max-h-72"></canvas>
            </div>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold text-gray-700 mb-4">Top 10 Products by Volume ({{ $selectedYear ? $selectedYear : 'All Years' }})</h2>
                <canvas id="topProductsChart" class="w-full max-h-72 mt-6"></canvas>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold text-gray-700 mb-4">Top Products List ({{ $selectedYear ? $selectedYear : 'All Years' }})</h2>
                <div class="h-[300px] overflow-y-auto">
                    <table class="w-full text-sm text-left">
                        <thead>
                            <tr class="text-gray-700 border-b">
                                <th class="py-2 px-4">#</th>
                                <th class="py-2 px-4">Product</th>
                                <th class="py-2 px-4 text-right">Units Sold</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topProducts as $index => $product)
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="py-2 px-4">{{ $index + 1 }}</td>
                                    <td class="py-2 px-4">{{ $product->product_name }}</td>
                                    <td class="py-2 px-4 text-right">{{ number_format($product->total) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-gray-500 py-4">No top products available</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold text-gray-700 mb-4">Sales by School ({{ $selectedYear ? $selectedYear : 'All Years' }})</h2>
            <div id="nasugbuMap" class="w-full h-[500px] rounded-md"></div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 overflow-x-auto">
            <h2 class="text-xl font-semibold text-gray-700 mb-4">Products</h2>
            <div id="product-table-container">
                @include('admin.partials.product-table-content', ['products' => $products])
            </div>
        </div>
    </div>
</x-layout>

<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const mapEl = document.getElementById('nasugbuMap');
    if (mapEl) {
        const map = L.map(mapEl, {
            zoomControl: false, 
            scrollWheelZoom: false,
            doubleClickZoom: false,
            dragging: false,
            boxZoom: false,
            touchZoom: false,
            keyboard: false
        }).setView([14.0940, 120.6890], 12);  

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        const schools = @json($schoolSales);
        schools.forEach(school => {
            if (!school.lat || !school.lng) return;

            const popupContent = `
                <div class="text-sm">
                    <strong>${school.school_name}</strong><br>
                    🧾 Orders: ${school.total_orders}<br>
                    💰 Sales: ₱${parseFloat(school.total_sales).toLocaleString(undefined, { minimumFractionDigits: 2 })}
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
    const ordersData = @json($monthlyOrders);
    const topLabels = @json($topProductsLabels);
    const topData = @json($topProductsData);

    const salesCtx = document.getElementById('salesTrendChart')?.getContext('2d');
    const ordersCtx = document.getElementById('ordersTrendChart')?.getContext('2d');
    const topCtx = document.getElementById('topProductsChart')?.getContext('2d');

    if (salesCtx && salesLabels?.length && salesData?.length) {
        new Chart(salesCtx, {
            type: 'line',
            data: {
                labels: salesLabels,
                datasets: [{
                    label: 'Sales',
                    data: salesData,
                    borderColor: '#3B82F6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    }

    if (ordersCtx && salesLabels?.length && ordersData?.length) {
        new Chart(ordersCtx, {
            type: 'bar',
            data: {
                labels: salesLabels,
                datasets: [{
                    label: 'Orders',
                    data: ordersData,
                    backgroundColor: '#10B981'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    }

    if (topCtx && topLabels?.length && topData?.length) {
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
                maintainAspectRatio: false,
                indexAxis: 'y'
            }
        });
    }

    const productTableContainer = document.getElementById('product-table-container');

    productTableContainer.addEventListener('click', function(e) {
        if (e.target.closest('.pagination a')) {
            e.preventDefault();
            const url = e.target.closest('.pagination a').href;
            const tableSection = document.getElementById('product-table-container');

            if (url) {
                fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.text())
                .then(html => {
                    tableSection.innerHTML = html;
                    tableSection.scrollIntoView({ behavior: 'smooth' });
                })
                .catch(error => console.error('Error fetching pagination:', error));
            }
        }
    });

    document.querySelectorAll('.close-btn').forEach(button => {
        button.addEventListener('click', function() {
            const alert = this.closest('.dismissible-alert');
            alert.classList.add('closing');

            alert.addEventListener('transitionend', () => {
                alert.remove();
            }, { once: true });
        });
    });
    
    document.querySelectorAll('[data-scroll-to]').forEach(element => {
        element.addEventListener('click', function(e) {
            if (e.target.closest('.close-btn')) {
                return;
            }
            
            const targetId = this.getAttribute('data-scroll-to');
            const targetElement = document.querySelector(targetId);

            if (targetElement) {
                targetElement.scrollIntoView({
                    behavior: 'smooth'
                });
            }
        });
    });
});
</script>