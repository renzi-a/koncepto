<x-profile-link>
    <div class="container mx-auto px-4 py-6 space-y-6">
        <div class="flex justify-between items-center mb-4">
            <h1 class="text-3xl font-bold text-gray-800">Quoted Orders</h1>
            <a href="{{ route('user.order.index') }}" class="text-blue-600 hover:underline">← Back to Orders</a>
        </div>

        @if(isset($orders))
            @forelse ($orders as $order)
                <li class="bg-white rounded border p-4 shadow">
                    <div class="flex justify-between items-center">
                        <div>
                            <h2 class="text-lg font-semibold">Custom Order #C{{ $order->id }}</h2>
                            <p class="text-sm text-gray-600">{{ $order->created_at->format('F d, Y h:i A') }}</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <a href="{{ route('user.custom-orders.show', $order->id) }}" class="text-sm text-blue-600 hover:underline">View</a>
                        </div>
                    </div>
                </li>
            @empty
                <p class="text-center text-gray-500">No quoted orders found.</p>
            @endforelse

       @elseif(isset($order) && isset($items))
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">Custom Order #C{{ $order->id }}</h1>
        <a href="{{ route('user.order.quoted.pdf', $order->id) }}" target="_blank"
   class="inline-block bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-2 rounded">
    Download PDF
</a>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full table-auto border text-sm text-gray-700">
            <thead class="bg-gray-100 text-left">
                <tr>
                    <th class="px-4 py-2">Item No.</th>
                    <th class="px-4 py-2">Name</th>
                    <th class="px-4 py-2">Brand</th>
                    <th class="px-4 py-2">Unit</th>
                    <th class="px-4 py-2">Quantity</th>
                    <th class="px-4 py-2">Photo</th>
                    <th class="px-4 py-2">Description</th>
                    <th class="px-4 py-2">Price</th>
                    <th class="px-4 py-2">Total Price</th>
                </tr>
            </thead>
            <tbody>
                @php $grandTotal = 0; @endphp
                @foreach ($items as $index => $item)
                    @php
                        $price = $item['price'] ?? 0;
                        $quantity = $item['quantity'] ?? 0;
                        $total = $price * $quantity;
                        $grandTotal += $total;
                    @endphp
                    <tr class="border-t">
                        <td class="px-4 py-2">{{ $index + 1 }}</td>
                        <td class="px-4 py-2">{{ $item['name'] }}</td>
                        <td class="px-4 py-2">{{ $item['brand'] ?? '-' }}</td>
                        <td class="px-4 py-2">{{ $item['unit'] ?? '-' }}</td>
                        <td class="px-4 py-2">{{ $item['quantity'] }}</td>
                        <td class="px-4 py-2">
                            @if (!empty($item['photo']))
                                <img src="{{ asset('storage/' . $item['photo']) }}" alt="Photo"
                                     class="w-12 h-12 object-cover rounded border">
                            @else
                                <span class="text-gray-400">No Image</span>
                            @endif
                        </td>
                        <td class="px-4 py-2">{{ $item['description'] ?? '-' }}</td>
                        <td class="px-4 py-2">
                            @if(isset($item['price']))
                                ₱{{ number_format($item['price'], 2) }}
                            @else
                                <span class="text-gray-400">Not yet set</span>
                            @endif
                        </td>
                        <td class="px-4 py-2">
                            @if(isset($item['price']))
                                ₱{{ number_format($total, 2) }}
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
                <tr class="bg-gray-100 font-semibold border-t">
                    <td colspan="8" class="px-4 py-2 text-right">Grand Total:</td>
                    <td class="px-4 py-2 text-green-700">₱{{ number_format($grandTotal, 2) }}</td>
                </tr>
            </tbody>
        </table>

    </div>

    @if ($order->status === 'quoted')
        <div x-data="{ showApproveModal: false, isLoading: false, agreed: false, payment_date: '' }">
    <button 
        @click="showApproveModal = true" 
        class="bg-green-600 text-white px-5 py-2 rounded hover:bg-green-700 mt-6"
    >
        Approve Quotation
    </button>

    <div 
        x-show="showApproveModal"
        class="fixed inset-0 bg-black/40 flex items-center justify-center z-50"
        x-transition
    >
        <div class="bg-white rounded-xl p-6 w-full max-w-md shadow" @click.away="showApproveModal = false">
            <h2 class="text-lg font-semibold text-gray-800 mb-3">Confirm Approval</h2>
            <p class="text-sm text-gray-700 mb-4">
                ⚠️ By approving this quotation, you agree that the order cannot be cancelled or modified afterward.
            </p>

            <label class="inline-flex items-start mb-4">
                <input type="checkbox" x-model="agreed" class="mt-1 mr-2">
                <span class="text-sm text-gray-700">I understand and agree to proceed with the approval.</span>
            </label>

            <form 
                action="{{ route('user.custom-orders.approve', $order->id) }}" 
                method="POST" 
                @submit.prevent="if (!payment_date) return; isLoading = true; $event.target.submit()" 
            >
                @csrf
                @method('PUT')

                @php
                    $today = \Carbon\Carbon::today()->toDateString();
                    $endNextMonth = \Carbon\Carbon::now()->addMonthNoOverflow()->endOfMonth()->toDateString();
                @endphp

                <div class="mb-4">
                    <label for="payment_date" class="block text-sm font-medium text-gray-700 mb-1">
                        Bank Check Date <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="date" 
                        name="payment_date" 
                        id="payment_date" 
                        x-model="payment_date"
                        required
                        min="{{ $today }}" 
                        max="{{ $endNextMonth }}"
                        class="border rounded px-3 py-2 w-full text-sm focus:ring-2 focus:ring-green-500"
                    >
                </div>

                <div class="flex justify-end space-x-2">
                    <button type="button" @click="showApproveModal = false" class="text-gray-600 hover:underline">
                        Cancel
                    </button>
                    <button 
                        type="submit" 
                        class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 disabled:opacity-50"
                        :disabled="!agreed || isLoading"
                    >
                        <span x-show="!isLoading">Yes, Approve</span>
                        <span x-show="isLoading" class="flex items-center space-x-1">
                            <svg class="animate-spin w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z" />
                            </svg>
                            Approving...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


@elseif ($order->status === 'approved')
 <p class="text-gray-600 italic">✅ You have approved this quotation.</p>
@endif
@endif
    </div>
</x-profile-link>
