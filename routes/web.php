<?php

use App\Http\Controllers\SchoolController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserManagementController;


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

    Route::prefix('admin/product')->name('product.')->group(function () {
        Route::get('/', [ProductController::class, 'index'])->name('index');
        Route::get('/create', [ProductController::class, 'create'])->name('create');
        Route::post('/', [ProductController::class, 'store'])->name('store');
        Route::get('/{product}/edit', [ProductController::class, 'edit'])->name('edit');
        Route::put('/{product}', [ProductController::class, 'update'])->name('update');
        Route::delete('/{product}', [ProductController::class, 'destroy'])->name('destroy');
    });
});

// Route::get('/admin/schools', [SchoolController::class, 'index'])->name('admin.schools.index');

//         Route::get('/', [SchoolController::class, 'index'])->name('index');
//         // Route::get('/{school}', [SchoolController::class, 'show'])->name('show');
//         // Route::get('/{school}/contract/create', [SchoolController::class, 'createContract'])->name('contract.create');
//         // Route::post('/{school}/contract', [SchoolController::class, 'storeContract'])->name('contract.store');


//     // Route::prefix('admin/users')->name('admin.users.')->group(function () {
//     //     Route::get('/{school}', [UserManagementController::class, 'index'])->name('index');
//     //     Route::get('/edit/{user}', [UserManagementController::class, 'edit'])->name('edit');
//     //     Route::put('/{user}', [UserManagementController::class, 'update'])->name('update');
//     //     Route::delete('/{user}', [UserManagementController::class, 'destroy'])->name('destroy');
//     // });

// User Routes
// Route::middleware(['auth', IsUser::class])->group(function () {
//     Route::get('/user/home2', [UserController::class, 'index'])->name('user.home');
// });
