<x-layout>
    <div class="container mx-auto px-4 py-6">
        <h1 class="text-3xl font-bold mb-6">Add Product</h1>

        @if ($errors->any())
            <div class="bg-red-100 text-red-700 p-4 rounded mb-6">
                <ul class="list-disc pl-6">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form id="addProductForm" method="POST" action="{{ route('product.store') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="productName" class="block font-semibold text-gray-700 mb-1">Product Name</label>
                    <input type="text" name="productName" id="productName"
                            placeholder="e.g. A4 Bond Paper"
                            value="{{ old('productName') }}"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-green-500 focus:border-green-500 shadow-sm"
                            required>
                    @error('productName')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="brandName" class="block font-semibold text-gray-700 mb-1">Brand</label>
                    <input type="text" name="brandName" id="brandName"
                            placeholder="e.g. PaperOne"
                            value="{{ old('brandName') }}"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-green-500 focus:border-green-500 shadow-sm"
                            required>
                    @error('brandName')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label for="price" class="block font-semibold text-gray-700 mb-1">Price</label>
                    <input type="number" name="price" id="price"
                            placeholder="e.g. 250.00"
                            value="{{ old('price') }}"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-green-500 focus:border-green-500 shadow-sm"
                            required step="0.01" min="0">
                    @error('price')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="category_id" class="block font-semibold text-gray-700 mb-1">Category</label>
                    <select name="category_id" id="category_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-green-500 focus:border-green-500 shadow-sm" required>
                        <option value="" disabled {{ old('category_id') ? '' : 'selected' }}>Select Category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category['id'] }}" {{ old('category_id') == $category['id'] ? 'selected' : '' }}>
                                {{ $category['categoryName'] }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="unit" class="block font-semibold text-gray-700 mb-1">Unit</label>
                    <input type="text" name="unit" id="unit"
                            placeholder="e.g. ream, piece, box"
                            value="{{ old('unit') }}"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-green-500 focus:border-green-500 shadow-sm"
                            required>
                    @error('unit')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="quantity" class="block font-semibold text-gray-700 mb-1">Quantity</label>
                    <input type="number" name="quantity" id="quantity"
                            placeholder="e.g. 10"
                            value="{{ old('quantity') }}"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-green-500 focus:border-green-500 shadow-sm"
                            required min="1">
                    @error('quantity')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label for="photo" class="block font-semibold text-gray-700 mb-1">Product Photo</label>
                <input type="file" name="photo" id="photo" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-green-500 focus:border-green-500 shadow-sm">
                @error('photo')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="description" class="block font-semibold text-gray-700 mb-1">Description</label>
                <textarea name="description" id="description" rows="4"
                            placeholder="Optional description here..."
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-green-500 focus:border-green-500 shadow-sm">{{ old('description') }}</textarea>
                @error('description')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div class="flex items-center space-x-4">
                <a href="{{ route('product.index') }}"
                   class="inline-flex items-center px-6 py-3 border border-gray-300 text-gray-700 font-semibold rounded-lg shadow-sm hover:bg-gray-100 transition-transform hover:scale-105">
                    Cancel
                </a>
                <button type="submit"
                        class="inline-flex items-center px-6 py-3 bg-green-600 text-white font-semibold rounded-lg shadow-lg hover:bg-green-700 transition-transform hover:scale-105">
                    Save Product
                </button>
            </div>
        </form>
    </div>

    <div id="quantityConfirmModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex items-center justify-center z-50 p-4">
        <div class="bg-white p-6 rounded-lg shadow-xl max-w-sm w-full transform transition-all duration-300 scale-95 opacity-0" id="quantityConfirmModalContent">
            <h3 class="text-2xl font-bold mb-4 text-gray-800">Product Exists</h3>
            <p id="duplicateMessage" class="mb-6 text-gray-700 leading-relaxed"></p>
            <div class="flex justify-end space-x-3">
                <button id="noButton" class="px-6 py-2 bg-gray-300 text-gray-800 font-semibold rounded-lg hover:bg-gray-400 transition-colors">No</button>
                <button id="yesButton" class="px-6 py-2 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors">Yes, Add Quantity</button>
            </div>
        </div>
    </div>

    <div id="addQuantityModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex items-center justify-center z-50 p-4">
        <div class="bg-white p-6 rounded-lg shadow-xl max-w-sm w-full transform transition-all duration-300 scale-95 opacity-0" id="addQuantityModalContent">
            <h3 class="text-2xl font-bold mb-4 text-gray-800">Add Quantity</h3>
            <p class="mb-4 text-gray-700">How much quantity do you want to add to <span id="existingProductName" class="font-bold text-green-700"></span>?</p>
            <input type="number" id="addQuantityInput" class="w-full border border-gray-300 rounded-lg px-3 py-2 mb-4 focus:ring-green-500 focus:border-green-500 shadow-sm" min="1" value="1" placeholder="Enter quantity to add">
            <div id="quantityModalError" class="text-red-500 text-sm mb-4 hidden"></div>
            <div class="flex justify-end space-x-3">
                <button id="cancelAddQuantity" class="px-6 py-2 bg-gray-300 text-gray-800 font-semibold rounded-lg hover:bg-gray-400 transition-colors">Cancel</button>
                <button id="confirmAddQuantity" class="px-6 py-2 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 transition-colors">Confirm Add</button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const addProductForm = document.getElementById('addProductForm');
            const quantityConfirmModal = document.getElementById('quantityConfirmModal');
            const quantityConfirmModalContent = document.getElementById('quantityConfirmModalContent');
            const addQuantityModal = document.getElementById('addQuantityModal');
            const addQuantityModalContent = document.getElementById('addQuantityModalContent');
            const yesButton = document.getElementById('yesButton');
            const noButton = document.getElementById('noButton');
            const cancelAddQuantity = document.getElementById('cancelAddQuantity');
            const confirmAddQuantity = document.getElementById('confirmAddQuantity');
            const duplicateMessage = document.getElementById('duplicateMessage');
            const existingProductNameSpan = document.getElementById('existingProductName');
            const addQuantityInput = document.getElementById('addQuantityInput');
            const quantityModalError = document.getElementById('quantityModalError');

            const successModal = document.createElement('div');
            successModal.id = 'successModal';
            successModal.className = 'fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex items-center justify-center z-50 p-4';
            successModal.innerHTML = `
                <div class="bg-white p-6 rounded-lg shadow-xl max-w-sm w-full transform transition-all duration-300 scale-95 opacity-0" id="successModalContent">
                    <h3 class="text-2xl font-bold mb-4 text-gray-800">Success!</h3>
                    <p id="successMessage" class="mb-6 text-gray-700 leading-relaxed"></p>
                    <div class="flex justify-end">
                        <button id="successModalClose" class="px-6 py-2 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 transition-colors">OK</button>
                    </div>
                </div>
            `;
            document.body.appendChild(successModal);

            const errorModal = document.createElement('div');
            errorModal.id = 'errorModal';
            errorModal.className = 'fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex items-center justify-center z-50 p-4';
            errorModal.innerHTML = `
                <div class="bg-white p-6 rounded-lg shadow-xl max-w-sm w-full transform transition-all duration-300 scale-95 opacity-0" id="errorModalContent">
                    <h3 class="text-2xl font-bold mb-4 text-red-600">Error!</h3>
                    <p id="errorMessage" class="mb-6 text-gray-700 leading-relaxed"></p>
                    <div class="flex justify-end">
                        <button id="errorModalClose" class="px-6 py-2 bg-red-600 text-white font-semibold rounded-lg hover:bg-red-700 transition-colors">Close</button>
                    </div>
                </div>
            `;
            document.body.appendChild(errorModal);

            const successModalContent = document.getElementById('successModalContent');
            const successMessage = document.getElementById('successMessage');
            const successModalClose = document.getElementById('successModalClose');
            const errorModalContent = document.getElementById('errorModalContent');
            const errorMessage = document.getElementById('errorMessage');
            const errorModalClose = document.getElementById('errorModalClose');

            let duplicateProductId = null;

            function showModal(modalElement, contentElement) {
                modalElement.classList.remove('hidden');
                setTimeout(() => {
                    contentElement.classList.remove('scale-95', 'opacity-0');
                    contentElement.classList.add('scale-100', 'opacity-100');
                }, 10);
            }

            function hideModal(modalElement, contentElement, callback = () => {}) {
                contentElement.classList.remove('scale-100', 'opacity-100');
                contentElement.classList.add('scale-95', 'opacity-0');
                setTimeout(() => {
                    modalElement.classList.add('hidden');
                    callback();
                }, 300);
            }

            addProductForm.addEventListener('submit', function(event) {
                event.preventDefault();

                const formData = new FormData(addProductForm);
                const actionUrl = addProductForm.action;
                const csrfToken = formData.get('_token');

                fetch(actionUrl, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    }
                })
                .then(response => {
                    if (response.redirected) {
                        window.location.href = response.url;
                        return new Promise(() => {});
                    }
                    return response.json().then(data => ({
                        status: response.status,
                        data: data
                    }));
                })
                .then(({ status, data }) => {
                    if (status === 409 && data.status === 'duplicate') {
                        duplicateProductId = data.product.id;
                        duplicateMessage.innerHTML = `A product named "<span class="font-bold">${data.product.productName}</span>" from "<span class="font-bold">${data.product.brandName}</span>" (current quantity: <span class="font-bold">${data.product.current_quantity}</span> ${data.product.unit}) already exists. Do you want to add to its quantity?`;
                        showModal(quantityConfirmModal, quantityConfirmModalContent);
                    } else if (status === 201 && data.status === 'success') {
                        successMessage.textContent = data.message;
                        showModal(successModal, successModalContent);
                    } else {
                        console.error('Unexpected response:', data);
                        errorMessage.textContent = 'An unexpected error occurred: ' + (data.message || 'Please try again.');
                        showModal(errorModal, errorModalContent);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    errorMessage.textContent = 'A network error occurred during product submission. Please check your internet connection and try again.';
                    showModal(errorModal, errorModalContent);
                });
            });

            yesButton.addEventListener('click', function() {
                hideModal(quantityConfirmModal, quantityConfirmModalContent, () => {
                    existingProductNameSpan.textContent = document.getElementById('productName').value;
                    addQuantityInput.value = document.getElementById('quantity').value;
                    showModal(addQuantityModal, addQuantityModalContent);
                    addQuantityInput.focus();
                });
            });

            noButton.addEventListener('click', function() {
                hideModal(quantityConfirmModal, quantityConfirmModalContent, () => {
                    errorMessage.textContent = 'Product was not added. Please modify the product details if you wish to add a new unique product.';
                    showModal(errorModal, errorModalContent);
                });
            });

            cancelAddQuantity.addEventListener('click', function() {
                hideModal(addQuantityModal, addQuantityModalContent, () => {
                    quantityModalError.classList.add('hidden');
                    quantityModalError.textContent = '';
                });
            });

            confirmAddQuantity.addEventListener('click', function() {
                const quantityToAdd = parseInt(addQuantityInput.value);
                quantityModalError.classList.add('hidden');
                quantityModalError.textContent = '';

                if (isNaN(quantityToAdd) || quantityToAdd <= 0) {
                    quantityModalError.textContent = 'Please enter a valid quantity greater than 0.';
                    quantityModalError.classList.remove('hidden');
                    return;
                }

                if (duplicateProductId) {
                    const addQuantityUrl = `/admin/product/${duplicateProductId}/add-quantity`;
                    const data = new URLSearchParams();
                    data.append('quantity_to_add', quantityToAdd);
                    data.append('_token', addProductForm.querySelector('[name="_token"]').value);

                    fetch(addQuantityUrl, {
                        method: 'POST',
                        body: data,
                        headers: {
                            'X-CSRF-TOKEN': addProductForm.querySelector('[name="_token"]').value,
                            'Content-Type': 'application/x-www-form-urlencoded',
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            return response.json().then(err => { throw new Error(err.message || 'Failed to update quantity.'); });
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.status === 'success') {
                            successMessage.textContent = data.message + ` New quantity: ${data.new_quantity}`;
                            hideModal(addQuantityModal, addQuantityModalContent, () => {
                                showModal(successModal, successModalContent);
                            });
                        } else {
                            errorMessage.textContent = 'Failed to update quantity: ' + (data.message || 'Unknown error.');
                            hideModal(addQuantityModal, addQuantityModalContent, () => {
                                showModal(errorModal, errorModalContent);
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error adding quantity:', error);
                        errorMessage.textContent = 'Error updating quantity. Please try again.';
                        hideModal(addQuantityModal, addQuantityModalContent, () => {
                            showModal(errorModal, errorModalContent);
                        });
                    });
                }
            });

            quantityConfirmModal.addEventListener('click', function(e) {
                if (e.target === quantityConfirmModal) {
                    hideModal(quantityConfirmModal, quantityConfirmModalContent);
                }
            });
            addQuantityModal.addEventListener('click', function(e) {
                if (e.target === addQuantityModal) {
                    hideModal(addQuantityModal, addQuantityModalContent);
                    quantityModalError.classList.add('hidden');
                    quantityModalError.textContent = '';
                }
            });

            successModalClose.addEventListener('click', function() {
                hideModal(successModal, successModalContent, () => {
                    window.location.href = "{{ route('product.index') }}";
                });
            });

            successModal.addEventListener('click', function(e) {
                if (e.target === successModal) {
                    hideModal(successModal, successModalContent, () => {
                        window.location.href = "{{ route('product.index') }}";
                    });
                }
            });

            errorModalClose.addEventListener('click', function() {
                hideModal(errorModal, errorModalContent);
            });

            errorModal.addEventListener('click', function(e) {
                if (e.target === errorModal) {
                    hideModal(errorModal, errorModalContent);
                }
            });
        });
    </script>
</x-layout>