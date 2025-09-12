<x-profile-link>
    <div class="container mx-auto px-4 py-8"
        x-data="() => ({
            showCancelModal: false,
            isLoading: false,
            cancelOrderId: null,
            cancelOrderType: '',
            reasonText: '',
            activeTab: 'all',
            init() {
                // Ensure modal never shows on page load
                this.showCancelModal = false;
                this.cancelOrderId = null;
                this.cancelOrderType = '';
                this.reasonText = '';
            }
        })"
        x-init="init()"
    >
        <div class="flex items-center justify-between mb-8 pb-4 border-b border-gray-200">
            <h1 class="text-3xl font-bold text-gray-800">My Orders</h1>
            <a href="{{ route('user.custom-order') }}"
               class="px-4 py-2 rounded-lg text-sm font-medium transition-all duration-300 bg-green-600 text-white hover:bg-green-700 shadow-md transform hover:scale-105">
                Create Custom Order
            </a>
        </div>

        {{-- Year filter dropdown --}}
        <div class="flex items-center justify-end mb-6">
            <label for="year-filter" class="mr-2 text-sm font-medium text-gray-700">Filter by Year:</label>
            <select id="year-filter" onchange="window.location.href = this.value"
                class="block w-32 px-3 py-2 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500">
                @foreach ($availableYears as $year)
                    <option value="{{ route('user.order.index', ['year' => $year]) }}"
                        @if ($currentYear == $year) selected @endif>
                        {{ $year }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Tabs --}}
        <div class="mb-6 border-b border-gray-200">
            <nav class="flex space-x-4 text-sm font-medium -mb-px">
                <button @click="activeTab = 'all'"
                    :class="activeTab === 'all' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-gray-600'"
                    class="pb-2 px-1 font-semibold">
                    All ({{ $normalOrders->count() + $customOrders->count() }})
                </button>
                <button @click="activeTab = 'new'"
                    :class="activeTab === 'new' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-gray-600'"
                    class="pb-2 px-1 font-semibold">
                    New ({{
                        $normalOrders->where('status', 'new')->count() +
                        $customOrders->whereIn('status', ['to be quoted', 'quoted'])->count()
                    }})
                </button>
                <button @click="activeTab = 'pending'"
                    :class="activeTab === 'pending' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-gray-600'"
                    class="pb-2 px-1 font-semibold">
                    Pending ({{
                        $normalOrders->whereIn('status', ['processing', 'To be delivered'])->count() +
                        $customOrders->whereIn('status', ['approved', 'processing', 'To be delivered', 'delivering'])->count()
                    }})
                </button>
                <button @click="activeTab = 'completed'"
                    :class="activeTab === 'completed' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-gray-600'"
                    class="pb-2 px-1 font-semibold">
                    Completed ({{
                        $normalOrders->where('status', 'delivered')->count() +
                        $customOrders->where('status', 'delivered')->count()
                    }})
                </button>
            </nav>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            {{-- Normal Orders --}}
            @forelse ($normalOrders as $order)
                <div x-show="activeTab === 'all'
                    || (activeTab === 'new' && ['new'].includes('{{ $order->status }}'))
                    || (activeTab === 'pending' && ['processing', 'To be delivered'].includes('{{ $order->status }}'))
                    || (activeTab === 'completed' && '{{ $order->status }}' === 'delivered')"
                    class="bg-white rounded-2xl shadow-xl p-4 transition-all duration-300 hover:shadow-2xl hover:scale-105 border-l-8 border-blue-400">
                    <div class="flex flex-col h-full">
                        <div class="flex-grow">
                            <h2 class="text-lg font-bold text-gray-800 mb-1">Order #{{ $order->id }}</h2>
                            <div class="mt-2 flex items-center space-x-2 mb-2">
                                <span class="inline-block text-xs px-2 py-0.5 rounded-full font-semibold bg-blue-200 text-blue-800">
                                    Normal
                                </span>
                                @php
                                    $statusClass = '';
                                    switch(strtolower($order->status)) {
                                        case 'new': $statusClass = 'bg-red-200 text-red-800'; break;
                                        case 'processing': $statusClass = 'bg-blue-200 text-blue-800'; break;
                                        case 'to be delivered': $statusClass = 'bg-purple-200 text-purple-800'; break;
                                        case 'delivered': $statusClass = 'bg-green-200 text-green-800'; break;
                                        default: $statusClass = 'bg-gray-200 text-gray-800'; break;
                                    }
                                @endphp
                                <span class="inline-block text-xs px-2 py-0.5 rounded-full font-semibold {{ $statusClass }}">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </div>
                            <p class="text-gray-600 text-sm mt-1">Items: {{ $order->items->count() ?? '-' }}</p>
                            <p class="text-gray-500 text-xs mt-1">Created: {{ $order->created_at->format('M d, Y') }}</p>
                        </div>

                        <div class="mt-4 flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2 items-center justify-end">
                            <a href="{{ route('user.normal-orders.show', $order->id) }}"
                               class="w-full sm:w-auto text-center py-1.5 px-4 text-blue-600 font-semibold rounded-lg border border-blue-600 hover:bg-blue-600 hover:text-white transition">
                                View Details
                            </a>
                            @if (!in_array($order->status, ['To be delivered', 'delivered']))
                                <button
                                    @click="cancelOrderId = '{{ $order->id }}'; cancelOrderType = 'normal'; reasonText = ''; showCancelModal = true"
                                    class="w-full sm:w-auto text-center py-1.5 px-4 text-red-600 font-semibold rounded-lg border border-red-600 hover:bg-red-600 hover:text-white transition">
                                    Cancel Order
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div x-show="activeTab === 'all' || activeTab === 'new' || activeTab === 'pending' || activeTab === 'completed'" class="col-span-1 md:col-span-2 lg:col-span-3 bg-white rounded-2xl shadow-xl p-8 text-center text-gray-500">
                    <p>No normal orders found for this section.</p>
                </div>
            @endforelse

            {{-- Custom Orders --}}
            @forelse ($customOrders as $order)
                <div x-show="activeTab === 'all'
                    || (activeTab === 'new' && ['to be quoted', 'quoted'].includes('{{ $order->status }}'))
                    || (activeTab === 'pending' && ['approved', 'processing', 'To be delivered', 'Delivering'].includes('{{ $order->status }}'))
                    || (activeTab === 'completed' && '{{ $order->status }}' === 'delivered')"
                    class="bg-white rounded-2xl shadow-xl p-4 transition-all duration-300 hover:shadow-2xl hover:scale-105 border-l-8 border-yellow-400">
                    <div class="flex flex-col h-full">
                        <div class="flex-grow">
                            <h2 class="text-lg font-bold text-gray-800 mb-1">Order #C{{ $order->id }}</h2>
                            <div class="mt-2 flex items-center space-x-2 mb-2">
                                <span class="inline-block text-xs px-2 py-0.5 rounded-full font-semibold bg-yellow-200 text-yellow-800">
                                    Custom
                                </span>
                                @php
                                    $statusClass = '';
                                    switch(strtolower($order->status)) {
                                        case 'to be quoted':
                                        case 'quoted': $statusClass = 'bg-red-200 text-red-800'; break;
                                        case 'approved':
                                        case 'processing': $statusClass = 'bg-teal-200 text-teal-800'; break;
                                        case 'to be delivered': $statusClass = 'bg-purple-200 text-purple-800'; break;
                                        case 'delivered': $statusClass = 'bg-green-200 text-green-800'; break;
                                        default: $statusClass = 'bg-gray-200 text-gray-800'; break;
                                    }
                                @endphp
                                <span class="inline-block text-xs px-2 py-0.5 rounded-full font-semibold {{ $statusClass }}">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </div>
                            <p class="text-gray-600 text-sm mt-1">Items: {{ $order->items_count }}</p>
                            <p class="text-gray-500 text-xs mt-1">Created: {{ $order->created_at->format('M d, Y') }}</p>
                        </div>

                        <div class="mt-4 flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2 items-center justify-end">
                            <a href="{{ route('user.custom-orders.show', $order->id) }}"
                               class="w-full sm:w-auto text-center py-1.5 px-4 text-blue-600 font-semibold rounded-lg border border-blue-600 hover:bg-blue-600 hover:text-white transition">
                                View Details
                            </a>
                            @if ($order->status === 'to be quoted')
                                <a href="{{ route('custom-orders.edit', $order->id) }}"
                                   class="w-full sm:w-auto text-center py-1.5 px-4 text-green-600 font-semibold rounded-lg border border-green-600 hover:bg-green-600 hover:text-white transition">
                                    Edit Order
                                </a>
                            @endif
                            @if (in_array($order->status, ['to be quoted', 'quoted']))
                                <button
                                    @click="cancelOrderId = '{{ $order->id }}'; cancelOrderType = 'custom'; reasonText = ''; showCancelModal = true"
                                    class="w-full sm:w-auto text-center py-1.5 px-4 text-red-600 font-semibold rounded-lg border border-red-600 hover:bg-red-600 hover:text-white transition">
                                    Cancel Order
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div x-show="activeTab === 'all' || activeTab === 'new' || activeTab === 'pending' || activeTab === 'completed'" class="col-span-1 md:col-span-2 lg:col-span-3 bg-white rounded-2xl shadow-xl p-8 text-center text-gray-500">
                    <p>No custom orders found for this section.</p>
                </div>
            @endforelse

            {{-- Cancel Modal --}}
            <div
                x-show="showCancelModal"
                x-cloak
                class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
            >
                <div class="bg-white rounded-xl p-6 w-full max-w-md shadow-lg" @click.away="showCancelModal = false">
                    <h2 class="text-lg font-semibold text-gray-800 mb-2">Cancel Order</h2>
                    <p class="text-gray-600 mb-4">Please provide a reason for cancelling this order.</p>

                    <form
                        method="POST"
                        :action="cancelOrderType === 'custom'
                            ? `{{ url('user/custom-orders') }}/${cancelOrderId}/cancel`
                            : `{{ url('user/normal-orders') }}/${cancelOrderId}/cancel`"
                        @submit.prevent="isLoading = true; $event.target.submit()"
                    >
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <label for="reason" class="block text-sm font-medium text-gray-700 mb-1">Reason</label>
                            <textarea id="reason" name="reason" x-model="reasonText" required
                                class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500"
                                rows="3"></textarea>
                        </div>

                        <div class="flex justify-end space-x-2">
                            <button type="button" @click="showCancelModal = false" class="text-gray-500 hover:underline">
                                Close
                            </button>
                            <button type="submit"
                                class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 font-semibold transition-colors"
                                x-bind:disabled="isLoading">
                                <span x-show="!isLoading">Yes, Cancel</span>
                                <span x-show="isLoading" class="flex items-center space-x-1">
                                    <svg class="animate-spin w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                    </svg>
                                    Cancelling...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-profile-link>