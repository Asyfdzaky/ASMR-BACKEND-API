<?php

namespace App\Http\Controllers;

use App\Models\RW;
use App\Models\RT;
use App\Models\pejabatRT;
use App\Models\pejabatRW;
use App\Models\Warga;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class GetRtRW extends Controller
{
    public function GetRW()
    {
        try {
            $rws = RW::all();

            $result = $rws->map(function ($rw) {
                $pejabat = pejabatRW::where('id_rw', $rw->id)
                                ->with('warga')
                                ->latest('periode_mulai')
                                ->first();

                $pejabatSummary = null;
                if ($pejabat && $pejabat->warga) {
                    $pejabatSummary = [
                        'nama_warga' => $pejabat->warga->nama,
                        'nik_warga' => $pejabat->warga->nik,
                        'id_pejabat_rw' => $pejabat->id,
                        'periode_mulai' => $pejabat->periode_mulai,
                        'periode_selesai' => $pejabat->periode_selesai,
                    ];
                }

                return [
                    'id_rw' => $rw->id,
                    'nama_rw' => $rw->nama_rw,
                    'pejabat' => $pejabatSummary
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'List Semua Data RW dengan Pejabat Terkini',
                'data' => $result
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data RW',
                'error' => $e->getMessage()
            ], 500);
        }
    }
     /**
     * Ambil data RT berdasarkan ID RW
     */
    public function GetRT($id)
    {
        $rw = RW::with('rt')->find($id);

        if (!$rw) {
            return response()->json([
                'success' => false,
                'message' => 'RW tidak ditemukan',
                'data' => null
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'List Data RT berdasarkan RW',
            'data' => $rw->rt
        ], 200);
    }

    public function getAllRW()
    {
        $rws = RW::all();
        return response()->json([
            'success' => true,
            'message' => 'List Semua Data RW',
            'data' => $rws
        ], 200);
    }

    /**
     * List all RTs with parent RW info and active PejabatRT summary.
     */
    public function getAllRTs()
    {
        try {
            $rts = RT::with('rw')->get();

            $result = $rts->map(function ($rt) {
                $pejabat = pejabatRT::where('id_rt', $rt->id)
                                ->with('warga')
                                ->latest('periode_mulai')
                                ->first();

                $pejabatSummary = null;
                if ($pejabat && $pejabat->warga) {
                    $pejabatSummary = [
                        'nama_warga' => $pejabat->warga->nama,
                        'nik_warga' => $pejabat->warga->nik,
                        'id_pejabat_rt' => $pejabat->id,
                        'periode_mulai' => $pejabat->periode_mulai,
                        'periode_selesai' => $pejabat->periode_selesai,
                    ];
                }

                return [
                    'id' => $rt->id,
                    'nama_rt' => $rt->nama_rt,
                    'id_rw' => $rt->id_rw,
                    'rw' => $rt->rw ? [
                        'id_rw' => $rt->rw->id,
                        'nama_rw' => $rt->rw->nama_rw
                    ] : null,
                    'pejabat' => $pejabatSummary
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'List Semua Data RT',
                'data' => $result
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data RT',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * List Warga residing in a specific RT, with optional search.
     */
    public function getWargaByRT(Request $request, $id_rt_entity)
    {
        try {
            // First, check if the RT exists
            $rt = RT::find($id_rt_entity);
            if (!$rt) {
                return response()->json([
                    'success' => false,
                    'message' => 'RT tidak ditemukan',
                    'data' => null
                ], 404);
            }

            $query = Warga::where('id_rt', $id_rt_entity)->whereHas('user', function($query){
                $query->where('status_akun', 1);
            });

            if ($request->has('search_nik')) {
                $query->where('nik', 'like', '%' . $request->input('search_nik') . '%');
            }

            if ($request->has('search_nama')) {
                $query->where('nama', 'like', '%' . $request->input('search_nama') . '%');
            }

            $query->with('user')->whereHas('user', function($query){
                $query->where('role', 'Warga');
            });

            $wargaList = $query->select('id', 'nik', 'nama')->get();

            $result = $wargaList->map(function ($warga) {
                return [
                    'id_warga' => $warga->id,
                    'nik' => $warga->nik,
                    'nama_lengkap' => $warga->nama,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'List Warga untuk RT ' . $rt->nama_rt,
                'data' => $result
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data warga berdasarkan RT',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * List Warga residing in a specific RW's scope (all RTs under it), with optional search.
     */
    public function getWargaByRW(Request $request, $id_rw_entity)
    {
        try {
            // First, check if the RW exists
            $rw = RW::find($id_rw_entity);
            if (!$rw) {
                return response()->json([
                    'success' => false,
                    'message' => 'RW tidak ditemukan',
                    'data' => null
                ], 404);
            }

            // Get all RT IDs under this RW
            $rt_ids = RT::where('id_rw', $id_rw_entity)->pluck('id');

            if ($rt_ids->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Tidak ada RT ditemukan untuk RW ' . $rw->nama_rw,
                    'data' => []
                ], 200);
            }

            $query = Warga::whereIn('id_rt', $rt_ids)->with('rt')->whereHas('user', function($query){
                $query->where('status_akun', 1);
            });

            if ($request->has('search_nik')) {
                $query->where('nik', 'like', '%' . $request->input('search_nik') . '%');
            }

            if ($request->has('search_nama')) {
                $query->where('nama', 'like', '%' . $request->input('search_nama') . '%');
            }

            $query->with('user')->whereHas('user', function($query){
                $query->where('role', 'Warga');
            });

            $wargaList = $query->select('id', 'nik', 'nama', 'id_rt')->get();

            $result = $wargaList->map(function ($warga) {
                return [
                    'id_warga' => $warga->id,
                    'nik' => $warga->nik,
                    'nama_lengkap' => $warga->nama,
                    'asal_rt_id' => $warga->id_rt,
                    'asal_rt_nama' => $warga->rt ? $warga->rt->nama_rt : null, 
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'List Warga untuk RW ' . $rw->nama_rw,
                'data' => $result
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data warga berdasarkan RW',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateWilayah(Request $request, $role)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'nama_rt' => 'sometimes|string|max:255',
            'nama_rw' => 'sometimes|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }
        try {
            DB::beginTransaction();
            if ($role == 'rt') {
                $rt = RT::find($request->id);
                $rt->nama_rt = $request->nama_rt;
                $rt->save();
            } else {
                $rw = RW::find($request->id);
                $rw->nama_rw = $request->nama_rw;
                $rw->save();
            }
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Wilayah berhasil diupdate',
                'data' => $rt ?? $rw
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate wilayah',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
