<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ApprovalSurat;
use App\Models\Notifikasi;
use App\Models\PengajuanSurat;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SuratController extends Controller
{
     // 1. Menampilkan pengajuan surat yang masih pending untuk RT tertentu
    public function getPendingSuratRT(Request $request, $id_rt)
    {
        $surat = PengajuanSurat::with(['warga', 'approvalSurat', 'rt', 'rw', 'detailPemohon'])
            ->whereHas('warga', function ($q) use ($id_rt) {
                $q->where('id_rt', $id_rt);
            })
            ->orderByDesc('created_at');

        if($request->filled('all')){  
            if ($request->all == true) {
                $surat = $surat->get();

                return response()->json([
                    'status' => 'success',
                    'data' => $surat,
                ]);
            }
        }
        
        if($request->filled('limit')){
            $surat = $surat->limit($request->limit);
            $surat = $surat->get();

            return response()->json([
                'status' => 'success',
                'data' => $surat,
            ]);
        }

        $surat = $surat->whereHas("approvalSurat", function ($q) {
            $q->where('status_approval', 'Pending');
        });

        $surat = $surat->get();

        return response()->json([
            'status' => 'success',
            'data' => $surat,
        ]);
    }

    // 2. Menampilkan pengajuan surat untuk RW yang sudah disetujui RT
    public function getPendingSuratRW(Request $request, $id_rw)
    {
        $surat = PengajuanSurat::with(['warga', 'approvalSurat', 'rt', 'rw', 'detailPemohon'])
            ->whereHas('warga.rt.rw', function ($q) use ($id_rw) {
                $q->where('id', $id_rw);
            })
            ->orderByDesc('created_at');

            if($request->filled('all')){  
                if ($request->all == true) {
                    $surat = $surat->get();

                    return response()->json([
                        'status' => 'success',
                        'data' => $surat,
                    ]);
                }
            }

            $surat = $surat->whereHas("approvalSurat", function ($q) {
                $q->whereIn('status_approval', ['Disetujui_RT', 'Disetujui_RW']);
            });

            if($request->filled('limit')){
                $surat = $surat->limit($request->limit);
            }

            $surat = $surat->get();

        return response()->json([
            'status' => 'success',
            'data' => $surat,
        ]);
    }

    // 3. Update status approval
    public function updateApprovalStatus(Request $request, $id_pengajuan)
    {
        $request->validate([
            'status_approval' => 'required|in:Disetujui_RT,Ditolak_RT,Disetujui_RW,Ditolak_RW,Selesai',
            'catatan' => 'nullable|string',
            'id_rt' => 'nullable|exists:rt,id',
            'id_rw' => 'nullable|exists:rw,id',
        ]);

        try {
            DB::beginTransaction();
        
            $pengajuan = PengajuanSurat::with('warga.rt.rw')->findOrFail($id_pengajuan);
        
            $approval = ApprovalSurat::firstOrNew(['id_pengajuan' => $pengajuan->id]);
            $approval->status_approval = $request->status_approval;
            $approval->catatan = $request->catatan;
            $approval->approved_at = now();
        
            if (in_array($request->status_approval, ['Disetujui_RT', 'Ditolak_RT'])) {
                $approval->id_rt = $request->id_rt;
            }
        
            if (in_array($request->status_approval, ['Disetujui_RW', 'Ditolak_RW'])) {
                $approval->id_rw = $request->id_rw;
            }
        
            $approval->save();
        
            $pengajuan->status = match($request->status_approval) {
                'Disetujui_RT' => 'Diproses_RW',
                'Disetujui_RW' => 'Disetujui',
                'Ditolak_RT', 'Ditolak_RW' => 'Ditolak',
                'Selesai' => 'Selesai',
                default => $pengajuan->status
            };
            $pengajuan->save();
        
            // Notifikasi Warga
            if ($pengajuan->warga && $pengajuan->warga->id_users) {
                Notifikasi::updateOrCreate(
                    [
                        'id_pengajuan_surat' => $pengajuan->id,
                        'id_user' => $pengajuan->warga->id_users,
                    ],
                    [
                        'jenis_notif' => 'surat',
                        'pesan' => 'Pengajuan surat telah ' . $pengajuan->status . '.'
                    ]
                );
            }
        
            // Notifikasi Pejabat RW
            if ($request->status_approval === 'Disetujui_RT') {
                $rw = $pengajuan->warga->rt->rw;
                if ($rw && $rw->pejabatRW && $rw->pejabatRW->warga && $rw->pejabatRW->warga->id_users) {
                    Notifikasi::create([
                        'id_user' => $rw->pejabatRW->warga->id_users,
                        'id_pengajuan_surat' => $pengajuan->id,
                        'jenis_notif' => 'surat',
                        'pesan' => 'Pengajuan surat baru telah ' . $pengajuan->status . '.',
                    ]);
                }
                
            }
        
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
        $query = PengajuanSurat::with(['warga.rt.rw', 'approvalSurat', 'detailPemohon']);

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
