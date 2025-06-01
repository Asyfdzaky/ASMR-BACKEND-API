<?php

namespace App\Http\Controllers;

use App\Models\RT;
use App\Models\RW;
use App\Models\User;
use App\Models\Warga;
use App\Models\pejabatRT;
use App\Models\pejabatRW;
use App\Models\DetailAlamat;
use Illuminate\Http\Request;
use App\Models\PengajuanSurat;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class WargaController extends Controller
{

    public function index()
    {
        try {
            // Ambil semua RW
            $dataRW = RW::all()->map(function ($rw) {
                // Cari Pejabat RW terkait (warga + user)
                $pejabatRW = pejabatRW::where('id_rw', $rw->id)->first();
                if ($pejabatRW) {
                    $warga = Warga::with('alamat')->find($pejabatRW->id_warga);
                    $user = $warga ? User::find($warga->id_users) : null;
                    $rw->data = [
                        'warga' => $warga ?? null,
                        'user' => $user ?? null,
                        'pejabat' => $pejabatRW ?? null,
                    ];
                } else {
                    $rw->data = null;
                }
                return $rw;
            });

            // Ambil semua RT dengan data Pejabat RT terkait
            $dataRT = RT::all()->map(function ($rt) {
                $pejabatRT = pejabatRT::where('id_rt', $rt->id)->first();
                if ($pejabatRT) {
                    $warga = Warga::with('alamat')->find($pejabatRT->id_warga);
                    $user = $warga ? User::find($warga->id_users) : null;
                    $rt->data = [
                        'warga' => $warga ?? null,
                        'user' => $user ?? null,
                        'pejabat' => $pejabatRT ?? null,
                    ];
                } else {
                    $rt->data = null;
                }
                $rt->rw = RW::find($rt->id_rw); // juga ambil data RW terkait
                return $rt;
            });

            // Ambil semua warga dengan alamat dan user
            $dataWarga = Warga::with(['alamat', 'user'])->get()->map(function ($warga) {
                $warga->email = $warga->user->email ?? null;
                $warga->alamat = $warga->alamat->alamat ?? null;
                $warga->no_rt = RT::find($warga->id_rt)->nama_rt ?? null;
                $warga->no_rw = RW::find(RT::find($warga->id_rt)->id_rw ?? null)->nama_rw ?? null;
                return $warga;
            });

            return response()->json([
                'rt' => $dataRT,
                'rw' => $dataRW,
                'warga' => $dataWarga,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat mengambil data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function PendingWarga()
    {
        try {
            // Misal warga dengan status aktif/nonaktif? 
            // Di struktur tidak ada approved, mungkin diganti dengan 'status' di user
            $dataWarga = Warga::whereHas('user', function ($query) {
                $query->where('status_akun', 0);
            })->with(['alamat', 'user'])->get()->map(function ($warga) {
                $warga->email = $warga->user->email ?? null;
                $warga->alamat = $warga->alamat->alamat ?? null;
                $warga->no_rt = RT::find($warga->id_rt)->nama_rt ?? null;
                $warga->no_rw = RW::find(RT::find($warga->id_rt)->id_rw ?? null)->nama_rw ?? null;
                return $warga;
            });

            return response()->json([
                'warga' => $dataWarga,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat mengambil data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function CountData()
    {
        try {
            $countWarga = User::where('role', 'Warga')->count();
            $countPejabat = User::whereIn('role', ['PejabatRT', 'PejabatRW'])->count();
            $countPengajuan = PengajuanSurat::count();
            $countPending = PengajuanSurat::where('status', 'Diproses_RW')->count();
            $countSelesai = PengajuanSurat::where('status', '!=', 'Diproses_RW')->count();

            return response()->json([
                'CountWarga' => $countWarga,
                'CountPejabat' => $countPejabat,
                'CountPengajuanSurat' => $countPengajuan,
                'CountDataPengajuanPending' => $countPending,
                'CountDataPengajuanSelesai' => $countSelesai,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat mengambil data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

        
    public function destroy($id, Request $request)
    {
        try {
            DB::beginTransaction();

            // Allow deletion by either warga_id or user_id
            $warga = $request->has('user_id') 
                ? Warga::where('id_users', $request->user_id)->firstOrFail()
                : Warga::findOrFail($id);

            // Get related data before deletion
            $user = $warga->user;
            
            // 1. Delete address
            if ($warga->alamat) {
                $warga->alamat()->delete();
            }

            // 2. Delete warga record
            $warga->delete();

            // 3. Delete user account
            $user->delete();

            DB::commit();

            return response()->json([
                "message" => "Data berhasil dihapus",
                "deleted" => [
                    "role" => $user->role,
                    "warga_id" => $warga->id,
                    "user_id" => $user->id,
                ]
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Gagal menghapus data',
                'message' => $e->getMessage()
            ], 500);
        }
    }  
}

