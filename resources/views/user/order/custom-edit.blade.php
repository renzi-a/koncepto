<x-profile-link>
    <div class="container mx-auto px-4 py-6 space-y-8">
        <div class="flex justify-between items-center mb-4">
            <h1 class="text-3xl font-bold text-gray-800">Edit Custom Order</h1>
        </div>

        <form method="POST" action="{{ route('custom-orders.update', $order->id) }}" enctype="multipart/form-data" id="orderForm">
            @csrf
            @method('PUT')

            <div class="overflow-x-auto">
                <table class="min-w-full table-auto bg-white border border-gray-300 rounded-md shadow-sm" id="customOrderTable">
                    <thead class="bg-gray-100 text-gray-700 text-sm">
                        <tr>
                            <th class="border px-4 py-2 w-20">Item No</th>
                            <th class="border px-4 py-2 w-64">Product Name</th>
                            <th class="border px-4 py-2">Brand</th>
                            <th class="border px-4 py-2">Unit</th>
                            <th class="border px-4 py-2">Quantity</th>
                            <th class="border px-4 py-2">Description</th>
                            <th class="border px-4 py-2">Image</th>
                            <th class="border px-4 py-2 text-center w-8">–</th>
                        </tr>
                    </thead>
                    <tbody id="orderItems">
                        @foreach($order->items as $index => $item)
                            <tr>
                                <td class="border px-2 py-1">
                                    <input type="text" name="items[{{ $index }}][item_no]" value="{{ $item->item_no ?? $index + 1 }}" class="w-full bg-transparent focus:outline-none">
                                </td>
                                <td class="border px-2 py-1">
                                    <input type="text" name="items[{{ $index }}][name]" value="{{ $item->name }}" required class="w-full bg-transparent focus:outline-none">
                                </td>
                                <td class="border px-2 py-1">
                                    <input type="text" name="items[{{ $index }}][brand]" value="{{ $item->brand }}" class="w-full bg-transparent focus:outline-none">
                                </td>
                                <td class="border px-2 py-1">
                                    <select name="items[{{ $index }}][unit]" required class="w-full bg-transparent focus:outline-none">
                                        <option value="">--</option>
                                        @foreach(["pcs", "pc", "unit", "box", "ream", "pack", "set", "bottle", "roll", "pad", "envelope", "bundle"] as $unit)
                                            <option value="{{ $unit }}" @selected($item->unit === $unit)>{{ $unit }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="border px-2 py-1">
                                    <input type="number" name="items[{{ $index }}][quantity]" value="{{ $item->quantity }}" min="1" required class="w-full bg-transparent focus:outline-none">
                                </td>
                                <td class="border px-2 py-1">
                                    <textarea name="items[{{ $index }}][description]" class="w-full bg-transparent focus:outline-none">{{ $item->description }}</textarea>
                                </td>
                                <td class="border px-2 py-1">
                                    <input type="file" name="items[{{ $index }}][photo]" accept="image/*">
                                    @if($item->photo)
                                        <img src="{{ asset('storage/' . $item->photo) }}" alt="Item image" class="w-12 h-12 mt-2 object-cover">
                                    @endif
                                </td>
                                <td class="border text-center">
                                    <button type="button" onclick="this.closest('tr').remove()" class="text-red-500 hover:text-red-700 font-bold text-lg">&minus;</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="flex justify-end mt-4">
                <button type="button" id="addRowBtn" class="px-4 py-2 bg-blue-600 text-white font-semibold rounded hover:bg-blue-700 transition">
                    + Add Row
                </button>
            </div>

            <hr class="my-6 border-t-2 border-gray-300">

            <div class="text-right relative">
                <button type="submit" class="px-6 py-2 bg-[#56AB2F] text-white font-semibold rounded-md hover:bg-green-700 transition flex items-center gap-2">
                    <svg id="spinner" class="animate-spin h-5 w-5 text-white hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                    </svg>
                    <span id="btnText">Update Order</span>
                </button>
            </div>
        </form>
    </div>

    <script>
        document.getElementById('orderForm').addEventListener('submit', function() {
            document.getElementById('spinner').classList.remove('hidden');
            document.getElementById('btnText').textContent = 'Saving...';
        });

        document.getElementById('addRowBtn').addEventListener('click', function () {
            const index = document.querySelectorAll('#orderItems tr').length;
            const row = `
                <tr>
                    <td class="border px-2 py-1"><input type="text" name="items[${index}][item_no]" value="${index + 1}" class="w-full bg-transparent focus:outline-none"></td>
                    <td class="border px-2 py-1"><input type="text" name="items[${index}][name]" required class="w-full bg-transparent focus:outline-none"></td>
                    <td class="border px-2 py-1"><input type="text" name="items[${index}][brand]" class="w-full bg-transparent focus:outline-none"></td>
                    <td class="border px-2 py-1">
                        <select name="items[${index}][unit]" required class="w-full bg-transparent focus:outline-none">
                            <option value="">--</option>
                            @foreach(["pcs", "pc", "unit", "box", "ream", "pack", "set", "bottle", "roll", "pad", "envelope", "bundle"] as $unit)
                                <option value="{{ $unit }}">{{ $unit }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td class="border px-2 py-1"><input type="number" name="items[${index}][quantity]" min="1" required class="w-full bg-transparent focus:outline-none"></td>
                    <td class="border px-2 py-1"><textarea name="items[${index}][description]" class="w-full bg-transparent focus:outline-none"></textarea></td>
                    <td class="border px-2 py-1"><input type="file" name="items[${index}][photo]" accept="image/*"></td>
                    <td class="border text-center"><button type="button" onclick="this.closest('tr').remove()" class="text-red-500 hover:text-red-700 font-bold text-lg">&minus;</button></td>
                </tr>`;
            document.getElementById('orderItems').insertAdjacentHTML('beforeend', row);
        });
    </script>
    @if(session('success'))
    <div id="successModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
        <div class="bg-white p-6 rounded-xl shadow-xl text-center max-w-md w-full animate-pop">
            <svg class="mx-auto h-12 w-12 text-green-500 mb-3" fill="none" stroke="currentColor" stroke-width="1.5"
                 viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
            </svg>
            <h2 class="text-xl font-semibold text-gray-800 mb-2">Success!</h2>
            <p class="text-gray-600">{{ session('success') }}</p>
            <button onclick="document.getElementById('successModal').remove()"
                    class="mt-4 px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition">
                Close
            </button>
        </div>
    </div>

    <style>
        @keyframes pop {
            0% { transform: scale(0.9); opacity: 0; }
            100% { transform: scale(1); opacity: 1; }
        }
        .animate-pop {
            animation: pop 0.2s ease-out;
        }
    </style>
@endif

</x-profile-link>
