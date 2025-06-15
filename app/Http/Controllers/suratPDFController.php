<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use App\Models\ApprovalSurat;
use App\Models\PengajuanSurat;
use App\Services\SuratService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class suratPDFController extends Controller
{
     protected $suratService;

    public function __construct(SuratService $suratService)
    {
        $this->suratService = $suratService;
    }

     public function generateAndSave($id)
    {
        $approval = ApprovalSurat::where('id_pengajuan', $id)->firstOrFail();
        Log::info('Cek approval untuk generate surat', $approval->toArray());

        // Cek apakah semua sudah approved
        if (
            $approval->status_approval === 'Disetujui_RW' &&
            $approval->approved_at &&
            $approval->status_approval !== 'Selesai'
        ) {
            // Generate PDF surat
            $pdfPath = $this->suratService->generateSurat($approval->pengajuanSurat);

            // Update pengajuan dan approval
            $approval->pengajuanSurat->update([
                'status' => 'Disetujui',
                'file_surat' => $pdfPath,
            ]);

            $approval->update([
                'status_approval' => 'Selesai',
            ]);

            return response()->json([
                'message' => 'Surat berhasil digenerate dan diselesaikan.',
                'path' => $pdfPath,
            ]);
        }

        return response()->json([
            'message' => 'Syarat approval belum lengkap atau sudah selesai.',
            'approval' => $approval->toArray(),
        ]);
    }


    public function download(PengajuanSurat $pengajuan)
    {
        if (!Storage::disk('public')->exists($pengajuan->file_surat)) {
            abort(404, 'File surat tidak ditemukan');
        }

        return response()->download(
            storage_path('app/public/' . $pengajuan->file_surat),
            basename($pengajuan->file_surat),
            ['Content-Type' => 'application/pdf']
        );
    }

    public function preview(PengajuanSurat $pengajuan)
    {
        if (!Storage::disk('public')->exists($pengajuan->file_surat)) {
            abort(404, 'File surat tidak ditemukan');
        }

        return response()->file(
            storage_path('app/public/' . $pengajuan->file_surat),
            ['Content-Type' => 'application/pdf']
        );
    }
}
