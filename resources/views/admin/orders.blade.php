<x-layout>
    <div class="container mx-auto px-4 py-6 space-y-8">
        <div class="flex items-center justify-between mb-4">
            <h1 class="text-3xl font-bold text-gray-800">Order Management</h1>
        </div>

        <div class="mb-6 border-b border-gray-200">
            <ul class="flex space-x-6">
                <li><a href="?tab=all" class="{{ $tab == 'all' ? 'border-b-2 border-green-600 text-green-600' : 'text-gray-600 hover:text-green-600' }} font-semibold pb-2">All</a></li>
                <li><a href="?tab=orders" class="{{ $tab == 'orders' ? 'border-b-2 border-green-600 text-green-600' : 'text-gray-600 hover:text-green-600' }} font-semibold pb-2">Orders</a></li>
                <li><a href="?tab=custom" class="{{ $tab == 'custom' ? 'border-b-2 border-green-600 text-green-600' : 'text-gray-600 hover:text-green-600' }} font-semibold pb-2">Custom Orders</a></li>
            </ul>
        </div>

        <div class="mb-4 flex flex-wrap gap-3">
            @foreach(['All', 'New Orders', 'Gathering', 'To be Delivered', 'Delivered'] as $status)
                <button
                    class="px-4 py-1.5 rounded text-sm font-semibold
                        {{ request('status') == $status || (request('status') == null && $status == 'All') ? 'bg-green-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}"
                    onclick="window.location='?tab={{ $tab }}&status={{ urlencode($status) }}'">
                    {{ $status }}
                </button>
            @endforeach
        </div>

        <x-orders-table :orders="$orders" />
    </div>

    <script>
        function showTab(tab) {
            const contents = document.querySelectorAll('.tab-content');
            const links = document.querySelectorAll('.tab-link');

            contents.forEach(c => c.classList.add('hidden'));
            links.forEach(l => {
                l.classList.remove('text-green-600', 'border-green-600', 'border-b-2');
                l.classList.add('text-gray-600');
            });

            document.getElementById(tab).classList.remove('hidden');
            document.querySelector(`a[href="#${tab}"]`).classList.add('text-green-600', 'border-b-2', 'border-green-600');
        }
    </script>
</x-layout>
