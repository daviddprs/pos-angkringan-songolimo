<?php

use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

// Redirect root ke halaman menu
Route::get('/', fn () => redirect()->route('menu.index'));

// --- Halaman Pesan Menu (Pelanggan / Kasir) ---
Route::get('/menu', [OrderController::class, 'index'])->name('menu.index');
Route::post('/order', [OrderController::class, 'store'])->name('orders.store');
Route::get('/order/{order}/sukses', [OrderController::class, 'sukses'])->name('orders.sukses');

// --- Manajemen Order (Kasir) ---
Route::get('/kasir/riwayat', [OrderController::class, 'riwayat'])->name('orders.riwayat');
Route::patch('/kasir/order/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status');


