<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CustomOrder;
use App\Models\CustomOrderItem;
use Illuminate\Support\Facades\Auth;
use App\Models\OrderHistory;
use Illuminate\Support\Facades\Storage;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class CustomOrderController extends Controller
{
    // Index page
    public function index()
    {
        return view('user.order.custom');
    }

    // Helper to generate daily sequential code
    private function generateDailyOrderCode()
    {
        $date = date('Ymd'); // YYYYMMDD

        // Get last order today
        $lastOrder = CustomOrder::whereDate('created_at', now()->toDateString())
            ->latest('id')
            ->first();

        if ($lastOrder && $lastOrder->order_code) {
            $lastNumber = (int) substr($lastOrder->order_code, strrpos($lastOrder->order_code, '-') + 1);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return 'CUS-' . $date . '-' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    // Store new custom order
    public function store(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string',
            'items.*.brand' => 'nullable|string',
            'items.*.unit' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.photo' => 'nullable|file|image|max:5120',
            'items.*.description' => 'nullable|string',
        ]);

        $order = CustomOrder::create([
            'user_id' => Auth::id(),
            'order_code' => $this->generateDailyOrderCode(),
        ]);

        foreach ($request->items as $item) {
            $photoPath = null;
            if (isset($item['photo']) && $item['photo'] instanceof \Illuminate\Http\UploadedFile) {
                $photoPath = $item['photo']->store('custom-orders', 'public');
            }

            $order->items()->create([
                'name' => $item['name'],
                'brand' => $item['brand'],
                'unit' => $item['unit'],
                'quantity' => $item['quantity'],
                'photo' => $photoPath,
                'description' => $item['description'] ?? null,
            ]);
        }

        return redirect()->route('user.order.index')->with('success', 'Custom order submitted successfully.');
    }

    // Cancel an order
    public function cancel(Request $request, CustomOrder $order)
    {
        if ($order->user_id !== Auth::id()) abort(403);

        if (in_array($order->status, ['gathering', 'to be delivered', 'delivered'])) {
            return redirect()->back()->with('error', 'You cannot cancel this order at this stage.');
        }

        $request->validate(['reason' => 'nullable|string|max:500']);

        $items = $order->items->map(function ($item) {
            return [
                'name' => $item->name,
                'brand' => $item->brand,
                'unit' => $item->unit,
                'quantity' => $item->quantity,
                'photo' => $item->photo,
                'description' => $item->description,
            ];
        });

        OrderHistory::create([
            'original_order_id' => $order->id,
            'user_id' => $order->user_id,
            'status' => 'cancelled',
            'reason' => $request->reason,
            'order_type' => 'custom',
            'items' => $items,
        ]);

        $order->items()->delete();
        $order->delete();

        return redirect()->route('user.order-history')->with('success', 'Order cancelled successfully.');
    }

    // Show order details
    public function show(Request $request, CustomOrder $order)
    {
        if ($order->user_id !== auth::id()) abort(403);

        $search = $request->input('search');
        $items = collect($order->items);

        if ($search) {
            $items = $items->filter(function ($item) use ($search) {
                return stripos($item['name'], $search) !== false ||
                       stripos($item['brand'] ?? '', $search) !== false ||
                       stripos($item['description'] ?? '', $search) !== false;
            });
        }

        if (in_array($order->status, ['quoted', 'approved'])) {
            return view('user.order.quoted', [
                'order' => $order,
                'items' => $items,
                'search' => $search,
            ]);
        }

        $perPage = 10;
        $page = request('page', 1);
        $paginatedItems = new \Illuminate\Pagination\LengthAwarePaginator(
            $items->forPage($page, $perPage),
            $items->count(),
            $perPage,
            $page,
            ['path' => url()->current()]
        );

        return view('user.order.show', [
            'order' => $order,
            'items' => $paginatedItems,
            'search' => $search,
        ]);
    }

    // Edit order
    public function edit(CustomOrder $order)
    {
        if ($order->user_id !== Auth::id()) abort(403);

        $order->load('items');

        return view('user.order.custom-edit', compact('order'));
    }

    // Update order
    public function update(Request $request, CustomOrder $order)
    {
        if ($order->user_id !== Auth::id()) abort(403);

        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id' => 'nullable|integer|exists:custom_order_items,id',
            'items.*.item_no' => 'nullable|string',
            'items.*.name' => 'required|string',
            'items.*.brand' => 'nullable|string',
            'items.*.unit' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.description' => 'nullable|string',
            'items.*.photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $existingIds = $order->items->pluck('id')->toArray();
        $submittedIds = [];

        foreach ($validated['items'] as $index => $itemData) {
            $photo = null;

            if (!empty($itemData['id'])) {
                $item = $order->items()->find($itemData['id']);
                $submittedIds[] = $itemData['id'];

                if (!$item) continue;

                if ($request->hasFile("items.$index.photo")) {
                    if ($item->photo && Storage::disk('public')->exists($item->photo)) {
                        Storage::disk('public')->delete($item->photo);
                    }
                    $photo = $request->file("items.$index.photo")->store('custom-order-images', 'public');
                }

                $item->update([
                    'item_no' => $itemData['item_no'] ?? $item->item_no,
                    'name' => $itemData['name'],
                    'brand' => $itemData['brand'],
                    'unit' => $itemData['unit'],
                    'quantity' => $itemData['quantity'],
                    'description' => $itemData['description'],
                    'photo' => $photo ?? $item->photo,
                ]);
            } else {
                if ($request->hasFile("items.$index.photo")) {
                    $photo = $request->file("items.$index.photo")->store('custom-order-images', 'public');
                }

                $newItem = $order->items()->create([
                    'item_no' => $itemData['item_no'] ?? ($index + 1),
                    'name' => $itemData['name'],
                    'brand' => $itemData['brand'],
                    'unit' => $itemData['unit'],
                    'quantity' => $itemData['quantity'],
                    'description' => $itemData['description'],
                    'photo' => $photo,
                ]);

                $submittedIds[] = $newItem->id;
            }
        }

        $itemsToDelete = array_diff($existingIds, $submittedIds);
        foreach ($itemsToDelete as $id) {
            $item = $order->items()->find($id);
            if ($item) {
                if ($item->photo && Storage::disk('public')->exists($item->photo)) {
                    Storage::disk('public')->delete($item->photo);
                }
                $item->delete();
            }
        }

        return redirect()->route('user.order.index')->with('success', 'Order updated successfully.');
    }

    // Quoted orders
    public function quotedOrders()
    {
        $orders = CustomOrder::where('user_id', Auth::id())
            ->where('status', 'quoted')
            ->with('items')
            ->latest()
            ->get();

        return view('user.order.quoted', compact('orders'));
    }

    public function showQuotedOrder($id)
    {
        $order = CustomOrder::where('user_id', Auth::id())
            ->where('status', 'quoted')
            ->with('items')
            ->findOrFail($id);

        return view('user.order.quoted-show', compact('order'));
    }

    public function downloadQuotedOrderPdf($id)
    {
        $order = CustomOrder::where('user_id', Auth::id())
            ->where('status', 'quoted')
            ->with('items')
            ->findOrFail($id);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('user.order.quoted-pdf', compact('order'));

        return $pdf->stream("CustomOrder-{$order->order_code}-Quotation.pdf");
    }

    public function approve(Request $request, $id)
    {
        $request->validate([
            'payment_date' => [
                'required',
                'date',
                'after_or_equal:today',
                function ($attribute, $value, $fail) {
                    $maxDate = \Carbon\Carbon::now()->addMonthNoOverflow()->endOfMonth();
                    if (\Carbon\Carbon::parse($value)->gt($maxDate)) {
                        $fail('The payment date must be within today and the end of next month.');
                    }
                }
            ],
        ]);

        $order = CustomOrder::where('user_id', Auth::id())
            ->where('status', 'quoted')
            ->findOrFail($id);

        DB::transaction(function () use ($order, $request) {
            $order->status = 'approved';
            $order->save();

            Payment::create([
                'order_id' => $order->id,
                'order_type' => 'custom_order',
                'payment_date' => $request->payment_date,
            ]);
        });

        return redirect()->route('user.order.index')->with('success', 'Order approved successfully.');
    }

    public function gatherView($id)
    {
        $order = CustomOrder::with(['items', 'user.school'])->findOrFail($id);
        $items = $order->items;

        return view('user.order.gather', compact('order', 'items'));
    }

    public function gatherPdf($id)
    {
        $order = CustomOrder::with(['items', 'user.school'])->findOrFail($id);
        $items = $order->items;

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('user.order.gather-pdf', compact('order', 'items'));

        return $pdf->stream("CustomOrder-{$order->order_code}-Gathered.pdf");
    }
}
