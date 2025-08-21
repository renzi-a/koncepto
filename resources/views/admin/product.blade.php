<x-layout>
    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded-lg shadow" role="alert">
            <p class="font-bold">Success!</p>
            <p>{{ session('success') }}</p>
        </div>
    @endif

    <div class="container mx-auto px-4 py-8">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4">
            <h1 class="text-3xl font-bold text-gray-800">Products</h1>

            <a href="{{ route('product.create') }}"
               class="inline-flex items-center px-6 py-3 bg-green-600 text-white font-semibold rounded-lg shadow-lg hover:bg-green-700 transition-transform hover:scale-105">
                + Add Product
            </a>
        </div>

        <div class="flex flex-col sm:flex-row sm:items-center gap-4 mb-6">
            <select id="category" name="category_id"
                    class="px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-green-500 focus:border-green-500 w-full sm:w-64">
                <option value="">All Categories</option>
                @foreach($categories as $category)
                    <option value="{{ $category['id'] }}" {{ request('category_id') == $category['id'] ? 'selected' : '' }}>
                        {{ $category['categoryName'] }}
                    </option>
                @endforeach
            </select>

            <input type="text" id="searchInput" placeholder="Search products..."
                   class="border px-4 py-2 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 w-full sm:w-64" value="{{ request('q') }}">
        </div>

        <div id="product-table-container">
            @include('admin.partials.products-table-content', ['products' => $products])
        </div>
    </div>

    <div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex items-center justify-center z-50 p-4">
        <div class="bg-white p-6 rounded-lg shadow-xl max-w-sm w-full transform transition-all duration-300 scale-95 opacity-0" id="deleteModalContent">
            <h3 class="text-2xl font-bold mb-4 text-red-600">Confirm Deletion</h3>
            <p class="mb-6 text-gray-700 leading-relaxed">Are you sure you want to delete this product? This action cannot be undone.</p>
            <div class="flex justify-end space-x-3">
                <button id="cancelDeleteBtn" class="px-6 py-2 bg-gray-300 text-gray-800 font-semibold rounded-lg hover:bg-gray-400 transition-colors">Cancel</button>
                <button id="confirmDeleteBtn" class="px-6 py-2 bg-red-600 text-white font-semibold rounded-lg hover:bg-red-700 transition-colors">Delete</button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const searchInput = document.getElementById('searchInput');
            const categoryFilter = document.getElementById('category');
            const productTableContainer = document.getElementById('product-table-container');

            const deleteModal = document.getElementById('deleteModal');
            const deleteModalContent = document.getElementById('deleteModalContent');
            const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
            const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');

            let searchTimeout;
            let formToDelete = null;

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

            function fetchProducts(page = 1) {
                const query = searchInput.value;
                const categoryId = categoryFilter.value;
                const url = new URL("{{ route('product.index') }}");
                url.searchParams.append('q', query);
                url.searchParams.append('category_id', categoryId);
                url.searchParams.append('page', page);

                fetch(url.toString(), {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.text();
                })
                .then(html => {
                    productTableContainer.innerHTML = html;
                    bindDeleteForms();
                })
                .catch(error => {
                    console.error('Error fetching products:', error);
                    productTableContainer.innerHTML = `<div class="p-6 text-center text-red-500">Error loading products. Please try again.</div>`;
                });
            }

            function debounce(func, delay) {
                return function(...args) {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(() => {
                        func.apply(this, args);
                    }, delay);
                };
            }

            const debouncedFetch = debounce(fetchProducts, 300);

            searchInput.addEventListener('input', () => debouncedFetch());
            categoryFilter.addEventListener('change', () => debouncedFetch());

            productTableContainer.addEventListener('click', function(e) {
                const link = e.target.closest('.pagination a');
                if (link) {
                    e.preventDefault();
                    const url = new URL(link.href);
                    const page = url.searchParams.get('page');
                    fetchProducts(page);
                }
            });
            
            function bindDeleteForms() {
                productTableContainer.querySelectorAll('.delete-form').forEach(form => {
                    form.removeEventListener('submit', handleDelete);
                    form.addEventListener('submit', handleDelete);
                });
            }

            function handleDelete(event) {
                event.preventDefault();
                formToDelete = event.target;
                showModal(deleteModal, deleteModalContent);
            }

            confirmDeleteBtn.addEventListener('click', function() {
                if (formToDelete) {
                    const url = formToDelete.getAttribute('action');
                    
                    const formData = new FormData();
                    formData.append('_method', 'DELETE');
                    formData.append('_token', formToDelete.querySelector('input[name="_token"]').value);

                    fetch(url, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log(data.success);
                        hideModal(deleteModal, deleteModalContent, () => {
                            formToDelete.closest('tr').remove();
                        });
                    })
                    .catch(error => {
                        console.error('An error occurred:', error);
                        hideModal(deleteModal, deleteModalContent);
                    });
                }
            });

            cancelDeleteBtn.addEventListener('click', function() {
                hideModal(deleteModal, deleteModalContent);
            });

            bindDeleteForms();
        });
    </script>
</x-layout>