<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('index');
});

Route::get('/checkout', function () {
    return view('order');
});

Route::post('/checkout/process', [App\Http\Controllers\CheckoutController::class, 'process']);
Route::post('/checkout/confirm', [App\Http\Controllers\CheckoutController::class, 'confirm']);

// Admin Routes
Route::get('/admin/login', [App\Http\Controllers\AdminController::class, 'index']); // For now, index handles login check
Route::post('/admin/login', [App\Http\Controllers\AdminController::class, 'login']);
Route::get('/admin/logout', [App\Http\Controllers\AdminController::class, 'logout']);
Route::get('/admin', [App\Http\Controllers\AdminController::class, 'index']);
Route::get('/admin/orders', [App\Http\Controllers\AdminController::class, 'orders']);
Route::get('/admin/customers', [App\Http\Controllers\AdminController::class, 'customers']);
Route::get('/admin/paid/{id}', [App\Http\Controllers\AdminController::class, 'markPaid']);
Route::get('/admin/completed/{id}', [App\Http\Controllers\AdminController::class, 'markCompleted']);
Route::get('/admin/reject/{id}', [App\Http\Controllers\AdminController::class, 'markRejected']);
Route::get('/admin/delete/{id}', [App\Http\Controllers\AdminController::class, 'deleteOrder']);
Route::get('/admin/invoice/{id}', [App\Http\Controllers\AdminController::class, 'invoice']);
Route::get('/admin/invoice/{id}/download', [App\Http\Controllers\AdminController::class, 'downloadInvoice']);
Route::get('/admin/export/orders', [App\Http\Controllers\AdminController::class, 'exportOrders']);
Route::get('/admin/settings', [App\Http\Controllers\AdminController::class, 'settings']);
Route::post('/admin/settings', [App\Http\Controllers\AdminController::class, 'updateSettings']);
Route::get('/admin/edit/{id}', [App\Http\Controllers\AdminController::class, 'edit']);
Route::post('/admin/update/{id}', [App\Http\Controllers\AdminController::class, 'update']);
Route::post('/admin/batch', [App\Http\Controllers\AdminController::class, 'batchUpdate']);
Route::get('/admin/api/updates', [App\Http\Controllers\AdminController::class, 'getLatestUpdates']);
