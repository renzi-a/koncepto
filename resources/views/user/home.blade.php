<x-nav-link/>
    <div class="bg-white">
            <div class="mx-auto max-w-screen-2xl px-6 py-16">
                <x-ads/>

                <div class="flex gap-12 flex-col lg:flex-row">
                    <aside class="w-full max-w-xs space-y-6">
                        <h3 class="text-lg font-bold text-gray-800">Categories</h3>
                        <form method="GET" action="{{ route('user.home') }}" class="space-y-3">
                            @foreach($categories as $category)
                                <label class="flex items-center gap-2">
                                    <input type="radio" name="category_id" value="{{ $category->id }}"
                                        class="accent-[#56AB2F]"
                                        onchange="this.form.submit()"
                                        {{ request('category_id') == $category->id ? 'checked' : '' }}>
                                    {{ $category->categoryName }}
                                </label>
                            @endforeach
                            <label class="flex items-center gap-2">
                                <input type="radio" name="category_id" value=""
                                    class="accent-[#56AB2F]"
                                    onchange="this.form.submit()"
                                    {{ request('category_id') == '' ? 'checked' : '' }}>
                                All Categories
                            </label>
                        </form>

                        <div class="pt-3">
                            <a href="{{ route('user.custom-order') }}" class="w-full block text-center bg-[#56AB2F] text-white font-semibold py-2 rounded-lg shadow hover:bg-green-700 transition">
                                Custom Order
                            </a>
                        </div>
                    </aside>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 flex-1">
                        @forelse ($products as $product)
                            <a href="{{ route('view_product', $product->id) }}"
                            class="group relative block bg-white p-2 rounded-lg shadow-sm hover:shadow-md transition">
                                <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->productName }}"
                                    class="aspect-square w-full rounded-md bg-gray-200 object-cover group-hover:opacity-75" />
                                <div class="mt-4 flex justify-between">
                                    <div>
                                        <h3 class="text-sm font-medium text-gray-800">{{ $product->productName }}</h3>
                                        <p class="mt-1 text-sm text-gray-500">{{ $product->brandName }}</p>
                                    </div>
                                    <p class="text-sm font-bold text-gray-900">₱{{ number_format($product->price, 2) }}</p>
                                </div>
                            </a>
                        @empty
                            <p class="text-gray-500">No products found.</p>
                        @endforelse
                    </div>
                </div>

                <div class="mt-10">
                    {{ $products->withQueryString()->links() }}
                </div>
            </div>
        </div>
    <x-footer/>