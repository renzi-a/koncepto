<x-nav-link />
<style>
  @keyframes fadeIn {
    from { opacity: 0; transform: scale(0.95); }
    to { opacity: 1; transform: scale(1); }
  }

  .animate-fadeIn {
    animation: fadeIn 0.7s ease-out forwards;
  }

  @keyframes bounceScale {
    0%, 100% { transform: scale(1) translateY(0); opacity: 1; }
    50% { transform: scale(1.15) translateY(-10%); opacity: 0.8; }
  }

  .animate-bounceScale {
    animation: bounceScale 1.2s ease-in-out infinite;
  }

#loadingOverlay {
  transition: opacity 0.3s ease;
  opacity: 0;
  pointer-events: none;
  display: none;
}

#loadingOverlay.active {
  display: flex;
  opacity: 1;
  pointer-events: auto;
}


  #loadingOverlay svg {
    width: 48px;
    height: 48px;
  }

  #loadingOverlay span {
    font-size: 1.25rem;
  }
</style>

@php $hasItems = $cartItems && $cartItems->count(); @endphp
<script>
    const userHomeUrl = "{{ route('user.home') }}";
</script>

<div class="bg-gray-100 py-10 min-h-screen">
    <div class="max-w-6xl mx-auto bg-white p-6 rounded-xl shadow-md" id="cartContainer">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Your Cart</h2>
            <a href="{{ route('user.home') }}" class="text-[#56AB2F] hover:underline font-medium">
                ← Back to Products
            </a>
        </div>

        <div id="cartContent">
        @if($hasItems)
           <div class="space-y-6" id="cartItemsContainer">

    <!-- Select All Checkbox -->
    <div class="flex items-center gap-6 border-b pb-4">
        <input type="checkbox" id="selectAll" checked class="w-5 h-5 text-[#56AB2F]" />
        <label for="selectAll" class="font-semibold text-gray-800">Select All</label>
    </div>

    @foreach($cartItems as $item)
    <div class="flex items-center gap-6 border-b pb-4 cart-item-row">
        <input type="checkbox" class="item-checkbox w-5 h-5 text-[#56AB2F]" checked data-item-id="{{ $item->id }}" />
        <img src="{{ asset('storage/' . $item->product->image) }}" alt="{{ $item->product->productName }}" class="w-24 h-24 object-cover rounded">
        <div class="flex-1">
            <h3 class="font-semibold text-lg text-gray-800">{{ $item->product->productName }}</h3>
            <p class="text-sm text-gray-500">{{ $item->product->brandName }}</p>
            <p class="text-[#56AB2F] font-semibold mt-1">₱{{ number_format($item->product->price, 2) }}</p>
        </div>
        <div class="flex items-center gap-2 justify-center text-center">
            <input type="hidden" id="product-{{ $item->id }}" value="{{ $item->product_id }}">
            <button type="button" onclick="updateQty({{ $item->id }}, -1)" class="px-2 py-1 bg-gray-200 rounded">−</button>
            <input 
                type="number"
                id="qty-{{ $item->id }}"
                value="{{ $item->quantity }}"
                min="1"
                max="{{ $item->product->quantity }}"
                data-max="{{ $item->product->quantity }}"
                data-price="{{ $item->product->price }}"
                class="w-14 text-center border rounded" />
            <button type="button" onclick="updateQty({{ $item->id }}, 1)" class="px-2 py-1 bg-gray-200 rounded">+</button>
        </div>
        <form onsubmit="deleteCartItem(event, {{ $item->id }})" id="delete-form-{{ $item->id }}">
            @csrf
            @method('DELETE')
            <button onclick="openDeleteModal({{ $item->id }})" type="button" class="ml-4" title="Remove item">
                <img src="{{ asset('images/icons/delete.png') }}" alt="Remove" class="w-5 h-5 hover:opacity-80 transition">
            </button>
        </form>
    </div>
    @endforeach
</div>


            <div class="mt-8 flex justify-between items-center" id="cartSummary">
                <p class="text-lg font-semibold text-gray-700">
                    Total:
                    <span id="cart-total" class="text-[#56AB2F] text-2xl">
                        ₱{{ number_format($cartItems->sum(fn($i) => $i->product->price * $i->quantity), 2) }}
                    </span>
                </p>
                <div class="flex gap-3">
                   <a href="{{ route('checkout.show') }}" class="bg-[#56AB2F] text-white font-semibold px-6 py-3 rounded-lg hover:bg-green-700 transition">
                        Checkout
                    </a>
                </div>
            </div>

        @else
            <p class="text-gray-500 text-center py-10" id="emptyMessage">Your cart is empty.</p>
            <div class="text-center" id="continueShopping">
                <a href="{{ route('user.home') }}" class="inline-block mt-4 bg-[#56AB2F] text-white px-6 py-3 rounded-lg hover:bg-green-700 transition">
                    Continue Shopping
                </a>
            </div>
        @endif
        </div>
    </div>
</div>

<div id="deleteModal" class="fixed inset-0 z-50 bg-black bg-opacity-50 flex items-center justify-center hidden">
    <div class="bg-white w-full max-w-sm p-6 rounded-xl shadow-xl transform scale-95 opacity-0 transition-all duration-300" id="deleteModalBox">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Remove Item</h2>
        <p class="text-gray-600 mb-6">Are you sure you want to remove this item from your cart?</p>
        <div class="flex justify-center gap-3">
            <button onclick="closeDeleteModal()" class="px-4 py-2 rounded-md border text-gray-600 hover:bg-gray-100 transition">Cancel</button>
            <button id="confirmDeleteBtn" class="px-4 py-2 rounded-md bg-red-500 text-white hover:bg-red-600 transition">Yes, remove</button>
        </div>
    </div>
</div>

<div id="loadingOverlay" style="display: none;" class="fixed inset-0 z-50 bg-black bg-opacity-40 items-center justify-center">
  <div class="bg-white rounded-xl p-8 shadow-lg flex flex-col items-center space-y-4 animate-fadeIn">
    <div id="spinnerIcon" class="w-12 h-12 text-[#56AB2F] animate-spin">
      <svg class="w-12 h-12" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
      </svg>
    </div>
    <div id="checkIcon" class="hidden text-green-500">
      <svg class="w-12 h-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
      </svg>
    </div>
    <span id="overlayMessage" class="text-[#56AB2F] font-semibold text-lg">Removing item...</span>
  </div>
</div>

<x-footer />

<script>
let deleteItemId = null;

function openDeleteModal(itemId) {
    deleteItemId = itemId;
    const modal = document.getElementById('deleteModal');
    const box = document.getElementById('deleteModalBox');
    modal.classList.remove('hidden');
    setTimeout(() => {
        box.classList.remove('scale-95', 'opacity-0');
        box.classList.add('scale-100', 'opacity-100');
    }, 50);
}

function closeDeleteModal() {
    const modal = document.getElementById('deleteModal');
    const box = document.getElementById('deleteModalBox');
    box.classList.add('scale-95', 'opacity-0');
    box.classList.remove('scale-100', 'opacity-100');
    setTimeout(() => modal.classList.add('hidden'), 300);
}

document.getElementById('confirmDeleteBtn').addEventListener('click', () => {
    if (!deleteItemId) return;

    closeDeleteModal();

    const overlay = document.getElementById('loadingOverlay');
    const spinner = document.getElementById('spinnerIcon');
    const check = document.getElementById('checkIcon');
    const overlayText = document.getElementById('overlayMessage');

    overlay.style.display = 'flex';
    requestAnimationFrame(() => {
        overlay.classList.add('active');
    });

    fetch(`/cart/remove/${deleteItemId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(() => {
        const itemDiv = document.querySelector(`#delete-form-${deleteItemId}`)?.closest('.flex.items-center');
        if (itemDiv) itemDiv.remove();

        recalculateTotal();

        spinner.classList.add('hidden');
        check.classList.remove('hidden');
        overlayText.textContent = 'Successfully removed';

        setTimeout(() => {
            overlay.classList.remove('active');
            setTimeout(() => {
                overlay.style.display = 'none';
                spinner.classList.remove('hidden');
                check.classList.add('hidden');
                overlayText.textContent = 'Removing item...';

                const cartItemsContainer = document.getElementById('cartItemsContainer');
                if (!cartItemsContainer || cartItemsContainer.children.length === 0) {
                    const cartContent = document.getElementById('cartContent');
                    cartContent.innerHTML = `
                        <p class="text-gray-500 text-center py-10" id="emptyMessage">Your cart is empty.</p>
                        <div class="text-center" id="continueShopping">
                            <a href="${window.location.origin}/user/home" class="inline-block mt-4 bg-[#56AB2F] text-white px-6 py-3 rounded-lg hover:bg-green-700 transition">
                                Continue Shopping
                            </a>
                        </div>
                    `;
                }
            }, 300);
        }, 1200);
    })
    .catch(err => {
        console.error('Delete failed:', err);
        overlay.classList.remove('active');
        setTimeout(() => overlay.style.display = 'none', 300);
        alert('Failed to delete item.');
    });
});


function updateQty(id, change) {
    const qtyInput = document.getElementById(`qty-${id}`);
    const productInput = document.getElementById(`product-${id}`);
    const max = parseInt(qtyInput.dataset.max);
    const min = 1;
    let value = parseInt(qtyInput.value);

    if (!isNaN(value)) {
        value = Math.min(max, Math.max(min, value + change));
        qtyInput.value = value;

        fetch("{{ route('cart.update') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name=csrf-token]').getAttribute('content')
            },
            body: JSON.stringify({
                items: {
                    [id]: {
                        product_id: productInput.value,
                        quantity: value
                    }
                }
            })
        })
        .then(response => response.json())
        .then(data => {
    if (data.success) {
        const checkbox = document.querySelector(`.item-checkbox[data-item-id="${id}"]`);
        if (checkbox && checkbox.checked) {
            recalculateTotal();
        }
    } else {
        alert(data.message || 'Update failed.');
    }
})
        .catch(error => console.error('Error:', error));
    }
}


function recalculateTotal() {
    let total = 0;
    document.querySelectorAll('.item-checkbox').forEach(checkbox => {
        if (checkbox.checked) {
            const itemId = checkbox.dataset.itemId;
            const qtyInput = document.getElementById(`qty-${itemId}`);
            if (qtyInput) {
                const qty = parseInt(qtyInput.value);
                const price = parseFloat(qtyInput.dataset.price);
                if (!isNaN(qty) && !isNaN(price)) {
                    total += qty * price;
                }
            }
        }
    });

    const totalElement = document.getElementById('cart-total');
    if (totalElement) {
        totalElement.textContent = `₱${total.toLocaleString(undefined, { minimumFractionDigits: 2 })}`;
    }
}

function updateSelectAllState() {
    const allCheckboxes = document.querySelectorAll('.item-checkbox');
    const allChecked = [...allCheckboxes].every(cb => cb.checked);
    document.getElementById('selectAll').checked = allChecked;
}

document.addEventListener('DOMContentLoaded', () => {
    const selectAllCheckbox = document.getElementById('selectAll');
    const itemCheckboxes = document.querySelectorAll('.item-checkbox');

    selectAllCheckbox.addEventListener('change', () => {
        const checked = selectAllCheckbox.checked;
        itemCheckboxes.forEach(cb => cb.checked = checked);
        recalculateTotal();
    });

    itemCheckboxes.forEach(cb => {
        cb.addEventListener('change', () => {
            updateSelectAllState();
            recalculateTotal();
        });
    });
});

document.querySelectorAll('input[id^="qty-"]').forEach(qtyInput => {
    qtyInput.addEventListener('input', () => {
        const itemId = qtyInput.id.replace('qty-', '');
        const checkbox = document.querySelector(`.item-checkbox[data-item-id="${itemId}"]`);
        if (checkbox && checkbox.checked) {
            recalculateTotal();
        }
    });
});

window.onload = () => {
    recalculateTotal();
};

</script>
