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
            'jenis_kelamin_pemohon' => 'required|in:Pria,Perempuan',
            'id_detailAlamat' => 'required|exists:detail_alamat,id',
            'jenis_surat' => 'required|string',
            "alamat" => "required|string",
            "kabupaten" => "required|string",
            "provinsi" => "required|string",
            'keterangan' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validasi gagal', 'errors' => $validator->errors()], 422);
        }

        try {
            
            $detailAlamat = DetailAlamat::create([
                "alamat" => $request->alamat,
                "kabupaten" => $request->kabupaten,
                "provinsi" => $request->provinsi,
            ]); 
            $pemohon = DetailPemohonSurat::create([
                'id_warga' => $warga->id,
                'id_detailAlamat' => $detailAlamat->id,
                'nama_pemohon' => $request->nama_pemohon,
                'nik_pemohon' => $request->nik_pemohon,
                'no_kk_pemohon' => $request->no_kk_pemohon,
                'phone_pemohon' => $request->phone_pemohon,
                'tempat_tanggal_lahir_pemohon' => $request->tempat_tanggal_lahir_pemohon,
                'jenis_kelamin_pemohon' => $request->jenis_kelamin_pemohon,
            ]);
            
            $pengajuan = PengajuanSurat::create([
                'id_warga' => $warga->id,
                'id_detailPemohon' => $pemohon->id,
                'jenis_surat' => $request->jenis_surat,
                'keterangan' => $request->keterangan,
                'status' => 'Diajukan',
                'created_at' => now()
            ]);
            ApprovalSurat::create([
                'id_pengajuan' => $pengajuan->id,
                'status_approval' => 'Pending',
                'catatan' => null,
                'approved_at' => null
            ]);

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

            $dataPengajuan = PengajuanSurat::with(['detailPemohon'])
                ->where('id_warga', $warga->id)
                ->orderByDesc('created_at')
                ->limit(2)
                ->get();

            return response()->json([
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
            ->get();

        $result = $pengajuanSurat->map(function ($pengajuan) {
            $approval = $pengajuan->approvalSurat;
            $progress = [];

            if ($approval) {
                $progress[] = [
                    'title' => 'Pengajuan sedang diproses',
                    'description' => 'Menunggu verifikasi RT',
                    'status' => $approval->status_approval === 'Pending' ? 'in-progress' : 'approved',
                ];

                $progress[] = [
                    'title' => 'Verifikasi RT',
                    'description' => $approval->id_pejabat_rt ? 'Sudah diverifikasi oleh RT' : 'Menunggu RT',
                    'status' => $approval->id_pejabat_rt ? 'approved' : 'pending',
                ];

                $progress[] = [
                    'title' => 'Verifikasi RW',
                    'description' => $approval->id_pejabat_rw ? 'Sudah diverifikasi oleh RW' : 'Menunggu RW',
                    'status' => $approval->id_pejabat_rw ? 'approved' : 'pending',
                ];

                $progress[] = [
                    'title' => 'Penerbitan Surat',
                    'description' => $approval->status_approval === 'Selesai' ? 'Surat selesai diterbitkan' : 'Dalam proses penerbitan',
                    'status' => $approval->status_approval === 'Selesai' ? 'approved' : 'in-progress',
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
