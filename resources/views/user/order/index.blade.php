<x-profile-link>
    <div class="container mx-auto px-4 py-6 space-y-6" 
        x-data="{ 
            showCancelModal: false, 
            isLoading: false, 
            cancelOrderId: null, 
            cancelOrderType: '', 
            reasonText: '' 
        }"
    >
        <div class="flex justify-between items-center">
            <h1 class="text-3xl font-bold text-gray-800">My Order</h1>
            <a href="{{ route('user.custom-order') }}"
               class="inline-block bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded">
               Create Custom Order
            </a>
        </div>

        @foreach ($normalOrders as $order)
            <div class="bg-blue-50 border-l-4 border-blue-400 rounded-xl shadow p-4 mb-4">
                <div class="flex justify-between items-stretch mb-2">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-700">Order #{{ $order->id }}</h2>
                        <p class="text-gray-600 mt-1">Items: {{ $order->items->count() }}</p>
                    </div>

                    <div class="flex flex-col justify-center items-end">
                        <div class="flex space-x-2 mb-4">
                            <span class="inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full">
                                Normal
                            </span>
                            <span class="inline-block bg-gray-100 text-gray-800 text-xs px-2 py-1 rounded-full">
                                {{ ucfirst($order->status) }}
                            </span>
                        </div>

                        <div class="flex flex-col space-y-2 text-xs">
                            @if (!in_array($order->status, ['to be delivered', 'delivered']))
                                <button 
                                    @click="cancelOrderId = {{ $order->id }}; cancelOrderType = 'normal'; reasonText = ''; showCancelModal = true"
                                    class="text-red-600 hover:underline"
                                >
                                    Cancel Order
                                </button>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="flex justify-between items-center mt-4 text-sm">
                    <a href="{{ route('user.normal-orders.show', $order->id) }}" class="text-blue-500 hover:underline text-sm">
                        View Details
                    </a>
                    <span class="text-gray-500">{{ $order->created_at->format('M d, Y') }}</span>
                </div>
            </div>
        @endforeach

        @foreach ($customOrders as $order)
            <div class="bg-yellow-50 border-l-4 border-yellow-400 rounded-xl shadow p-4 mb-4">
                <div class="flex justify-between items-stretch mb-2">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-700">Order #C{{ $order->id }}</h2>
                        <p class="text-gray-600 mt-1">Items: {{ $order->items_count }}</p>
                    </div>

                    <div class="flex flex-col justify-center items-end">
                        <div class="flex space-x-2 mb-4">
                            <span class="inline-block bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded-full">
                                Custom
                            </span>
                            <span class="inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full">
                                {{ ucfirst($order->status) }}
                            </span>
                        </div>
                        
                        <div class="flex flex-col space-y-2 text-xs">
                            @if ($order->status === 'to be quoted')
                                <a 
                                    href="{{ route('custom-orders.edit', $order->id) }}" 
                                    class="text-green-600 hover:underline"
                                >
                                    Edit Order
                                </a>
                            @endif
                            
                            @if (in_array($order->status, ['to be quoted', 'quoted']))
                                <button 
                                    @click="cancelOrderId = {{ $order->id }}; cancelOrderType = 'custom'; reasonText = ''; showCancelModal = true"
                                    class="text-red-600 hover:underline"
                                >
                                    Cancel Order
                                </button>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="flex justify-between items-center mt-4 text-sm">
                    <a href="{{ route('user.custom-orders.show', $order->id) }}" class="text-blue-500 hover:underline text-sm">
                        View Details
                    </a>
                    <span class="text-gray-500">{{ $order->created_at->format('M d, Y') }}</span>
                </div>
            </div>
        @endforeach

        <div 
            x-show="showCancelModal" 
            class="fixed inset-0 bg-black/40 flex items-center justify-center z-50"
            x-transition
        >
            <div class="bg-white rounded-xl p-6 w-full max-w-md shadow" @click.away="showCancelModal = false">
                <h2 class="text-lg font-semibold text-gray-800 mb-2">Cancel Order</h2>
                <p class="text-gray-600 mb-4">Please provide a reason for cancelling this order.</p>

                <form 
                    method="POST" 
                    :action="cancelOrderType === 'custom' 
                        ? `/user/custom-orders/${cancelOrderId}/cancel` 
                        : `/user/normal-orders/${cancelOrderId}/cancel`"
                    @submit.prevent="isLoading = true; $event.target.submit()"
                >
                    @csrf

                    <div class="mb-4">
                        <label for="reason" class="block text-sm font-medium text-gray-700 mb-1">
                            Reason
                        </label>
                        <textarea 
                            id="reason"
                            name="reason"
                            x-model="reasonText"
                            required
                            class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500"
                            rows="3"
                        ></textarea>
                    </div>

                    <input type="hidden" name="reason" :value="reasonText">

                    <div class="flex justify-end space-x-2">
                        <button type="button" @click="showCancelModal = false" class="text-gray-500 hover:underline">
                            Close
                        </button>
                        <button type="submit" 
                            class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700"
                            x-bind:disabled="isLoading"
                        >
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
</x-profile-link>
