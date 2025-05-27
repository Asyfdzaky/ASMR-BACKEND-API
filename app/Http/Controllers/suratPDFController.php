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

     public function cekDanGenerateSurat(ApprovalSurat $approval)
    {
        Log::info('Cek approval untuk generate surat', $approval->toArray());

        // Cek apakah semua sudah approved
        if (
            $approval->status_rt === 'approved' &&
            $approval->status_rw === 'approved' &&
            $approval->tanggal_approval_rt &&
            $approval->tanggal_approval_rw &&
            $approval->status_approval !== 'Selesai'
        ) {
            // Generate PDF surat
            $pdfPath = $this->suratService->generateSurat($approval->pengajuanSurat);

            // Update pengajuan dan approval
            $approval->pengajuanSurat->update([
                'status_pengajuan' => 'Selesai',
                'pdf_path' => $pdfPath,
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
        ]);
    }


    public function download(PengajuanSurat $pengajuan)
    {
        if (!Storage::disk('public')->exists($pengajuan->pdf_path)) {
            abort(404, 'File surat tidak ditemukan');
        }

        return response()->download(
            storage_path('app/public/' . $pengajuan->pdf_path),
            basename($pengajuan->pdf_path),
            ['Content-Type' => 'application/pdf']
        );
    }

    public function preview(PengajuanSurat $pengajuan)
    {
        if (!Storage::disk('public')->exists($pengajuan->pdf_path)) {
            abort(404, 'File surat tidak ditemukan');
        }

        return response()->file(
            storage_path('app/public/' . $pengajuan->pdf_path),
            ['Content-Type' => 'application/pdf']
        );
    }
}
