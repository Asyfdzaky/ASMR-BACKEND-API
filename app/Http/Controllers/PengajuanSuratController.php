<?php

namespace App\Http\Controllers;

use App\Models\Warga;
use Illuminate\Http\Request;
use App\Models\ApprovalSurat;
use App\Models\DetailAlamat;
use App\Models\PengajuanSurat;
use App\Models\DetailPemohonSurat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class PengajuanSuratController extends Controller
{
   public function getDataWarga()
    {
        try {
            $user = Auth::user();
            if (!$user) return response()->json(['error' => 'User not authenticated'], 401);

            $warga = Warga::with(['rt', 'rt.rw', 'alamat'])->where('id_users', $user->id)->first();
            if (!$warga) return response()->json(['error' => 'Warga data not found'], 404);

            return response()->json([
                'user' => $user,
                'warga' => $warga
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error occurred', 'error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $warga = Warga::where('id_users', $user->id)->first();
        if (!$warga) {
            return response()->json(['message' => 'Data warga tidak ditemukan'], 404);
        }

        $validator = Validator::make($request->all(), [
            'nama_pemohon' => 'required|string',
            'nik_pemohon' => 'required|string',
            'no_kk_pemohon' => 'required|string',
            'phone_pemohon' => 'required|string',
            'tempat_tanggal_lahir_pemohon' => 'required|string',
            'jenis_kelamin_pemohon' => 'required|in:Laki-Laki,Perempuan',
            // 'id_detailAlamat' => 'required|exists:detail_alasmat,id',
            'jenis_surat' => 'required|string',
            "alamat" => "required|string",
            // "kabupaten" => "required|string",
            // "provinsi" => "required|string",
            'keterangan' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validasi gagal', 'errors' => $validator->errors()], 422);
        }

        try {
            
            DB::beginTransaction();
            try {
                // $detailAlamat = DetailAlamat::create([
                //     "alamat" => $request->alamat,
                //     "kabupaten" => $request->kabupaten,
                //     "provinsi" => $request->provinsi,
                // ]); 
                $pemohon = DetailPemohonSurat::create([
                    'id_warga' => $warga->id,
                    'alamat_pemohon' => $request->alamat,
                    'nama_pemohon' => $request->nama_pemohon,
                    'nik_pemohon' => $request->nik_pemohon,
                    'no_kk_pemohon' => $request->no_kk_pemohon,
                    'phone_pemohon' => $request->phone_pemohon,
                    'tempat_tanggal_lahir_pemohon' => $request->tempat_tanggal_lahir_pemohon,
                    'jenis_kelamin_pemohon' => $request->jenis_kelamin_pemohon,
                ]);
                
                $pengajuan = PengajuanSurat::create([
                    'id_warga' => $warga->id,
                    'id_rt' => $warga->rt->id,
                    'id_rw' => $warga->rt->rw->id,
                    'id_detail_pemohon' => $pemohon->id,
                    'jenis_surat' => $request->jenis_surat,
                    'keterangan' => $request->keterangan ?? "",
                    'status' => 'Diajukan',
                    'file_surat' => "",
                    'created_at' => now()
                ]);
                
                ApprovalSurat::create([
                    'id_pengajuan' => $pengajuan->id,
                    'id_pejabat_rt' => $warga->rt->id,
                    'id_pejabat_rw' => $warga->rt->rw->id,
                    'status_approval' => 'Pending_RT',
                    'catatan' => null,
                    'approved_at' => null
                ]);
                
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

            return response()->json([
                'message' => 'Pengajuan berhasil diajukan!',
                'pengajuan' => $pengajuan
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Terjadi kesalahan saat menyimpan pengajuan', 'error' => $e->getMessage()], 500);
        }
    }

    public function getDataPengajuan()
    {
        try {
            $user = Auth::user();
            $warga = Warga::where('id_users', $user->id)->first();
            if (!$warga) return response()->json(['message' => 'Data warga tidak ditemukan'], 404);

            $dataPengajuan = PengajuanSurat::with(['detailPemohon', 'approvalSurat'])
                ->where('id_warga', $warga->id)
                ->orderByDesc('created_at')
                ->limit(2)
                ->get();

            return response()->json([
                'status' => 'success',
                'pengajuan' => $dataPengajuan
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Terjadi kesalahan saat mengambil data', 'error' => $e->getMessage()], 500);
        }
    }

    public function getHistoryData($id_warga)
    {
        $pengajuanSurat = PengajuanSurat::with('approvalSurat')
            ->where('id_warga', $id_warga)
            ->orderByDesc('created_at')
            ->get();

        $result = $pengajuanSurat->map(function ($pengajuan) {
            $approval = $pengajuan->approvalSurat;
            $progress = [];

            if ($approval) {
                $progress[] = [
                    'title' => 'Pengajuan sedang diproses',
                    'description' => 'Menunggu verifikasi RT',
                    'status' => ($approval->status_approval === 'Diajukan' ? 'in-progress' : 'approved'),
                ];

                $progress[] = [
                    'title' => 'Verifikasi RT',
                    'description' => $approval->status_approval === 'Disetujui_RT' ? 'Pengajuan telah disetujui oleh RT' : ($approval->status_approval === 'Ditolak_RT' ? 'Pengajuan ditolak oleh RT' : 'RT sedang memverifikasi pengajuan'),
                    'status' => $approval->status_approval === 'Disetujui_RT' || $approval->status_approval === 'Disetujui_RW' || $approval->status_approval === 'Selesai' ? 'approved' : ($approval->status_approval === 'Ditolak_RT' ? 'rejected' : ($approval->status_approval === 'Pending_RT' ? 'in-progress' : 'pending')),
                ];

                $progress[] = [
                    'title' => 'Verifikasi RW',
                    'description' => $approval->status_approval === 'Disetujui_RW' ? 'Pengajuan telah disetujui oleh RW' : ($approval->status_approval === 'Ditolak_RW' ? 'Pengajuan ditolak oleh RW' : 'RW sedang memverifikasi pengajuan'),
                    'status' => $approval->status_approval === 'Disetujui_RW' || $approval->status_approval === 'Selesai' ? 'approved' : ($approval->status_approval === 'Ditolak_RW' ? 'rejected' : ($approval->status_approval === 'Pending_RW' || $approval->status_approval === 'Disetujui_RT' ? 'in-progress' : 'pending')),
                ];

                $progress[] = [
                    'title' => 'Penerbitan Surat',
                    'description' => $approval->status_approval === 'Selesai' && $approval->status_approval === 'Disetujui_RW' ? 'Surat telah selesai diterbitkan' : 'Surat sedang dalam proses penerbitan',
                    'status' => $approval->status_approval === 'Selesai' ? 'approved' : ($approval->status_approval === 'Disetujui_RW' ? 'in-progress' : 'pending'),
                ];
            }

            return [    
                'id_pengajuan' => $pengajuan->id,
                'created_at' => $pengajuan->created_at->format('Y-m-d'),
                'jenis_surat' => $pengajuan->jenis_surat,
                'status' => $pengajuan->status,
                'progress' => $progress
            ];
        });

        return response()->json($result);
    }
}
