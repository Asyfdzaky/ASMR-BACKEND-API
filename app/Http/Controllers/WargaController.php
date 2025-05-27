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
                    $warga = Warga::find($pejabatRW->id_warga);
                    $user = $warga ? User::find($warga->id_users) : null;
                    $rw->pejabat = [
                        'nama' => $warga->nama ?? null,
                        'email' => $user->email ?? null,
                        'periode' => $pejabatRW->periode,
                        'ttd' => $pejabatRW->ttd,
                    ];
                } else {
                    $rw->pejabat = null;
                }
                return $rw;
            });

            // Ambil semua RT dengan data Pejabat RT terkait
            $dataRT = RT::all()->map(function ($rt) {
                $pejabatRT = pejabatRT::where('id_rt', $rt->id)->first();
                if ($pejabatRT) {
                    $warga = Warga::find($pejabatRT->id_warga);
                    $user = $warga ? User::find($warga->id_users) : null;
                    $rt->pejabat = [
                        'nama' => $warga->nama ?? null,
                        'email' => $user->email ?? null,
                        'periode' => $pejabatRT->periode,
                        'ttd' => $pejabatRT->ttd,
                    ];
                } else {
                    $rt->pejabat = null;
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
                $query->where('status', 'NonAktif');
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
            $countPending = PengajuanSurat::where('status', 'Diajukan')->count();
            $countSelesai = PengajuanSurat::where('status', '!=', 'Diajukan')->count();

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

    public function updateRT(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            // Cari RT dan pejabat RT terkait
            $rt = RT::findOrFail($id);
            $pejabatRT = pejabatRT::where('id_rt', $id)->firstOrFail();
            $warga = Warga::findOrFail($pejabatRT->id_warga);
            $user = User::findOrFail($warga->id_users);

            $request->validate([
                'nama_rt' => 'required|string',
                'email' => 'required|email|unique:users,email,'.$user->id,
                'periode' => 'required|string',
                'ttd' => 'nullable|string',
            ]);

            // Update RT nama
            $rt->nama_rt = $request->nama_rt;
            $rt->save();

            // Update user email
            $user->email = $request->email;
            $user->save();

            // Update pejabat RT
            $pejabatRT->periode = $request->periode;
            if ($request->has('ttd')) {
                $pejabatRT->ttd = $request->ttd;
            }
            $pejabatRT->save();

            DB::commit();
            return response()->json(['message' => 'RT berhasil diperbarui'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Terjadi kesalahan saat update RT',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateRW(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $rw = RW::findOrFail($id);
            $pejabatRW = pejabatRW::where('id_rw', $id)->firstOrFail();
            $warga = Warga::findOrFail($pejabatRW->id_warga);
            $user = User::findOrFail($warga->id_users);

            $request->validate([
                'nama_rw' => 'required|string',
                'email' => 'required|email|unique:users,email,'.$user->id,
                'periode' => 'required|string',
                'ttd' => 'nullable|string',
            ]);

            // Update RW nama
            $rw->nama_rw = $request->nama_rw;
            $rw->save();

            // Update user email
            $user->email = $request->email;
            $user->save();

            // Update pejabat RW
            $pejabatRW->periode = $request->periode;
            if ($request->has('ttd')) {
                $pejabatRW->ttd = $request->ttd;
            }
            $pejabatRW->save();

            DB::commit();
            return response()->json(['message' => 'RW berhasil diperbarui'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Terjadi kesalahan saat update RW',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

