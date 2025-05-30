<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\UpdateController;

Route::get('/', function () {
    return view('dashboard');
})->name('dashboard');

// 注文一覧ページ
Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');

// 新規注文登録ページ
Route::get('/orders/new_order', [OrderController::class, 'newOrder'])->name('orders.new_order');
Route::post('/orders/store', [OrderController::class, 'order_store'])->name('orders.order_store');

// 注文詳細ページ
Route::get('/order_details/{order_id}', [OrderController::class, 'orderDetails'])->name('orders.order_details');

// キャンセルページ
Route::get('/order_details/{order_id}/cancel', [OrderController::class, 'showCancelForm'])->name('orders.cancel');
Route::post('/order_details/cancel', [OrderController::class, 'processCancel'])->name('orders.processCancel');

// 新規納品ページ
Route::post('/orders/delivery_prepare', [DeliveryController::class, 'prepare'])->name('orders.delivery_prepare');
Route::post('/orders/delivery_store', [DeliveryController::class, 'store'])->name('orders.delivery_store');

// 納品一覧ページ
Route::get('/deliveries', [DeliveryController::class, 'index'])->name('deliveries.index');

// 納品詳細ページ
Route::get('/deliveries/{delivery_id}', [DeliveryController::class, 'show'])->name('deliveries.details');

// 返品ページ
Route::get('/deliveries/{delivery_id}/return', [DeliveryController::class, 'showReturnForm'])->name('deliveries.return_form');
Route::post('/deliveries/return', [DeliveryController::class, 'processReturn'])->name('deliveries.processReturn');

// 顧客一覧ページ
Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');

// 顧客更新ページ
Route::get('/customers/edit', [UpdateController::class, 'update'])->name('customers.edit');