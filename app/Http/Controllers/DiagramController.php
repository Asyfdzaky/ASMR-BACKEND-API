<?php

namespace App\Http\Controllers;

use App\Models\PengajuanSurat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DiagramController extends Controller
{
     // Data pengajuan surat per bulan untuk grafik (tahun ini)
    public function jumlahPengajuanPerBulan(Request $request)
    {
        $year = $request->input('year', date('Y'));

        // Ambil data jumlah pengajuan per bulan di tahun tertentu
        $data = PengajuanSurat::select(
                    DB::raw('MONTH(created_at) as bulan'),
                    DB::raw('COUNT(*) as total')
                )
                ->whereYear('created_at', $year)
                ->groupBy(DB::raw('MONTH(created_at)'))
                ->orderBy('bulan')
                ->get();

        // Format hasil agar bulan yang kosong tetap muncul (opsional)
        $result = [];
        for ($m = 1; $m <= 12; $m++) {
            $found = $data->firstWhere('bulan', $m);
            $result[] = [
                'bulan' => $m,
                'total' => $found ? $found->total : 0,
            ];
        }

        return response()->json($result);
    }

    // Data jumlah pengajuan berdasarkan jenis surat
    public function jumlahPengajuanPerJenis()
    {
        $data = PengajuanSurat::select('jenis_surat', DB::raw('COUNT(*) as total'))
                ->groupBy('jenis_surat')
                ->get();

        return response()->json($data);
    }
}
