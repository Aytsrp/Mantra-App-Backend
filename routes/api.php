<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\JenisPajakController;
use App\Http\Controllers\Api\LaporanController;
use App\Http\Controllers\Api\ObjekPajakController;
use App\Http\Controllers\Api\PembayaranController;
use App\Http\Controllers\Api\ProfilController;
use App\Http\Controllers\Api\TunggakanController;
use App\Http\Middleware\EnsureWajibPajak;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:login');
    Route::post('refresh', [AuthController::class, 'refresh'])->middleware('throttle:10,1');
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
});

Route::middleware(['auth:sanctum', EnsureWajibPajak::class])->group(function () {
    Route::get('dashboard/ringkasan', [DashboardController::class, 'ringkasan']);

    Route::get('profil', [ProfilController::class, 'show']);
    Route::get('pengaturan', [ProfilController::class, 'pengaturan']);
    Route::put('pengaturan', [ProfilController::class, 'ubahPengaturan']);

    Route::get('jenis-pajak', [JenisPajakController::class, 'index']);
    Route::get('objek-pajak', [ObjekPajakController::class, 'index']);
    Route::get('objek-pajak/{id}', [ObjekPajakController::class, 'show']);
    Route::get('objek-pajak/{id}/riwayat', [ObjekPajakController::class, 'riwayat']);

    Route::get('laporan', [LaporanController::class, 'index']);
    Route::get('laporan/{id}', [LaporanController::class, 'show']);
    Route::get('pembayaran', [PembayaranController::class, 'index']);
    Route::get('tunggakan', [TunggakanController::class, 'index']);

    Route::post('laporan', [LaporanController::class, 'store']);
    Route::put('profil', [ProfilController::class, 'ditunda']);
    Route::post('profil/foto', [ProfilController::class, 'ditunda']);
    Route::post('profil/verifikasi-wa', [ProfilController::class, 'ditunda']);
    Route::put('profil/password', [ProfilController::class, 'ditunda']);
});