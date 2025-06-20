<?php

use App\Http\Controllers\ApprovalRoleController;
use Illuminate\Http\Request;
use App\Http\Controllers\GetRtRW;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SuratController;
use App\Http\Controllers\WargaController;
use App\Http\Controllers\DiagramController;
use App\Http\Controllers\NotifikasiController;
use App\Http\Controllers\PengajuanSuratController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\suratPDFController;
use App\Http\Controllers\programKerjaController;
use App\Http\Controllers\RegisterPejabatController;
use Illuminate\Support\Facades\Hash;

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
    Route::get('/all-rt', [GetRtRW::class, 'getAllRTs']); // List semua RT dengan detail
    Route::get('/rt/{id_rt_entity}/warga', [GetRtRW::class, 'getWargaByRT']); // List warga by RT ID
    Route::get('/rw/{id_rw_entity}/warga', [GetRtRW::class, 'getWargaByRW']); // List warga by RW ID (scope)
    Route::put('/{role}', [GetRtRW::class, 'updateWilayah']); // Update wilayah
});

// -------------------------
// PEJABAT RT-RW (ADMIN ONLY)
// -------------------------
Route::middleware(['auth:sanctum','admin'])->prefix('pejabat')->group(function () {
    Route::post('/jabatan/rt', [RegisterPejabatController::class, 'storeJabatanRT']);   // Buat jabatan RT
    Route::post('/jabatan/rw', [RegisterPejabatController::class, 'storeJabatanRW']);   // Buat jabatan RW
    Route::post('/register', [RegisterPejabatController::class, 'store']);   // Registrasi pejabat
    Route::get('/rt/{id_rt_entity}/details', [RegisterPejabatController::class, 'getRTDetails']); // Get details RT
    Route::get('/rw/{id_rw_entity}/details', [RegisterPejabatController::class, 'getRWDetails']); // Get details RW
    Route::put('/rt/{id}', [RegisterPejabatController::class, 'updateRT']);       // Update RT
    Route::put('/rw/{id}', [RegisterPejabatController::class, 'updateRW']);       // Update RW
    Route::delete('/rw/{id}', [RegisterPejabatController::class, 'deleteRW']);   // Hapus RW
    Route::delete('/rt/{id}', [RegisterPejabatController::class, 'deleteRT']);   // Hapus RT
    Route::get('/warga/{nik}', [RegisterPejabatController::class, 'getWargaByNIK']); // Get warga by NIK
});

// -------------------------
// APPROVAL ROLE WARGA
// -------------------------
Route::middleware(['auth:sanctum','admin'])->prefix('approval-role')->group(function () {
    Route::get('/warga', [ApprovalRoleController::class, 'getWarga']);   // get data warga
    Route::put('/warga/{id}/approve', [ApprovalRoleController::class, 'ApproveWarga']);       // approve warga
    Route::put('/warga/{id}/reject', [ApprovalRoleController::class, 'RejectWarga']);       // reject warga
});

// -------------------------
// PENGAJUAN & APPROVAL SURAT (USER YANG LOGIN)
// -------------------------
Route::middleware('auth:sanctum')->prefix('surat')->group(function () {
    // Pengajuan Surat Routes
    Route::get('/data-warga', [PengajuanSuratController::class, 'getDataWarga']);         // Get data warga
    Route::post('/pengajuan', [PengajuanSuratController::class, 'store']);                // Submit pengajuan
    Route::get('/riwayat-pengajuan', [PengajuanSuratController::class, 'getDataPengajuan']); // Get pengajuan data
    Route::get('/riwayat-pengajuan/{id_warga}', [PengajuanSuratController::class, 'getHistoryData']); // Get history

    // Pengajuan & Approval
    Route::get('/', [SuratController::class, 'getAllPengajuanSurat']);                   // Semua pengajuan
    Route::get('/pending/rt/{id_rt}', [SuratController::class, 'getPendingSuratRT']);     // Pending oleh RT
    Route::get('/pending/rw/{id_rw}', [SuratController::class, 'getPendingSuratRW']);     // Pending oleh RW
    Route::put('/{id_pengajuan}/approval', [SuratController::class, 'updateApprovalStatus']); // Approve/tolak

    // PDF Surat (akses tetap pakai auth:sanctum untuk keamanan)
    Route::get('/{id}/generate', [SuratPDFController::class, 'generateAndSave']);   // Buat PDF
    Route::get('/{pengajuan}/download', [SuratPDFController::class, 'download']);          // Download PDF
    Route::get('/{pengajuan}/preview', [SuratPDFController::class, 'preview']);            // Preview PDF
});

// -------------------------
// BIODATA
// -------------------------
Route::middleware('auth:sanctum')->prefix('biodata')->group(function () {
    Route::get('/', [WargaController::class, 'index']);            // Get semua data RT, RW, Warga
    Route::get('/pending-warga', [WargaController::class, 'PendingWarga']); // Warga yang status non aktif
    Route::get('/count', [WargaController::class, 'CountData']);   // Count data summary
    Route::delete('/{id}', [WargaController::class, 'destroy']);  // Hapus warga
});

// -------------------------
// GRAFIK
// -------------------------
Route::middleware('auth:sanctum')->prefix('grafik')->group(function () {
    Route::post('/jumlah-pengajuan-bulan', [DiagramController::class, 'jumlahPengajuanPerBulan']);
    Route::get('/jumlah-pengajuan-jenis', [DiagramController::class, 'jumlahPengajuanPerJenis']);
});

// -------------------------
// PROGRAM KERJA
// -------------------------
Route::middleware('auth:sanctum')->prefix('proker')->group(function(){
    Route::get('/',[programKerjaController::class, 'index']); // Get semua program kerja
    Route::get('/{id}',[programKerjaController::class, 'show']); // Get program kerja by id
    Route::post('/',[programKerjaController::class, 'store']); // Tambah program kerja
    Route::put('/{id}',[programKerjaController::class, 'update']); // Update program kerja
    Route::delete('/{id}',[programKerjaController::class, 'destroy']); // Hapus program kerja
});

// -------------------------
// USER PROFILE MANAGEMENT
// -------------------------
Route::middleware('auth:sanctum')->prefix('profile')->group(function () {
    Route::get('/', [ProfileController::class, 'show']); // Mengambil profil lengkap
    Route::put('/', [ProfileController::class, 'update']); // Memperbarui detail profil (email, nama, dll.)
    Route::put('/password', [ProfileController::class, 'updatePassword']); // Memperbarui kata sandi
});

// -------------------------
// Notifikasi
// -------------------------
Route::prefix('notifications')->prefix('notifications')->group(function () {
    Route::get('/count', [NotifikasiController::class, 'getNotificationCounts']);
    Route::delete('/clear', [NotifikasiController::class, 'clearNotification']);
});

// -------------------------
// AUTH (Bawaan Laravel Breeze / Sanctum)
// -------------------------
require __DIR__.'/auth.php';
