<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MenuController;
use App\Http\Controllers\Api\TransaksiController;

// Rute Menu
Route::get('/menu', [MenuController::class, 'index']);

// Rute Transaksi (Tipe GET & POST)
Route::get('/transaksi', [TransaksiController::class, 'handleGet']);
Route::post('/transaksi', [TransaksiController::class, 'handlePost']);