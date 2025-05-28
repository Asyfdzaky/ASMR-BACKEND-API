<?php

use Illuminate\Http\Request;
use App\Http\Controllers\GetRtRW;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SuratController;
use App\Http\Controllers\WargaController;
use App\Http\Controllers\DiagramController;
use App\Http\Controllers\suratPDFController;
use App\Http\Controllers\programKerjaController;
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
Route::middleware(['auth:sanctum','admin'])->prefix('pejabat')->group(function () {
    Route::post('/register', [RegisterPejabatController::class, 'store']);   // Registrasi pejabat
    Route::put('/{id}', [RegisterPejabatController::class, 'update']);       // Update pejabat
    Route::delete('/{id}', [RegisterPejabatController::class, 'destroy']);   // Hapus pejabat
});

// -------------------------
// PENGAJUAN & APPROVAL SURAT (USER YANG LOGIN)
// -------------------------
Route::middleware('auth:sanctum')->prefix('surat')->group(function () {
    // Pengajuan & Approval
    Route::get('/', [SuratController::class, 'getAllPengajuanSurat']);                   // Semua pengajuan
    Route::get('/pending/rt/{id_rt}', [SuratController::class, 'getPendingSuratRT']);     // Pending oleh RT
    Route::get('/pending/rw/{id_rw}', [SuratController::class, 'getPendingSuratRW']);     // Pending oleh RW
    Route::put('/{id_pengajuan}/approval', [SuratController::class, 'updateApprovalStatus']); // Approve/tolak

    // PDF Surat (akses tetap pakai auth:sanctum untuk keamanan)
    Route::get('/{pengajuan}/generate', [SuratPDFController::class, 'generateAndSave']);   // Buat PDF
    Route::get('/{pengajuan}/download', [SuratPDFController::class, 'download']);          // Download PDF
    Route::get('/{pengajuan}/preview', [SuratPDFController::class, 'preview']);            // Preview PDF

    Route::prefix('biodata')->group(function () {
        Route::get('/', [WargaController::class, 'index']);            // Get semua data RT, RW, Warga
        Route::get('/pending-warga', [WargaController::class, 'PendingWarga']); // Warga yang status non aktif
        Route::get('/count', [WargaController::class, 'CountData']);   // Count data summary
        Route::put('/rt/{id}', [WargaController::class, 'updateRT']);  // Update RT dan pejabat RT
        Route::put('/rw/{id}', [WargaController::class, 'updateRW']);  // Update RW dan pejabat RW
    });
    Route::prefix('grafik')->group(function () {
        Route::get('/jumlah-pengajuan-bulan', [DiagramController::class, 'jumlahPengajuanPerBulan']);
        Route::get('/jumlah-pengajuan-jenis', [DiagramController::class, 'jumlahPengajuanPerJenis']);
    });
    Route::prefix('proker')->group(function(){
        Route::get('/',[programKerjaController::class, 'index']); // Get semua program kerja
        Route::get('/{id}',[programKerjaController::class, 'show']); // Get program kerja by id
        Route::post('/',[programKerjaController::class, 'store']); // Tambah program kerja
        Route::put('/{id}',[programKerjaController::class, 'update']); // Update program kerja
        Route::delete('/{id}',[programKerjaController::class, 'destroy']); // Hapus program kerja
    });
});
// -------------------------
// AUTH (Bawaan Laravel Breeze / Sanctum)
// -------------------------
require __DIR__.'/auth.php';
