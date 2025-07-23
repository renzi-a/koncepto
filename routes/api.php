<?php

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Models\Orders;
use App\Models\OrderDetail;
use App\Models\CustomOrder;
use App\Models\CustomOrderItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\Api\AdminChatApiController;
use App\Http\Controllers\Api\OrderApiController;
use App\Http\Controllers\Api\SchoolApiController;
use App\Http\Controllers\Api\DeliveryController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/logout', function (Request $request) {
    if ($request->user()) {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully'], 200);
    }
    return response()->json(['message' => 'No active session to log out from'], 401);
})->middleware('auth:sanctum');

Route::post('/login', function (Request $request) {
    $user = User::where('email', $request->email)->first();
    if (! $user || ! \Illuminate\Support\Facades\Hash::check($request->password, $user->password)){
        return response()->json(['message' => 'Invalid credentials'], 401);
    }
    $token = $user->createToken('mobile', ['*'], now()->addMinutes(60))->plainTextToken;
    return response()->json([
        'token' => $token,
        'user' => $user,
    ]);
});

Route::get('/homescreen', function (Request $request) {
    $year = $request->query('year');
    $startDate = $year ? Carbon::createFromDate($year)->startOfYear() : Carbon::now()->startOfYear();
    $endDate = $year ? Carbon::createFromDate($year)->endOfYear() : Carbon::now()->endOfYear();
    $pendingOrders = Orders::where('status', 'new')
        ->whereBetween('created_at', [$startDate, $endDate])
        ->count();
    $completedOrders = Orders::where('status', 'delivered')
        ->whereBetween('created_at', [$startDate, $endDate])
        ->count();
    $orderRevenue = OrderDetail::whereHas('order', function ($query) use ($startDate, $endDate) {
        $query->where('status', 'delivered')
              ->whereBetween('created_at', [$startDate, $endDate]);
    })->sum(DB::raw('price * quantity'));
    $customPending = CustomOrder::whereIn('status', [
        'to_be_quoted', 'quoted', 'approved', 'gathering'
    ])->whereBetween('created_at', [$startDate, $endDate])->count();
    $customCompleted = CustomOrder::where('status', 'delivered')
        ->whereBetween('created_at', [$startDate, $endDate])
        ->count();
    $customRevenue = CustomOrderItem::whereHas('customOrder', function ($query) use ($startDate, $endDate) {
        $query->where('status', 'delivered')
              ->whereBetween('created_at', [$startDate, $endDate]);
    })->sum('total_price');
    $totalRevenue = $orderRevenue + $customRevenue;
    return response()->json([
        'pendingOrders' => $pendingOrders,
        'completedOrders' => $completedOrders,
        'customPending' => $customPending,
        'customCompleted' => $customCompleted,
        'totalRevenue' => $totalRevenue
    ]);
});

Route::prefix('admin')->name('api.admin.')->middleware('auth:sanctum')->group(function () {
    // Admin Chat
    Route::prefix('chat')->group(function () {
        Route::get('/users', [AdminChatApiController::class, 'index']);
        Route::get('/messages/{userId}', [AdminChatApiController::class, 'fetchMessages']);
        Route::post('/send/{receiverId}', [AdminChatApiController::class, 'send']);
        Route::post('/typing/{userId}', [AdminChatApiController::class, 'typing']);
        Route::get('/checkTyping/{userId}', [AdminChatApiController::class, 'checkTyping']);
    });

    // School routes
    Route::get('/schools', [SchoolApiController::class, 'index'])->name('schools');

    // Orders and custom orders
    Route::get('/orders', [OrderApiController::class, 'adminOrders'])->name('orders');
    Route::get('/orders/fetch', [OrderApiController::class, 'fetchOrders'])->name('orders.fetch');
    Route::get('/orders/{id}', [OrderApiController::class, 'show'])->name('orders.show');
    Route::post('/update-order-status', [OrderApiController::class, 'updateOrderStatus'])->name('orders.updateStatus');
    Route::get('/orders/{id}/details', [OrderApiController::class, 'adminShow'])->name('orders.details');
    
    Route::get('/custom-orders/{id}/details', [OrderApiController::class, 'adminCustomShow'])->name('custom-orders.details');
    Route::get('/custom-orders/{orderId}/quotation', [OrderApiController::class, 'showQuotation'])->name('custom-orders.quotation');
    Route::post('/custom-orders/{orderId}/quotation/save', [OrderApiController::class, 'saveQuotationPrices'])->name('custom-orders.saveQuotationPrices');
    Route::get('/custom-orders/{id}/gather', [OrderApiController::class, 'gather'])->name('custom-orders.gather');
    Route::post('/custom-order-items/{id}/toggle-gathered', [OrderApiController::class, 'toggleGathered'])->name('custom-order-items.toggleGathered');

    // Delivery routes
    Route::get('/orders/{id}/destination', [DeliveryController::class, 'getOrderDestination'])->name('orders.destination');
    Route::post('/delivery/{orderId}/update-location', [DeliveryController::class, 'updateLocation'])->name('delivery.updateLocation');
});


