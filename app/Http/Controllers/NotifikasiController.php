<?php

namespace App\Http\Controllers;

use App\Models\Notifikasi;
use App\Models\User;
use App\Models\Warga;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotifikasiController extends Controller
{
    public function getNotificationCounts(Request $request)
    {
        $user = Auth::user();
        $counts = [];

        $notifikasiProker = Notifikasi::where('id_user', $user->id)->where('jenis_notif', 'proker')->count();

        switch ($user->role) {
            case 'Admin':
                $rekapCount = Notifikasi::where('jenis_notif', 'surat')->
                    where('id_user', $user->id)
                    ->count();

                $counts = [
                    ['route' => 'dashboard', 'count' => $notifikasiProker],
                    ['route' => 'approval-role', 'count' => $rekapCount]
                ];
                break;

            case 'PejabatRT':
            case 'PejabatRW':
                $rekapCount = Notifikasi::where('jenis_notif', 'surat')
                    ->where('id_user', $user->id)
                    ->count();

                $counts = [
                    ['route' => 'dashboard', 'count' => $notifikasiProker],
                    ['route' => 'rekap-pengajuan', 'count' => 0],
                    ['route' => 'pengajuan-masalah', 'count' => $rekapCount]
                ];
                break;
            case 'Warga':
                $warga = Warga::where('id_users', $user->id)->first();
                
                if ($warga) {
                    $historiCount = Notifikasi::where('id_user', $user->id)
                        ->where('jenis_notif', 'surat')
                        ->count();

                    $counts = [
                        ['route' => 'dashboard', 'count' => $notifikasiProker],
                        ['route' => 'histori', 'count' => $historiCount]
                    ];
                }
                break;
        }

        return response()->json($counts);
    }

    public function clearNotification(Request $request)
    {
        $request->validate([
            'jenis' => 'required|in:surat,proker,lainnya',
        ]);
        
        $user = Auth::user();
        $idUser = $user->id;
        $jenis_notif = $request->jenis;

        $notifikasi = Notifikasi::where('id_user', $idUser)
            ->where('jenis_notif', $jenis_notif);

        if ($notifikasi) {
            $notifikasi->delete();
            return response()->json([
                'status' => 'success',
                'message' => 'Notifikasi berhasil dihapus.',
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Notifikasi tidak ditemukan.',
            ]);
        }
    }
}
