<?php

use Illuminate\Session\Middleware\StartSession;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use App\Http\Controllers\SchoolController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderHistoryController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\AdminChatController;
use App\Http\Controllers\CustomOrderController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdsController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\UserOrderController;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use App\Http\Middleware\IsAdmin;
use App\Http\Middleware\IsUser;
use Illuminate\Http\Request;

// Public Routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/login', fn() => view('login'))->name('login');
Route::post('/login', [LoginController::class, 'store'])->name('login.store');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Admin Routes
Route::middleware(['auth', IsAdmin::class])->group(function () {
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.dashboard');
    Route::get('/admin/ads', [AdsController::class, 'index'])->name('admin.ads');


    Route::get('/admin/orders', [OrderController::class, 'adminOrders'])->name('admin.orders');
    Route::get('/admin/orders/{id}', [OrderController::class, 'show'])->name('admin.orders.show');
    Route::get('/admin/orders/ajax', [OrderController::class, 'fetchOrders'])->name('admin.orders.ajax');
    Route::post('/admin/orders/update-status', [OrderController::class, 'updateOrderStatus'])->name('admin.orders.updateStatus');
    Route::get('/admin/orders/{order}', [OrderController::class, 'adminShow'])
        ->name('admin.orders.show');
    Route::get('/admin/custom-orders/{customOrder}', [OrderController::class, 'adminCustomShow'])
        ->name('admin.custom-orders.show');
    Route::get('/admin/custom-orders/{id}/quotation', [OrderController::class, 'showQuotation'])
        ->name('admin.custom-orders.quotation');
    Route::post('/admin/custom-orders/{id}/quotation', [OrderController::class, 'saveQuotationPrices'])
        ->name('admin.custom-orders.quotation.save');
    Route::get('/admin/custom-orders/{id}/gather', [OrderController::class, 'gather'])
        ->name('admin.custom-orders.gather');
    Route::get('/admin/custom-orders/{id}/gather-pdf', [OrderController::class, 'gatherPdf'])
        ->name('admin.custom-orders.gather-pdf');
    Route::post('/admin/custom-orders/items/{id}/toggle-gathered', [OrderController::class, 'toggleGathered'])->name('admin.custom-orders.toggle-gathered');


    Route::get('/admin/payment', [PaymentController::class, 'index'])->name('admin.payment');

    // Chat
    Route::prefix('admin/chat')->middleware(['auth'])->group(function () {
        Route::get('/', [AdminChatController::class, 'index'])->name('admin.chat.index');
        Route::get('/{user}', [AdminChatController::class, 'show'])->name('admin.chat.show');
        Route::post('/{user}', [AdminChatController::class, 'send'])->name('admin.chat.send');
        Route::get('/{user}/messages', [AdminChatController::class, 'fetchMessages'])->name('admin.chat.messages');
        Route::post('/{user}/typing', [AdminChatController::class, 'typing'])->name('admin.chat.typing');
        Route::get('/{user}/typing-check', [AdminChatController::class, 'checkTyping'])->name('admin.chat.checkTyping');
    });



    // Product Management
    Route::prefix('admin/product')->name('product.')->group(function () {
        Route::get('/', [ProductController::class, 'index'])->name('index');
        Route::get('/create', [ProductController::class, 'create'])->name('create');
        Route::post('/', [ProductController::class, 'store'])->name('store');
        Route::get('/{product}/edit', [ProductController::class, 'edit'])->name('edit');
        Route::put('/{product}', [ProductController::class, 'update'])->name('update');
        Route::delete('/{product}', [ProductController::class, 'destroy'])->name('destroy');
    });

    // School Management
    Route::prefix('admin/schools')->name('admin.schools.')->group(function () {
        Route::get('/', [SchoolController::class, 'index'])->name('index');
        Route::get('/create', [SchoolController::class, 'create'])->name('create');
        Route::post('/', [SchoolController::class, 'store'])->name('store');
        Route::get('/{school}', [SchoolController::class, 'show'])->name('show');
        Route::get('/{school}/edit', [SchoolController::class, 'edit'])->name('edit');
        Route::put('/{school}', [SchoolController::class, 'update'])->name('update');
        Route::delete('/{school}', [SchoolController::class, 'destroy'])->name('destroy');
    });
    // User Management per School
    Route::prefix('admin/users')->name('admin.users.')->group(function () {
         Route::get('/', [UserManagementController::class, 'all'])->name('index');
        Route::get('/{school}', [UserManagementController::class, 'index'])->name('school');
        Route::get('/schools/user/create/{school}', [UserManagementController::class, 'create'])->name('create');
        Route::post('/{school}', [UserManagementController::class, 'store'])->name('store');
        Route::get('/schools/user/edit/{user}', [UserManagementController::class, 'edit'])->name('edit'); 
        Route::put('/{user}', [UserManagementController::class, 'update'])->name('update');
        Route::delete('/{user}', [UserManagementController::class, 'destroy'])->name('destroy');
       
    });

});

// USER
Route::middleware(['auth', IsUser::class])->group(function () {
    // Dashboard & Profile
    Route::get('/user/dashboard', [UserController::class, 'dashboard'])->name('user.dashboard');
    Route::get('/user/profile', [UserController::class, 'profile'])->name('user.profile');
    Route::put('/user/profile', [UserController::class, 'updateProfile'])->name('user.profile.update');
    Route::get('/user/users', [UserController::class, 'users'])->name('user.users');

    // Product
    Route::get('/user/home', [UserController::class, 'index'])->name('user.home');
    Route::get('/user/view_product/{product}', [UserController::class, 'viewProduct'])->name('view_product');

    // Notifications
    Route::get('/notifications', [UserController::class, 'showNotifications'])->name('notifications');
    Route::post('/notifications/clear', [UserController::class, 'clearNotifications'])->name('notifications.clear');
    Route::post('/notifications/mark-read', [UserController::class, 'markAsRead'])->name('notifications.markRead');

    // Cart & Checkout
    Route::get('/user/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/remove/{id}', [CartController::class, 'remove'])->name('cart.remove');
    Route::get('/checkout', [CartController::class, 'form'])->name('checkout.show');
    Route::post('/checkout', [CartController::class, 'process'])->name('checkout.process');

    // Chat
    Route::get('/user/chat-popup', [ChatController::class, 'popup'])->name('user.chat.popup');
    Route::get('/user/chat', [ChatController::class, 'full'])->name('user.chat.full');
    Route::post('/user/chat/send', [ChatController::class, 'send'])->name('user.chat.send');
    Route::get('/chat/messages', [ChatController::class, 'fetchMessages'])->name('user.chat.messages');
    Route::post('/user/chat/typing', [ChatController::class, 'typing'])->name('user.chat.typing');
    Route::get('/user/chat/typing-check', [ChatController::class, 'checkTyping'])->name('user.chat.checkTyping');
    Route::get('/chat/source', [ChatController::class, 'getChatSource'])->name('user.chat.source');

    // ORDER SECTION
    Route::get('/user/order', [UserOrderController::class, 'index'])->name('user.order.index');
    Route::get('/user/order/{id}', [UserOrderController::class, 'show'])->name('user.order.show');
    Route::get('/user/order-request', [UserOrderController::class, 'orderRequest'])->name('user.order-request');
    Route::get('/user/track-order', [UserOrderController::class, 'trackOrder'])->name('user.track-order');
    Route::get('/user/normal-orders/{order}', [UserOrderController::class, 'show'])->name('user.normal-orders.show');
    Route::post('/user/normal-orders/{order}/cancel', [UserOrderController::class, 'cancel'])->name('user.normal-orders.cancel');

    // ORDER HISTORY
    Route::get('/user/order-history', [OrderHistoryController::class, 'index'])->name('user.order-history');
    Route::get('/user/order-history/{id}', [OrderHistoryController::class, 'show'])->name('user.order-history-show');
    Route::delete('/orders/bulk-delete', [OrderHistoryController::class, 'bulkDelete'])->name('user.orders.bulkDelete');
    Route::delete('/orders/{id}', [OrderHistoryController::class, 'destroy'])->name('user.orders.destroy');

    // CUSTOM ORDER
    Route::get('/user/custom-order', [CustomOrderController::class, 'index'])->name('user.custom-order');
    Route::post('/custom-orders', [CustomOrderController::class, 'store'])->name('custom-orders.store');
    Route::post('user/custom-orders/{order}/cancel', [CustomOrderController::class, 'cancel'])->name('custom-orders.cancel');
    Route::get('/user/custom-orders/{order}', [CustomOrderController::class, 'show'])->name('user.custom-orders.show'); 
    Route::get('user/custom-orders/{order}/edit', [CustomOrderController::class, 'edit'])->name('custom-orders.edit');
    Route::put('/custom-orders/{order}', [CustomOrderController::class, 'update'])->name('custom-orders.update');
    Route::get('/orders/quoted', [CustomOrderController::class, 'quotedOrders'])->name('user.order.quoted');
    Route::get('/orders/quoted/{id}', [CustomOrderController::class, 'showQuotedOrder'])->name('user.order.quoted.show');
    Route::get('/orders/quoted/{id}/pdf', [CustomOrderController::class, 'downloadQuotedOrderPdf'])->name('user.order.quoted.pdf');
    Route::put('/user/custom-orders/{id}/approve', [CustomOrderController::class, 'approve'])
        ->name('user.custom-orders.approve');
    Route::get('/user/custom-orders/{customOrder}/gather', [CustomOrderController::class, 'gatherView'])
        ->name('user.custom-orders.gather');
    Route::get('/user/custom-orders/{id}/gather-pdf', [CustomOrderController::class, 'gatherPdf'])
        ->name('user.order.gather-pdf');

});


