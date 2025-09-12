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
                                        @foreach(["pcs","pc","unit","box","ream","pack","set","bottle","roll","pad","envelope","bundle"] as $unit)
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
                                    <button type="button" onclick="removeRow(this)" class="text-red-500 hover:text-red-700 font-bold text-lg">&minus;</button>
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
        let rowIndex = document.querySelectorAll('#orderItems tr').length;

        const unitPlurals = {
            'pc':'pcs','unit':'units','box':'boxes','ream':'reams','pack':'packs','set':'sets',
            'bottle':'bottles','roll':'rolls','pad':'pads','envelope':'envelopes','bundle':'bundles',
            'kg':'kg','g':'g','l':'L','ml':'mL'
        };

        function escapeHtml(str) {
            if (!str) return '';
            return String(str).replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/'/g,'&#39;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
        }

        function updateUnitDisplay(row) {
            const qty = parseInt(row.querySelector('input[name$="[quantity]"]')?.value) || 0;
            const select = row.querySelector('select[name$="[unit]"]');
            if (!select) return;
            for (let opt of select.options) {
                if (!opt.dataset.singular) opt.dataset.singular = opt.value;
                if (!opt.dataset.plural) opt.dataset.plural = unitPlurals[opt.value] || opt.value;
                opt.textContent = (qty > 1 ? opt.dataset.plural : opt.dataset.singular);
            }
            select.value = select.value;
        }

        function removeRow(button) {
            const tableBody = document.getElementById('orderItems');
            if (tableBody.rows.length <= 1) return alert("At least one row required.");
            button.closest('tr').remove();
            updateItemNumbers();
        }

        function updateItemNumbers() {
            document.querySelectorAll('#orderItems tr').forEach((tr,index)=>{
                tr.querySelector('input[name^="items"][name$="[item_no]"]').value = index+1;
                tr.querySelectorAll('input, select, textarea').forEach(input=>{
                    input.name = input.name.replace(/items\[\d+\]/,`items[${index}]`);
                });
                updateUnitDisplay(tr);
            });
            rowIndex = document.querySelectorAll('#orderItems tr').length;
        }

        function createRow(values={}) {
            const tableBody = document.getElementById('orderItems');
            const tr = document.createElement('tr');
            const optionsHtml = Object.keys(unitPlurals).map(u=>{
                const plural = unitPlurals[u];
                const selected = u === (values.unit||'')?'selected':'';
                return `<option value="${escapeHtml(u)}" data-singular="${escapeHtml(u)}" data-plural="${escapeHtml(plural)}" ${selected}>${escapeHtml(u)}</option>`;
            }).join('');
            tr.innerHTML = `
                <td class="border px-2 py-1"><input type="text" name="items[${rowIndex}][item_no]" value="${values.itemNo??(rowIndex+1)}" class="w-full bg-transparent focus:outline-none"></td>
                <td class="border px-2 py-1"><input type="text" name="items[${rowIndex}][name]" value="${escapeHtml(values.name??'')}" required class="w-full bg-transparent focus:outline-none"></td>
                <td class="border px-2 py-1"><input type="text" name="items[${rowIndex}][brand]" value="${escapeHtml(values.brand??'')}" class="w-full bg-transparent focus:outline-none"></td>
                <td class="border px-2 py-1">
                    <select name="items[${rowIndex}][unit]" required class="w-full bg-transparent focus:outline-none">
                        <option value="">--</option>
                        ${optionsHtml}
                    </select>
                </td>
                <td class="border px-2 py-1"><input type="number" name="items[${rowIndex}][quantity]" value="${values.quantity??''}" min="1" required class="w-full bg-transparent focus:outline-none"></td>
                <td class="border px-2 py-1"><textarea name="items[${rowIndex}][description]" class="w-full bg-transparent focus:outline-none">${escapeHtml(values.description??'')}</textarea></td>
                <td class="border px-2 py-1"><input type="file" name="items[${rowIndex}][photo]" accept="image/*"></td>
                <td class="border text-center"><button type="button" onclick="removeRow(this)" class="text-red-500 hover:text-red-700 font-bold text-lg">&minus;</button></td>
            `;
            tableBody.appendChild(tr);

            const qtyInput = tr.querySelector('input[name$="[quantity]"]');
            const unitSelect = tr.querySelector('select[name$="[unit]"]');
            const brandInput = tr.querySelector('input[name$="[brand]"]');

            if (qtyInput) qtyInput.addEventListener('input', ()=>updateUnitDisplay(tr));
            if (unitSelect) unitSelect.addEventListener('change', ()=>updateUnitDisplay(tr));

            if (brandInput && unitSelect) {
                brandInput.addEventListener('keydown',(e)=>{
                    if(e.key==="Tab"){e.preventDefault();unitSelect.focus();unitSelect.dispatchEvent(new MouseEvent('mousedown',{bubbles:true,cancelable:false,view:window}));}
                });
            }

            updateUnitDisplay(tr);
            rowIndex++;
        }

        document.getElementById('addRowBtn').addEventListener('click', ()=>{
            const lastRow = document.querySelector('#orderItems tr:last-child');
            if(lastRow){
                const name = lastRow.querySelector('input[name$="[name]"]').value.trim();
                const unit = lastRow.querySelector('select[name$="[unit]"]').value;
                const qty = lastRow.querySelector('input[name$="[quantity]"]').value.trim();
                if(!name || !unit || !qty) return alert("Please complete Product Name, Unit, and Quantity before adding a new row.");
            }
            createRow();
        });

        document.getElementById('orderForm').addEventListener('submit', function(){
            document.getElementById('spinner').classList.remove('hidden');
            document.getElementById('btnText').textContent = 'Saving...';
        });

        document.addEventListener("keydown", function(e){
            if(e.key==="Enter"){
                const activeRow = e.target.closest("#orderItems tr");
                if(activeRow){
                    const name = activeRow.querySelector('input[name$="[name]"]').value.trim();
                    const unit = activeRow.querySelector('select[name$="[unit]"]').value;
                    const qty = activeRow.querySelector('input[name$="[quantity]"]').value.trim();
                    if(name && unit && qty){ e.preventDefault(); document.getElementById('addRowBtn').click(); }
                }
            }
        });
    </script>
</x-profile-link>
