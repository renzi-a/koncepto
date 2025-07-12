<?php

use App\Http\Controllers\SchoolController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\AdminChatController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdsController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserManagementController;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use App\Http\Middleware\IsAdmin;
use App\Http\Middleware\IsUser;

// Public Routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/login', fn() => view('login'))->name('login');
Route::post('/login', [LoginController::class, 'store'])->name('login.store');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Admin Routes
Route::middleware(['auth', IsAdmin::class])->group(function () {
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.dashboard');
    Route::get('/admin/ads', [AdsController::class, 'index'])->name('admin.ads');


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
        Route::get('/{school}', [UserManagementController::class, 'index'])->name('index');
        Route::get('/schools/user/create/{school}', [UserManagementController::class, 'create'])->name('create');
        Route::post('/{school}', [UserManagementController::class, 'store'])->name('store');
        Route::get('/schools/user/edit/{user}', [UserManagementController::class, 'edit'])->name('edit'); // ✅ fixed here
        Route::put('/{user}', [UserManagementController::class, 'update'])->name('update');
        Route::delete('/{user}', [UserManagementController::class, 'destroy'])->name('destroy');
    });

});

// USER
Route::middleware(['auth', IsUser::class])->group(function () {
    Route::get('/user/home2', [UserController::class, 'index'])->name('user.home');
    Route::get('/user/view_product/{product}', [UserController::class, 'viewProduct'])->name('view_product');
    Route::get('/notifications', [UserController::class, 'showNotifications'])->name('notifications');
    Route::post('/notifications/clear', [UserController::class, 'clearNotifications'])->name('notifications.clear');
    Route::post('/notifications/mark-read', function () {
        Notification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['status' => 'marked']);
    })->middleware('auth')->name('notifications.markRead');



    // Cart
    Route::get('/user/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/user/cart/update', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/user/cart/remove/{id}', [CartController::class, 'remove'])->name('cart.remove');
    Route::get('/user/checkout', [CartController::class, 'checkout'])->name('checkout');

    // Chat
    Route::get('/user/chat-popup', [ChatController::class, 'popup'])->name('user.chat.popup');
    Route::get('/user/chat', [ChatController::class, 'full'])->name('user.chat.full');
    Route::post('/user/chat/send', [ChatController::class, 'send'])->name('user.chat.send');
    Route::get('/chat/messages', [ChatController::class, 'fetchMessages'])->name('user.chat.messages');
    Route::post('/user/chat/typing', [ChatController::class, 'typing'])->name('user.chat.typing');
    Route::get('/user/chat/typing-check', [ChatController::class, 'checkTyping'])->name('user.chat.checkTyping');


});





