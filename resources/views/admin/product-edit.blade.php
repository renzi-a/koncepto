<x-layout>
    <div class="container mx-auto px-4 py-6">
        <h1 class="text-3xl font-bold mb-6">Edit Product</h1>

        @if ($errors->any())
            <div class="bg-red-100 text-red-700 p-4 rounded mb-6">
                <ul class="list-disc pl-6">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form id="editProductForm" action="{{ route('product.update', $product->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="productName" class="block font-semibold">Product Name</label>
                    <input type="text" name="productName" value="{{ $product->productName }}" class="w-full border rounded px-3 py-2" required>
                </div>
                <div>
                    <label for="brandName" class="block font-semibold">Brand</label>
                    <input type="text" name="brandName" value="{{ $product->brandName }}" class="w-full border rounded px-3 py-2" required>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label for="price" class="block font-semibold">Price</label>
                    <input type="number" name="price" value="{{ $product->price }}" class="w-full border rounded px-3 py-2" required>
                </div>
                <div>
                    <label for="category_id" class="block font-semibold">Category</label>
                    <select name="category_id" class="w-full border rounded px-3 py-2" required>
                        @foreach($categories as $category)
                            <option value="{{ $category['id'] }}" {{ $product->category_id == $category['id'] ? 'selected' : '' }}>
                                {{ $category['categoryName'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="unit" class="block font-semibold">Unit</label>
                    <input type="text" name="unit" value="{{ $product->unit }}" class="w-full border rounded px-3 py-2" required>
                </div>
                <div>
                    <label for="quantity" class="block font-semibold">Quantity</label>
                    <input type="number" name="quantity" value="{{ $product->quantity }}" class="w-full border rounded px-3 py-2" required>
                </div>
            </div>

            <div>
                <label for="photo" class="block font-semibold">Product Image</label>
                @if ($product->photo)
                    <img src="{{ asset('storage/' . $product->photo) }}" class="w-20 h-20 object-cover rounded mb-2">
                @endif
                <input type="file" name="photo" class="w-full border rounded px-3 py-2">
            </div>

            <div>
                <label for="description" class="block font-semibold">Description</label>
                <textarea name="description" rows="4" class="w-full border rounded px-3 py-2">{{ $product->description }}</textarea>
            </div>

            <div class="flex space-x-4">
                <a href="{{ route('product.index') }}" class="bg-gray-300 text-gray-800 px-4 py-2 rounded hover:bg-gray-400 transition">Back</a>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">Update Product</button>
            </div>
        </form>
    </div>

    <div id="confirmModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex items-center justify-center z-50 p-4">
        <div class="bg-white p-6 rounded-lg shadow-xl max-w-sm w-full transform transition-all duration-300 scale-95 opacity-0" id="confirmModalContent">
            <h3 class="text-2xl font-bold mb-4 text-gray-800">Update Product</h3>
            <p id="confirmMessage" class="mb-6 text-gray-700 leading-relaxed">Are you sure you want to update this product?</p>
            <div class="flex justify-end space-x-3">
                <button id="noButton" class="px-6 py-2 bg-gray-300 text-gray-800 font-semibold rounded-lg hover:bg-gray-400 transition-colors">No</button>
                <button id="yesButton" class="px-6 py-2 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors">Yes</button>
            </div>
        </div>
    </div>

    <div id="loadingModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex items-center justify-center z-50 p-4">
        <div class="bg-white p-6 rounded-lg shadow-xl max-w-sm w-full text-center">
            <div class="loader ease-linear rounded-full border-4 border-t-4 border-blue-200 h-12 w-12 mb-4 mx-auto" style="border-top-color: #3b82f6;"></div>
            <h3 class="text-xl font-semibold text-gray-800">Updating Product...</h3>
            <p class="text-gray-500">Please wait while we save your changes.</p>
        </div>
    </div>

    <div id="successModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex items-center justify-center z-50 p-4">
        <div class="bg-white p-6 rounded-lg shadow-xl max-w-sm w-full transform transition-all duration-300 scale-95 opacity-0" id="successModalContent">
            <h3 class="text-2xl font-bold mb-4 text-gray-800">Success!</h3>
            <p id="successMessage" class="mb-6 text-gray-700 leading-relaxed">Product updated successfully!</p>
            <div class="flex justify-end">
                <button id="successModalClose" class="px-6 py-2 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 transition-colors">OK</button>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const editProductForm = document.getElementById('editProductForm');
            const confirmModal = document.getElementById('confirmModal');
            const confirmModalContent = document.getElementById('confirmModalContent');
            const loadingModal = document.getElementById('loadingModal');
            const successModal = document.getElementById('successModal');
            const successModalContent = document.getElementById('successModalContent');
            const yesButton = document.getElementById('yesButton');
            const noButton = document.getElementById('noButton');
            const successModalClose = document.getElementById('successModalClose');

            function showModal(modalElement, contentElement) {
                modalElement.classList.remove('hidden');
                if (contentElement) {
                    setTimeout(() => {
                        contentElement.classList.remove('scale-95', 'opacity-0');
                        contentElement.classList.add('scale-100', 'opacity-100');
                    }, 10);
                }
            }

            function hideModal(modalElement, contentElement, callback = () => {}) {
                if (contentElement) {
                    contentElement.classList.remove('scale-100', 'opacity-100');
                    contentElement.classList.add('scale-95', 'opacity-0');
                    setTimeout(() => {
                        modalElement.classList.add('hidden');
                        callback();
                    }, 300);
                } else {
                    modalElement.classList.add('hidden');
                    callback();
                }
            }

            editProductForm.addEventListener('submit', function(event) {
                event.preventDefault();
                showModal(confirmModal, confirmModalContent);
            });

            yesButton.addEventListener('click', function() {
                hideModal(confirmModal, confirmModalContent, () => {
                    showModal(loadingModal);
                    
                    const formData = new FormData(editProductForm);
                    const actionUrl = editProductForm.action;
                    const csrfToken = editProductForm.querySelector('input[name="_token"]').value;

                    fetch(actionUrl, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': csrfToken
                        }
                    })
                    .then(response => {
                        hideModal(loadingModal); // Hide loading modal regardless of outcome
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.message) {
                            document.getElementById('successMessage').textContent = data.message;
                            showModal(successModal, successModalContent);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        hideModal(loadingModal, null, () => {
                            alert('An unexpected error occurred. Please try again.');
                        });
                    });
                });
            });

            noButton.addEventListener('click', function() {
                hideModal(confirmModal, confirmModalContent);
            });

            successModalClose.addEventListener('click', function() {
                hideModal(successModal, successModalContent, () => {
                    window.location.href = "{{ route('product.index') }}";
                });
            });

            // Close modals if the user clicks outside the content
            [confirmModal, successModal].forEach(modal => {
                modal.addEventListener('click', function(e) {
                    if (e.target.id === modal.id) {
                        hideModal(modal, modal.querySelector('[id$="Content"]'));
                    }
                });
            });
        });
    </script>
</x-layout>