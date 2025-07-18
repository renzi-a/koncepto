<?php

namespace App\Http\Controllers;

use App\Models\Payment;


use Illuminate\Http\Request;

class PaymentController extends Controller
{
     public function store(Request $request)
{
    $request->validate([
        'order_id' => 'required|integer',
        'order_type' => 'required|string|in:order,custom',
        'payment_date' => 'required|date|after_or_equal:today|before_or_equal:' . now()->addMonth()->toDateString(),
    ]);

    Payment::create([
        'order_id' => $request->order_id,
        'order_type' => $request->order_type,
        'payment_date' => $request->payment_date,
    ]);

    return back()->with('success', 'Payment date set successfully.');
}


}
