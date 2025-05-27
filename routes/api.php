<?php

use Illuminate\Http\Request;
use App\Http\Controllers\GetRtRW;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SuratController;
use App\Http\Controllers\suratPDFController;
use App\Http\Controllers\RegisterPejabatController;

// -------------------------
// AUTHENTICATED USER ROUTE
// -------------------------
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// -------------------------
// WILAYAH (RT/RW)
// -------------------------
Route::prefix('wilayah')->group(function () {
    Route::get('/rw', [GetRtRW::class, 'GetRW']);           // List semua RW
    Route::get('/rt/{id_rw}', [GetRtRW::class, 'GetRT']);   // List RT berdasarkan RW
});

// -------------------------
// PEJABAT RT-RW (ADMIN ONLY)
// -------------------------
Route::middleware(['auth:sanctum'])->prefix('pejabat')->group(function () {
    Route::post('/register', [RegisterPejabatController::class, 'store']);   // Registrasi pejabat
    Route::put('/{id}', [RegisterPejabatController::class, 'update']);       // Update pejabat
    Route::delete('/{id}', [RegisterPejabatController::class, 'destroy']);   // Hapus pejabat
});

// -------------------------
// PENGAJUAN & APPROVAL SURAT (USER YANG LOGIN)
// -------------------------
Route::middleware(['auth:sanctum'])->prefix('surat')->group(function () {
    Route::get('/', [SuratController::class, 'getAllPengajuanSurat']);               // Semua pengajuan (bisa filter)
    Route::get('/pending/rt/{id_rt}', [SuratController::class, 'getPendingSuratRT']); // Pending approval oleh RT
    Route::get('/pending/rw/{id_rw}', [SuratController::class, 'getPendingSuratRW']); // Pending approval oleh RW
    Route::put('/{id_pengajuan}/approval', [SuratController::class, 'updateApprovalStatus']); // Setujui/Tolak
});
 Route::get('/surat/{pengajuan}/generate', [suratPDFController::class, 'generateAndSave']);
Route::get('/surat/{pengajuan}/download', [suratPDFController::class, 'download']);
Route::get('/surat/{pengajuan}/preview', [suratPDFController::class, 'preview']);
// -------------------------
// AUTH (Bawaan Laravel Breeze / Sanctum)
// -------------------------
require __DIR__.'/auth.php';
