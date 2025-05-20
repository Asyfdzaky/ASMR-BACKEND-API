<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ApprovalSurat;
use App\Models\PengajuanSurat;
use Illuminate\Support\Facades\DB;

class SuratController extends Controller
{
     // 1. Menampilkan pengajuan surat yang masih pending untuk RT tertentu
    public function getPendingSuratRT($id_rt)
    {
        $surat = PengajuanSurat::with(['warga', 'approval'])
            ->whereHas('warga', function ($q) use ($id_rt) {
                $q->where('id_rt', $id_rt);
            })
            ->whereHas('approval', function ($q) {
                $q->where(function ($query) {
                    $query->whereNull('status_approval')
                          ->orWhere('status_approval', 'Pending');
                });
            })
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $surat
        ]);
    }

    // 2. Menampilkan pengajuan surat untuk RW yang sudah disetujui RT
    public function getPendingSuratRW($id_rw)
    {
        $surat = PengajuanSurat::with(['warga', 'approval'])
            ->whereHas('warga.rt.rw', function ($q) use ($id_rw) {
                $q->where('id', $id_rw);
            })
            ->whereHas('approval', function ($q) {
                $q->where('status_approval', 'Disetujui_RT');
            })
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $surat
        ]);
    }

    // 3. Update status approval
    public function updateApprovalStatus(Request $request, $id_pengajuan)
    {
        $request->validate([
            'status_approval' => 'required|in:Disetujui_RT,Ditolak_RT,Disetujui_RW,Ditolak_RW,Selesai',
            'catatan' => 'nullable|string',
            'id_pejabat_rt' => 'nullable|exists:pejabat__r_t,id',
            'id_pejabat_rw' => 'nullable|exists:pejabat__r_w,id',
        ]);

        try {
            DB::beginTransaction();

            $approval = ApprovalSurat::firstOrNew(['id_pengajuan' => $id_pengajuan]);

            $approval->status_approval = $request->status_approval;
            $approval->catatan = $request->catatan;
            $approval->approved_at = now();

            if (in_array($request->status_approval, ['Disetujui_RT', 'Ditolak_RT'])) {
                $approval->id_pejabat_rt = $request->id_pejabat_rt;
            }

            if (in_array($request->status_approval, ['Disetujui_RW', 'Ditolak_RW'])) {
                $approval->id_pejabat_rw = $request->id_pejabat_rw;
            }

            $approval->save();

            $pengajuan = PengajuanSurat::findOrFail($id_pengajuan);
            $pengajuan->status = match($request->status_approval) {
                'Disetujui_RT' => 'Diproses_RW',
                'Disetujui_RW' => 'Disetujui',
                'Ditolak_RT', 'Ditolak_RW' => 'Ditolak',
                'Selesai' => 'Selesai',
                default => $pengajuan->status
            };
            $pengajuan->save();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Status berhasil diperbarui'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // 4. Menampilkan semua pengajuan surat (opsional filter)
    public function getAllPengajuanSurat(Request $request)
    {
        $query = PengajuanSurat::with(['warga.rt.rw', 'approval']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('id_rt')) {
            $query->whereHas('warga.rt', fn($q) => $q->where('id', $request->id_rt));
        }

        if ($request->filled('id_rw')) {
            $query->whereHas('warga.rt.rw', fn($q) => $q->where('id', $request->id_rw));
        }

        if ($request->filled(['start_date', 'end_date'])) {
            $query->whereBetween('created_at', [$request->start_date, $request->end_date]);
        }

        return response()->json([
            'status' => 'success',
            'data' => $query->orderByDesc('created_at')->get()
        ]);
    }
}
