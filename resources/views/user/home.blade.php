<x-nav-link/>

<div class="bg-white">
    <div class="mx-auto max-w-screen-2xl px-4 sm:px-6 lg:px-8 py-16">
        <x-ads/>

        <div class="flex flex-col lg:flex-row gap-8 lg:gap-12 mt-10">
            <aside class="w-full lg:w-1/4 xl:w-1/5 space-y-8 p-6 bg-gray-50 rounded-lg shadow-sm">
                <h3 class="text-xl font-bold text-gray-800 border-b pb-4 mb-4">Product Categories</h3>
                <form id="filter-form" method="GET" action="{{ route('user.home') }}" class="space-y-4">
                    @foreach($categories as $category)
                        <label class="flex items-center gap-3 text-gray-700 hover:text-green-700 transition duration-200 cursor-pointer">
                            <input type="radio" name="category_id" value="{{ $category->id }}"
                                   class="form-radio h-4 w-4 text-[#56AB2F] accent-[#56AB2F]"
                                   {{ request('category_id') == $category->id ? 'checked' : '' }}>
                            <span class="text-base font-medium">{{ $category->categoryName }}</span>
                        </label>
                    @endforeach

                    <label class="flex items-center gap-3 text-gray-700 hover:text-green-700 transition duration-200 cursor-pointer">
                        <input type="radio" name="category_id" value=""
                               class="form-radio h-4 w-4 text-[#56AB2F] accent-[#56AB2F]"
                               {{ request('category_id') == '' ? 'checked' : '' }}>
                        <span class="text-base font-medium">All Categories</span>
                    </label>
                </form>

                <div class="pt-6 border-t mt-6">
                    <a href="{{ route('user.custom-order') }}"
                       class="w-full flex items-center justify-center px-6 py-3 bg-[#56AB2F] text-white font-bold rounded-lg shadow-md hover:bg-green-700 transition-transform duration-200 ease-in-out transform hover:scale-105 text-lg">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                             xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                             d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                        Custom Order
                    </a>
                </div>
            </aside>

            <div class="flex-1" id="products-container">
                @include('user.partials.products', ['products' => $products])
            </div>
        </div>
    </div>
</div>

<x-footer/>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {

    function fetchProducts(url) {
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'html',
            success: function(data) {
                let newProducts = $(data).find('#products-container').html();
                $('#products-container').html(newProducts);
                window.history.pushState(null, null, url);

                // Smooth dynamic scroll to top of products
                $('html, body').animate(
                    { scrollTop: $('#products-container').offset().top - 50 },
                    800, // 0.8 seconds for slightly slower, smooth effect
                    'swing'
                );
            }
        });
    }

    $(document).on('click', '#products-container .pagination a', function(e) {
        e.preventDefault();
        let url = $(this).attr('href');
        fetchProducts(url);
    });

    $('#filter-form').on('submit', function(e) {
        e.preventDefault();
        let url = $(this).attr('action') + '?' + $(this).serialize();
        fetchProducts(url);
    });

    $('#filter-form input').on('change', function() {
        $('#filter-form').submit();
    });

});
</script>