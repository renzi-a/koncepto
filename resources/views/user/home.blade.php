<x-nav-link/> {{-- Assuming this includes your header/navigation --}}

<div class="bg-white">
    <div class="mx-auto max-w-screen-2xl px-4 sm:px-6 lg:px-8 py-16">
        <x-ads/> {{-- Your advertisement component --}}

        <div class="flex flex-col lg:flex-row gap-8 lg:gap-12 mt-10">
            {{-- Categories & Custom Order Sidebar --}}
            <aside class="w-full lg:w-1/4 xl:w-1/5 space-y-8 p-6 bg-gray-50 rounded-lg shadow-sm">
                <h3 class="text-xl font-bold text-gray-800 border-b pb-4 mb-4">Product Categories</h3>
                <form method="GET" action="{{ route('user.home') }}" class="space-y-4">
                    @foreach($categories as $category)
                        <label class="flex items-center gap-3 text-gray-700 hover:text-green-700 transition duration-200 cursor-pointer">
                            <input type="radio" name="category_id" value="{{ $category->id }}"
                                class="form-radio h-4 w-4 text-[#56AB2F] accent-[#56AB2F] focus:ring-[#56AB2F]"
                                onchange="this.form.submit()"
                                {{ request('category_id') == $category->id ? 'checked' : '' }}>
                            <span class="text-base font-medium">{{ $category->categoryName }}</span>
                        </label>
                    @endforeach
                    <label class="flex items-center gap-3 text-gray-700 hover:text-green-700 transition duration-200 cursor-pointer">
                        <input type="radio" name="category_id" value=""
                            class="form-radio h-4 w-4 text-[#56AB2F] accent-[#56AB2F] focus:ring-[#56AB2F]"
                            onchange="this.form.submit()"
                            {{ request('category_id') == '' ? 'checked' : '' }}>
                        <span class="text-base font-medium">All Categories</span>
                    </label>
                </form>

                <div class="pt-6 border-t mt-6">
                    <a href="{{ route('user.custom-order') }}"
                       class="w-full flex items-center justify-center px-6 py-3 bg-[#56AB2F] text-white font-bold rounded-lg shadow-md hover:bg-green-700 transition-transform duration-200 ease-in-out transform hover:scale-105 text-lg">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                        Custom Order
                    </a>
                </div>
            </aside>

            {{-- Product Grid --}}
            <div class="flex-1">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    @forelse ($products as $product)
                        <a href="{{ route('view_product', $product->id) }}"
                           class="group relative block bg-white border border-gray-200 p-4 rounded-xl shadow-lg hover:shadow-xl transition-transform duration-220 ease-in-out transform hover:scale-103">
                            <div class="aspect-square w-full rounded-lg bg-gray-100 overflow-hidden">
                                <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->productName }}"
                                    class="h-full w-full object-contain group-hover:opacity-85 transition-opacity duration-200" />
                            </div>
                            <div class="mt-4 text-center">
                                <h3 class="text-lg font-semibold text-gray-800 truncate">{{ $product->productName }}</h3>
                                <p class="mt-1 text-sm text-gray-600">{{ $product->brandName }}</p>
                                <p class="mt-2 text-xl font-extrabold text-green-700">₱{{ number_format($product->price, 2) }}</p>
                            </div>
                        </a>
                    @empty
                        <div class="col-span-full bg-gray-100 p-8 rounded-lg shadow-inner text-center">
                            <p class="text-gray-600 text-lg font-medium mb-4">No products found matching your criteria.</p>
                            <p class="text-gray-500">Try selecting "All Categories" or adjusting your search.</p>
                            <a href="{{ route('user.home', ['category_id' => '']) }}" class="mt-6 inline-block bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-700 transition">
                                View All Products
                            </a>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Pagination --}}
        <div class="mt-12">
            {{ $products->withQueryString()->links() }}
        </div>
    </div>
</div>

<x-footer/> {{-- Your footer component --}}