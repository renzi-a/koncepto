<x-layout>
    <div class="container mx-auto px-4 py-6 space-y-6">
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-800">
                Quotation for Order #{{ $order->order_code ?? $order->id }}
            </h1>
            <a href="{{ route('admin.custom-orders.show', $order->id) }}" class="text-blue-600 hover:underline">← Back to Custom Order</a>
        </div>

        <form method="POST" action="{{ route('admin.custom-orders.quotation.save', $order->id) }}">
            @csrf
            <div class="overflow-x-auto bg-white rounded-lg shadow">
                <table class="min-w-full table-auto text-sm text-left text-gray-600">
                    <thead class="bg-gray-100 text-gray-800 font-semibold">
                        <tr>
                            <th class="px-4 py-3">Name</th>
                            <th class="px-4 py-3">Brand</th>
                            <th class="px-4 py-3">Unit</th>
                            <th class="px-4 py-3">Quantity</th>
                            <th class="px-4 py-3">Price (₱)</th>
                            <th class="px-4 py-3">Total Price (₱)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($order->items as $item)
                            <tr class="border-t">
                                <td class="px-4 py-2">{{ $item->name }}</td>
                                <td class="px-4 py-2">{{ $item->brand ?? 'N/A' }}</td>
                                <td class="px-4 py-2">{{ $item->unit ?? 'N/A' }}</td>
                                <td class="px-4 py-2 quantity-cell">{{ $item->quantity }}</td>
                                <td class="px-4 py-2">
                                    <input type="number" step="0.01" min="0" name="prices[{{ $item->id }}]" 
                                        value="{{ old('prices.' . $item->id, $item->price ?? '') }}"
                                        class="border border-gray-300 rounded px-2 py-1 w-24 price-input" 
                                        data-quantity="{{ $item->quantity }}" />
                                </td>
                                <td class="px-4 py-2 total-price-cell">₱{{ number_format($item->total_price ?? 0, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-gray-100 font-semibold">
                            <td colspan="5" class="px-4 py-3 text-right">Grand Total:</td>
                            <td class="px-4 py-3" id="grand-total">₱0.00</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="mt-6 flex justify-end space-x-4">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Save Prices</button>
            </div>
        </form>
    </div>

    <script>
        function updateGrandTotal() {
            let grandTotal = 0;
            document.querySelectorAll('.total-price-cell').forEach(cell => {
                const text = cell.textContent.replace('₱', '').trim();
                grandTotal += parseFloat(text) || 0;
            });
            document.getElementById('grand-total').textContent = `₱${grandTotal.toFixed(2)}`;
        }

        document.querySelectorAll('.price-input').forEach(input => {
            input.addEventListener('input', e => {
                const quantity = parseFloat(e.target.dataset.quantity);
                const price = parseFloat(e.target.value) || 0;
                const totalPrice = quantity * price;
                const totalPriceCell = e.target.closest('tr').querySelector('.total-price-cell');
                totalPriceCell.textContent = `₱${totalPrice.toFixed(2)}`;
                updateGrandTotal();
            });
        });

        updateGrandTotal();
    </script>
</x-layout>
