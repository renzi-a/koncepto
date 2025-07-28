<x-profile-link>
    <div class="container mx-auto px-4 py-6 space-y-6" x-data="orderDelete()">
        <h1 class="text-3xl font-bold text-gray-800">Order History</h1>

        <form method="POST" action="{{ route('user.orders.bulkDelete') }}" id="bulkDeleteForm" @submit.prevent="confirmDelete()">
            @csrf
            @method('DELETE')

            <div class="mb-4 flex items-center space-x-4">
                <label class="inline-flex items-center cursor-pointer">
                    <input type="checkbox" id="selectAll" class="form-checkbox h-5 w-5 text-blue-600" @change="toggleAll($event)">
                    <span class="ml-2 select-none">Select All</span>
                </label>

                <button type="submit"
                    :disabled="!hasSelected || loading"
                    class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded disabled:opacity-50 flex items-center space-x-2"
                >
                    <span x-show="!loading">Delete</span>
                    <svg x-show="loading" class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                    </svg>
                </button>
            </div>

            <div class="bg-white p-4 shadow rounded-lg">
                <div class="mt-4">
                    {{ $orders->links() }}
                </div>

                @if($orders->count())
                    <ul class="space-y-2">
                        @foreach ($orders as $order)
                            <li class="border rounded p-4 flex justify-between items-center
                                {{ ($order->order_status === 'Cancelled' || ($order->status ?? null) === 'cancelled') ? 'bg-red-50' : 'bg-gray-50' }}">
                                <div class="flex items-center space-x-4">
                                    <input type="checkbox" name="orders[]" value="{{ $order->id }}" class="order-checkbox form-checkbox h-5 w-5 text-blue-600" @change="updateSelection()">
                                    <div>
                                        <p>
                                            <strong class="text-gray-900">
                                                @if ($order->type === 'custom')
                                                    Custom Order #{{ $order->id }}
                                                @else
                                                    Order #{{ $order->id }}
                                                @endif
                                            </strong>
                                        </p>
                                        <p class="text-sm text-gray-600">
                                            Status:
                                            @php
                                                $status = $order->order_status ?? ucfirst($order->status);
                                            @endphp
                                            <span class="{{ ($status === 'Cancelled') ? 'text-red-600' : 'text-green-600' }}">
                                                {{ $status }}
                                            </span>
                                        </p>
                                        <p class="text-sm text-gray-600">
                                            Date: {{ \Carbon\Carbon::parse($order->created_at)->format('M d, Y') }}
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-4">
                                    <a href="{{ $order->type === 'custom' 
                                            ? route('user.custom-orders.show', $order->id) 
                                            : route('user.order.show', $order->id) }}" 
                                        class="text-blue-600 hover:underline text-sm">
                                        View Details
                                    </a>
                                    <form method="POST" action="{{ route('user.orders.destroy', $order->id) }}" onsubmit="return confirm('Are you sure you want to delete this order?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                            class="text-red-600 hover:text-red-800 text-sm font-semibold">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-gray-500">No orders found.</p>
                @endif
            </div>
        </form>

        <div
            x-show="showModal"
            x-transition.opacity
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
            style="display: none;"
        >
            <div
                x-show="showModal"
                x-transition
                @click.away="showModal = false"
                class="bg-white rounded-lg shadow-lg max-w-md w-full p-6"
                style="display: none;"
            >
                <h2 class="text-xl font-semibold mb-4">Confirm Delete</h2>
                <p class="mb-6">Are you sure you want to delete the selected orders? This action cannot be undone.</p>
                <div class="flex justify-end space-x-4">
                    <button
                        @click="showModal = false"
                        class="px-4 py-2 rounded bg-gray-300 hover:bg-gray-400"
                        :disabled="loading"
                    >
                        Cancel
                    </button>
                    <button
                        @click="submitDelete()"
                        class="px-4 py-2 rounded bg-red-600 text-white hover:bg-red-700 flex items-center space-x-2"
                        :disabled="loading"
                    >
                        <span x-show="!loading">Delete</span>
                        <svg x-show="loading" class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <div
            x-show="showSuccess"
            x-transition
            class="fixed bottom-4 right-4 bg-green-500 text-white px-4 py-3 rounded shadow-lg z-50"
            style="display: none;"
        >
            Orders deleted successfully!
        </div>
    </div>

    <script>
        function orderDelete() {
            return {
                showModal: false,
                showSuccess: false,
                loading: false,
                hasSelected: false,

                toggleAll(event) {
                    const checked = event.target.checked;
                    document.querySelectorAll('.order-checkbox').forEach(chk => chk.checked = checked);
                    this.hasSelected = checked;
                },

                updateSelection() {
                    const checkboxes = document.querySelectorAll('.order-checkbox');
                    this.hasSelected = Array.from(checkboxes).some(chk => chk.checked);
                    const allChecked = Array.from(checkboxes).every(chk => chk.checked);
                    document.getElementById('selectAll').checked = allChecked;
                },

                confirmDelete() {
                    if (!this.hasSelected) return;
                    this.showModal = true;
                },

                submitDelete() {
                    this.loading = true;
                    this.showSuccess = false;

                    document.getElementById('bulkDeleteForm').submit();
                }
            }
        }
    </script>
</x-profile-link>