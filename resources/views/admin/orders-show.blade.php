<x-layout>
    <div class="container mx-auto px-6 py-8 space-y-6">

        <!-- Header and Back Button -->
        <div class="flex items-center justify-between mb-4">
            <h1 class="text-2xl font-bold text-gray-800">
                Order {{ $order->order_code ?? 'N/A' }}
            </h1>
            <a href="{{ route('admin.orders') }}"
               class="text-sm text-blue-600 hover:underline">
                ← Back to orders
            </a>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-lg">
            <!-- Customer & Shipping Information -->
            <div class="flex items-start justify-between mb-6">
                <div class="flex items-center gap-4">
                    @if($order->user && $order->user->school && $order->user->school->image)
                        <img src="{{ asset('storage/' . $order->user->school->image) }}"
                             alt="School Logo"
                             class="w-16 h-16 object-cover rounded-full border border-gray-200 shadow-sm">
                    @else
                        <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center text-gray-400 text-xs font-medium border border-gray-200">
                            No Logo
                        </div>
                    @endif

                    <div>
                        <p class="text-sm font-semibold text-gray-800">
                            School: <span class="font-normal">{{ $order->user->school->school_name ?? 'N/A' }}</span>
                        </p>
                        <p class="text-sm font-semibold text-gray-800">
                            School Admin: <span class="font-normal">{{ $order->user->first_name ?? 'N/A' }} {{ $order->user->last_name ?? '' }}</span>
                        </p>
                        <p class="text-sm font-semibold text-gray-800">
                            Phone No: <span class="font-normal">{{ $order->user->cp_no ?? 'N/A' }}</span>
                        </p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-xs text-gray-500">
                        Ordered: <span class="font-medium text-gray-700">{{ $order->created_at->format('M d, Y h:i A') }}</span>
                    </p>
                    <div class="mt-2">
                        <!-- Status Badge -->
                        @php
                            $statusClass = '';
                            switch(strtolower($order->status)) {
                                case 'pending':
                                    $statusClass = 'bg-gray-200 text-gray-800';
                                    break;
                                case 'processing':
                                    $statusClass = 'bg-blue-200 text-blue-800';
                                    break;
                                case 'to be delivered':
                                    $statusClass = 'bg-purple-200 text-purple-800';
                                    break;
                                case 'delivered':
                                    $statusClass = 'bg-green-200 text-green-800';
                                    break;
                                case 'cancelled':
                                    $statusClass = 'bg-red-200 text-red-800';
                                    break;
                                default:
                                    $statusClass = 'bg-gray-200 text-gray-800';
                                    break;
                            }
                        @endphp
                        <span class="inline-block text-xs px-2 py-0.5 rounded-full font-semibold {{ $statusClass }}">
                            {{ ucfirst($order->status) }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Shipping Details Box -->
            <div class="bg-gray-50 p-4 rounded-lg shadow-inner mb-6 border border-gray-200">
                <h2 class="font-semibold text-sm text-gray-700 mb-1">Shipping Address</h2>
                <p class="text-sm text-gray-600">{{ $order->user->school->address ?? 'N/A' }}</p>
            </div>

            <!-- Order Items Table -->
            <div class="overflow-x-auto mt-4">
                <table class="min-w-full table-auto border text-sm text-gray-700 rounded-lg overflow-hidden">
                    <thead class="bg-gray-100 text-left">
                        <tr>
                            <th class="px-4 py-2">Item No.</th>
                            <th class="px-4 py-2">Name</th>
                            <th class="px-4 py-2">Unit</th>
                            <th class="px-4 py-2">Quantity</th>
                            <th class="px-4 py-2">Price</th>
                            <th class="px-4 py-2">Total Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $grandTotal = 0; @endphp
                        @forelse ($order->orderDetails as $index => $detail)
                            @php
                                $price = $detail->price ?? 0;
                                $quantity = $detail->quantity ?? 0;
                                $total = $price * $quantity;
                                $grandTotal += $total;
                            @endphp
                            <tr class="border-t">
                                <td class="px-4 py-2">{{ $index + 1 }}</td>
                                <td class="px-4 py-2">{{ $detail->product->productName ?? 'Unknown' }}</td>
                                <td class="px-4 py-2">{{ $detail->product->unit ?? '-' }}</td>
                                <td class="px-4 py-2">{{ $detail->quantity }}</td>
                                <td class="px-4 py-2">₱{{ number_format($price, 2) }}</td>
                                <td class="px-4 py-2">₱{{ number_format($total, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-4 text-center text-gray-500">No items found for this order.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-gray-100 font-semibold border-t">
                        <tr>
                            <td colspan="5" class="px-4 py-2 text-right">Grand Total:</td>
                            <td class="px-4 py-2 text-green-700">₱{{ number_format($grandTotal, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Additional Actions (e.g., Print) -->
            <div class="mt-6 flex justify-end">
                <a href="{{ route('admin.orders') }}"
                   class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 shadow-md transition-colors">
                    Back to Orders List
                </a>
            </div>
        </div>
    </div>
</x-layout>