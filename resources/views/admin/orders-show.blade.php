<x-layout>
    <div class="container mx-auto px-4 py-6 space-y-6">

        <div class="flex justify-between items-center mb-6">
            <div class="flex items-center space-x-4">
                <h1 class="text-3xl font-extrabold text-gray-900">Order #{{ $order->id }}</h1>
                <form action="{{ route('admin.orders.updateStatus') }}" method="POST">
                    @csrf
                    <input type="hidden" name="order_id" value="{{ $order->id }}">
                    <input type="hidden" name="type" value="normal">
                    <select name="status" onchange="this.form.submit()" class="rounded-md border-gray-300 text-sm font-medium">
                        @foreach(['pending', 'processing', 'completed', 'cancelled', 'to be delivered', 'delivered'] as $status)
                            <option value="{{ $status }}" {{ $order->status === $status ? 'selected' : '' }}>
                                {{ ucfirst($status) }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>
            <div class="text-right">
                <span class="block text-sm text-gray-600">Ordered: {{ $order->created_at->format('M d, Y') }}</span>
                <span class="block text-xs text-gray-400">Time: {{ $order->created_at->format('h:i A') }}</span>
            </div>
        </div>

        <div class="bg-white p-8 rounded-lg shadow-md space-y-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h2 class="font-semibold text-lg text-gray-700 mb-2">Customer & Contact</h2>
                    <p class="text-gray-600">
                        <strong>School Name: </strong> {{ $order->user->school->school_name ?? 'N/A' }}
                    </p>
                    <p class="text-gray-600">
                        <strong>School Admin: </strong> {{ $order->user->school->school_admin->first_name ?? 'N/A' }} {{ $order->user->school->school_admin->last_name ?? '' }}
                    </p>
                    <p class="text-gray-600">
                        <strong>Phone No: </strong> {{ $order->user->cp_no ?? 'N/A' }}
                    </p>
                </div>
                <div>
                    <h2 class="font-semibold text-lg text-gray-700 mb-2">Shipping Information</h2>
                    <p class="text-gray-600">
                        <strong>Address: </strong> {{ $order->user->school->address ?? 'N/A' }}
                    </p>
                </div>
            </div>

            <hr>

            <div class="bg-gray-50 p-4 rounded-lg shadow-inner overflow-x-auto">
                <h2 class="font-semibold text-lg text-gray-700 mb-4">Order Items</h2>
                <table class="w-full table-auto text-sm text-left text-gray-600">
                    <thead class="bg-gray-200 text-gray-800">
                        <tr>
                            <th class="px-4 py-3">Product</th>
                            <th class="px-4 py-3">Quantity</th>
                            <th class="px-4 py-3">Unit Price</th>
                            <th class="px-4 py-3 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $grandTotal = 0; @endphp
                        @foreach ($order->orderDetails as $detail)
                            @php $grandTotal += $detail->quantity * $detail->price; @endphp
                            <tr class="border-t">
                                <td class="px-4 py-2 font-medium">{{ $detail->product->productName ?? 'Deleted Product' }}</td>
                                <td class="px-4 py-2">{{ $detail->quantity }}</td>
                                <td class="px-4 py-2">₱{{ number_format($detail->price, 2) }}</td>
                                <td class="px-4 py-2 text-right">₱{{ number_format($detail->quantity * $detail->price, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-100 font-semibold border-t">
                        <tr>
                            <td colspan="3" class="px-4 py-2 text-right">Grand Total:</td>
                            <td class="px-4 py-2 text-right text-green-700">₱{{ number_format($grandTotal, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</x-layout>