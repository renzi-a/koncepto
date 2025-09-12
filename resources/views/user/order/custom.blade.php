<x-profile-link>
    <div class="container mx-auto px-4 py-6 space-y-8">
        <div class="flex justify-between items-center mb-4">
            <h1 class="text-3xl font-bold text-gray-800">Custom Order</h1>
        </div>

        <div class="mb-6">
            <label class="block font-semibold text-gray-700 mb-2">Upload Excel File</label>
            <div class="flex flex-col md:flex-row items-start md:items-center gap-4 justify-between">
                <input type="file" id="excelUpload" accept=".xlsx, .xls"
                    class="block w-full md:w-auto text-sm text-gray-700
                    file:mr-4 file:py-2 file:px-4
                    file:rounded file:border-0
                    file:text-sm file:font-semibold
                    file:bg-blue-600 file:text-white
                    hover:file:bg-blue-700 transition" />
                <div class="flex items-center gap-2 ml-auto">
                    <button id="removeFileBtn"
                        class="hidden px-4 py-2 bg-red-600 text-white font-semibold rounded hover:bg-red-700 transition">
                        Remove File
                    </button>
                    <a href="/Koncepto-Template.xlsx" download
                        class="px-4 py-2 bg-green-600 text-white font-semibold rounded hover:bg-green-700 transition">
                        Download Template
                    </a>
                </div>
            </div>
        </div>

        <form method="POST" id="customOrderForm" action="{{ route('custom-orders.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="overflow-x-auto">
                <table class="min-w-full table-auto bg-white border border-gray-300 rounded-md shadow-sm" id="customOrderTable">
                    <thead class="bg-gray-100 text-gray-700 text-sm">
                        <tr>
                            <th class="border px-4 py-2 w-20">Item No</th>
                            <th class="border px-4 py-2 w-64">Product Name</th>
                            <th class="border px-4 py-2">Brand</th>
                            <th class="border px-4 py-2">Unit</th>
                            <th class="border px-4 py-2">Quantity</th>
                            <th class="border px-4 py-2">Photo</th>
                            <th class="border px-4 py-2">Description</th>
                            <th class="border px-4 py-2 text-center w-8">–</th>
                        </tr>
                    </thead>
                    <tbody id="orderItems">
                    </tbody>
                </table>
            </div>

            <div class="flex justify-end mt-4">
                <button type="button" id="addRowBtn" class="px-4 py-2 bg-blue-600 text-white font-semibold rounded hover:bg-blue-700 transition">
                    + Add Row
                </button>
            </div>

            <hr class="my-6 border-t-2 border-gray-300">

            <div class="text-right">
                <button type="submit" class="px-6 py-2 bg-[#56AB2F] text-white font-semibold rounded-md hover:bg-green-700 transition">
                    Get Price Quotation
                </button>
            </div>
        </form>
    </div>

    <div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="errorModalLabel">Error</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="errorModalBody"></div>
            </div>
        </div>
    </div>

    <div id="loadingOverlay" class="hidden fixed inset-0 z-50 bg-black bg-opacity-40 flex items-center justify-center">
        <div class="bg-white rounded-xl p-8 shadow-lg flex items-center space-x-5 animate-fadeIn">
            <svg class="animate-spin animate-bounceScale text-[#56AB2F]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
            </svg>
            <span class="text-[#56AB2F] font-semibold">Submitting order...</span>
        </div>
    </div>
</x-profile-link>

<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    let rowIndex = 0;

    const columnMappings = {
        itemNo: ['item no', 'item number', 'no', 'code', 'product code'],
        productName: ['product name', 'item', 'item description', 'name'],
        brand: ['brand', 'offered brand'],
        unit: ['unit', 'unit of measure', 'measurement'],
        quantity: ['quantity', 'qty', 'qty.'],
        photo: ['photo', 'image', 'picture'],
        description: ['description', 'remarks', 'note']
    };

    function normalizeHeader(str) {
        return String(str).toLowerCase().replace(/[^a-z0-9]+/g, ' ').trim().replace(/\s+/g, ' ');
    }

    function showErrorModal(messages) {
        document.getElementById("errorModalBody").innerHTML = Array.isArray(messages) ? messages.join("<br>") : messages;
        new bootstrap.Modal(document.getElementById("errorModal")).show();
    }

    function getColumnIndex(rawHeaders, keywords) {
        const normalizedKeywords = keywords.map(normalizeHeader);
        for (let i = 0; i < rawHeaders.length; i++) {
            const header = normalizeHeader(rawHeaders[i]);
            if (header && !/^\d+$/.test(header) && normalizedKeywords.includes(header)) {
                return i;
            }
        }
        return -1;
    }

    // mapping singular -> plural for display
    const unitPlurals = {
        'pc': 'pcs',
        'unit': 'units',
        'box': 'boxes',
        'ream': 'reams',
        'pack': 'packs',
        'set': 'sets',
        'bottle': 'bottles',
        'roll': 'rolls',
        'pad': 'pads',
        'envelope': 'envelopes',
        'bundle': 'bundles',
        'kg': 'kg', // unchanged
        'g': 'g', // unchanged
        'l': 'L', // unchanged (lowercase key)
        'ml': 'mL' // unchanged display
    };

    // update the visible option text inside the SELECT based on quantity
    function updateUnitDisplay(row) {
        const quantityInput = row.querySelector('input[name$="[quantity]"]');
        const unitSelect = row.querySelector('select[name$="[unit]"]');
        if (!unitSelect) return;

        // parse quantity safely (empty should be treated as 0)
        const quantity = parseInt(quantityInput?.value) || 0;

        // update every option's text (so dropdown shows plural forms too)
        for (let i = 0; i < unitSelect.options.length; i++) {
            const opt = unitSelect.options[i];
            // store singular/plural in dataset if not present
            if (!opt.dataset.singular) opt.dataset.singular = String(opt.value);
            if (!opt.dataset.plural) opt.dataset.plural = (unitPlurals[String(opt.value)] || String(opt.value));
            opt.textContent = (quantity > 1 ? opt.dataset.plural : opt.dataset.singular);
        }

        // ensure select keeps selected value (text changed only)
        unitSelect.value = unitSelect.value;
    }

    function removeRow(button) {
        const tableBody = document.getElementById('orderItems');
        if (tableBody.rows.length <= 1) {
            showErrorModal("At least one row is required.");
            return;
        }
        button.closest('tr').remove();
        updateItemNumbers();
    }

    function updateItemNumbers() {
        const rows = document.querySelectorAll('#orderItems tr');
        rows.forEach((tr, index) => {
            const itemNoInput = tr.querySelector('input[name^="items"][name$="[item_no]"]');
            if (itemNoInput) itemNoInput.value = index + 1;

            const inputs = tr.querySelectorAll('input, select, textarea');
            inputs.forEach(input => {
                if (input.name) {
                    // replace only the first occurrence of index
                    input.name = input.name.replace(/items\[\d+\]/, `items[${index}]`);
                }
            });

            // reattach listeners to ensure dynamic behavior still works after renumbering
            const quantityInput = tr.querySelector('input[name$="[quantity]"]');
            const unitSelect = tr.querySelector('select[name$="[unit]"]');

            if (quantityInput) {
                quantityInput.removeEventListener; // safe no-op if not supported
                quantityInput.addEventListener('input', () => updateUnitDisplay(tr));
            }
            if (unitSelect) {
                unitSelect.removeEventListener;
                unitSelect.addEventListener('change', () => updateUnitDisplay(tr));
            }
        });
        rowIndex = rows.length;
    }

    function createRow(values = {}) {
        const tableBody = document.getElementById('orderItems');
        const tr = document.createElement('tr');

        // build options with data-singular and data-plural attributes
        const optionsHtml = Object.keys(unitPlurals).map(u => {
            const plural = unitPlurals[u] || u;
            const selected = u === (values.unit || '') ? 'selected' : '';
            // option text will be adjusted by updateUnitDisplay after insertion
            return `<option value="${escapeHtml(u)}" data-singular="${escapeHtml(u)}" data-plural="${escapeHtml(plural)}" ${selected}>${escapeHtml(u)}</option>`;
        }).join('');

        tr.innerHTML = `
            <td class="border px-2 py-1 w-20">
                <input type="text" name="items[${rowIndex}][item_no]" value="${values.itemNo ?? (rowIndex + 1)}" class="w-full bg-transparent focus:outline-none">
            </td>
            <td class="border px-2 py-1 w-64">
                <input type="text" name="items[${rowIndex}][name]" value="${escapeHtml(values.name ?? '')}" required class="w-full bg-transparent focus:outline-none">
            </td>
            <td class="border px-2 py-1">
                <input type="text" name="items[${rowIndex}][brand]" value="${escapeHtml(values.brand ?? '')}" class="w-full bg-transparent focus:outline-none">
            </td>
            <td class="border px-2 py-1">
                <div class="relative flex items-center gap-2">
                    <select name="items[${rowIndex}][unit]" required class="bg-transparent focus:outline-none appearance-none pr-6">
                        <option value="">--</option>
                        ${optionsHtml}
                    </select>
                    <svg class="w-4 h-4 absolute right-2 top-1/2 transform -translate-y-1/2 pointer-events-none text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>
            </td>
            <td class="border px-2 py-1">
                <input type="number" name="items[${rowIndex}][quantity]" min="1" value="${values.quantity ?? ''}" required class="w-full bg-transparent focus:outline-none">
            </td>
            <td class="border px-2 py-1">
                <input type="file" name="items[${rowIndex}][photo]" class="w-full bg-transparent focus:outline-none">
            </td>
            <td class="border px-2 py-1">
                <textarea name="items[${rowIndex}][description]" rows="1" class="w-full bg-transparent focus:outline-none resize-none">${escapeHtml(values.description ?? '')}</textarea>
            </td>
            <td class="border text-center">
                <button type="button" onclick="removeRow(this)" class="text-red-500 hover:text-red-700 font-bold text-lg">&minus;</button>
            </td>
        `;
        tableBody.appendChild(tr);

        // After append, wire up event listeners on the new row
        const newRow = tableBody.lastElementChild;
        const quantityInput = newRow.querySelector('input[name$="[quantity]"]');
        const unitSelect = newRow.querySelector('select[name$="[unit]"]');
        const brandInput = newRow.querySelector('input[name$="[brand]"]');

        if (quantityInput) quantityInput.addEventListener('input', () => updateUnitDisplay(newRow));
        if (unitSelect) unitSelect.addEventListener('change', () => updateUnitDisplay(newRow));

        // Add the new event listener for the Brand input
        if (brandInput && unitSelect) {
            brandInput.addEventListener('keydown', (e) => {
                if (e.key === "Tab") {
                    e.preventDefault(); // Prevent the default tab behavior
                    unitSelect.focus(); // Move focus to the unit select
                    // Now programmatically open the dropdown
                    const event = new MouseEvent('mousedown', {
                        bubbles: true,
                        cancelable: false,
                        view: window
                    });
                    unitSelect.dispatchEvent(event);
                }
            });
        }
        
        // Initialize the visible option texts based on initial quantity (if Excel provided one)
        updateUnitDisplay(newRow);

        rowIndex++;
    }

    // helper to escape inserted values
    function escapeHtml(str) {
        if (str === null || str === undefined) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    }

    // Add row button: validate last row required fields are filled, then add.
    document.getElementById('addRowBtn').addEventListener('click', () => {
        const lastRow = document.querySelector('#orderItems tr:last-child');
        if (lastRow) {
            const nameEl = lastRow.querySelector('input[name$="[name]"]');
            const unitEl = lastRow.querySelector('select[name$="[unit]"]');
            const qtyEl = lastRow.querySelector('input[name$="[quantity]"]');

            const name = nameEl ? nameEl.value.trim() : '';
            const unit = unitEl ? unitEl.value : '';
            const qty = qtyEl ? qtyEl.value.trim() : '';

            if (!name || !unit || !qty) {
                showErrorModal("Please complete Product Name, Unit, and Quantity before adding a new row.");
                return;
            }
        }
        createRow();

        // focus newly created row's product name
        const newLast = document.querySelector('#orderItems tr:last-child');
        if (newLast) {
            const firstInput = newLast.querySelector('input[name$="[name]"]');
            if (firstInput) firstInput.focus();
        }
    });

    // Remove file -> reset table to 1 empty row
    document.getElementById('removeFileBtn').addEventListener('click', () => {
        document.getElementById('excelUpload').value = '';
        document.getElementById('removeFileBtn').classList.add('hidden');
        document.getElementById('orderItems').innerHTML = '';
        rowIndex = 0;
        createRow();
    });

    // Excel upload parsing (keeps your original logic but doesn't default quantity to 1)
    document.getElementById('excelUpload').addEventListener('change', function (e) {
        const file = e.target.files[0];
        if (!file) return;

        document.getElementById('removeFileBtn').classList.remove('hidden');
        const reader = new FileReader();

        reader.onload = function (event) {
            try {
                const data = new Uint8Array(event.target.result);
                const workbook = XLSX.read(data, { type: 'array' });
                const worksheet = workbook.Sheets[workbook.SheetNames[0]];
                const jsonData = XLSX.utils.sheet_to_json(worksheet, { header: 1 });

                if (!jsonData.length) {
                    showErrorModal("Excel file is empty or not properly formatted.");
                    return;
                }

                let headerRowIndex = 0;
                for (let i = 0; i < jsonData.length; i++) {
                    const row = jsonData[i];
                    if (row && row.some(cell => columnMappings.itemNo.includes(normalizeHeader(cell)))) {
                        headerRowIndex = i;
                        break;
                    }
                }

                const rawHeaders = jsonData[headerRowIndex];
                const dataStartRow = headerRowIndex + 1;

                const colIndices = {
                    itemNo: getColumnIndex(rawHeaders, columnMappings.itemNo),
                    productName: getColumnIndex(rawHeaders, columnMappings.productName),
                    brand: getColumnIndex(rawHeaders, columnMappings.brand),
                    unit: getColumnIndex(rawHeaders, columnMappings.unit),
                    quantity: getColumnIndex(rawHeaders, columnMappings.quantity),
                    photo: getColumnIndex(rawHeaders, columnMappings.photo),
                    description: getColumnIndex(rawHeaders, columnMappings.description)
                };

                const tableBody = document.getElementById('orderItems');
                tableBody.innerHTML = '';
                rowIndex = 0;

                for (let i = dataStartRow; i < jsonData.length; i++) {
                    const row = jsonData[i] || [];

                    const itemNo = (colIndices.itemNo >= 0) ? (row[colIndices.itemNo] ?? '') : '';
                    const productName = (colIndices.productName >= 0) ? (row[colIndices.productName] ?? '') : '';
                    const brand = (colIndices.brand >= 0) ? (row[colIndices.brand] ?? '') : '';
                    let unit = (colIndices.unit >= 0) ? String(row[colIndices.unit] ?? '').trim() : '';
                    const quantity = (colIndices.quantity >= 0) ? (row[colIndices.quantity] ?? '') : '';
                    const photo = (colIndices.photo >= 0) ? (row[colIndices.photo] ?? '') : '';
                    const description = (colIndices.description >= 0) ? (row[colIndices.description] ?? '') : '';

                    const allEmpty = [productName, brand, unit, quantity].every(val => val === null || val === undefined || String(val).trim() === '');
                    if (allEmpty) continue;

                    // normalize unit to known singular key if possible
                    unit = String(unit).toLowerCase();
                    if (!unitPlurals.hasOwnProperty(unit)) {
                        // if excel had 'pcs' or 'boxes', try to map back to singular
                        const reverse = Object.keys(unitPlurals).find(k => String(unitPlurals[k]).toLowerCase() === unit);
                        if (reverse) unit = reverse;
                    }
                    if (!unit) unit = '';

                    // IMPORTANT: do NOT auto-default quantity to 1. Leave blank when excel empty.
                    const qtyValue = (quantity === '' || quantity === null || quantity === undefined || isNaN(Number(quantity))) ? '' : Number(quantity);

                    createRow({
                        itemNo: itemNo,
                        name: productName,
                        brand: brand,
                        unit: unit,
                        quantity: qtyValue,
                        photo: photo,
                        description: description
                    });
                }

                if (rowIndex === 0) {
                    showErrorModal("No valid data rows found in the Excel file.");
                    createRow();
                }
            } catch (err) {
                console.error(err);
                showErrorModal("Failed to parse Excel file. Make sure the file is a valid .xls/.xlsx spreadsheet.");
            }
        };

        reader.readAsArrayBuffer(file);
    });

    document.addEventListener("keydown", function (e) {
        if (e.key === "Enter") {
            const activeRow = e.target.closest("#orderItems tr");
            if (activeRow) {
                const nameEl = activeRow.querySelector('input[name$="[name]"]');
                const unitEl = activeRow.querySelector('select[name$="[unit]"]');
                const qtyEl = activeRow.querySelector('input[name$="[quantity]"]');

                const name = nameEl ? nameEl.value.trim() : '';
                const unit = unitEl ? unitEl.value : '';
                const qty = qtyEl ? qtyEl.value.trim() : '';

                if (name && unit && qty) {
                    e.preventDefault(); 
                    document.getElementById('addRowBtn').click();

                    setTimeout(() => {
                        const lastRow = document.querySelector('#orderItems tr:last-child');
                        if (lastRow) {
                            const firstInput = lastRow.querySelector('input[name$="[name]"]');
                            if (firstInput) firstInput.focus();
                        }
                    }, 0);
                }
            }
        }
    });

    window.addEventListener('DOMContentLoaded', () => {
        if (rowIndex === 0) createRow();
    });

    document.addEventListener("DOMContentLoaded", function () {
        const form = document.getElementById("customOrderForm");
        const overlay = document.getElementById("loadingOverlay");
        if (form) {
            form.addEventListener("submit", function () {
                if (overlay) overlay.classList.remove("hidden");
            });
        }
    });
</script>