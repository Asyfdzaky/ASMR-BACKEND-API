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
        $monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];

        $result = [];
        for ($m = 0; $m < 12; $m++) {
            $result[] = [
                'name' => $monthNames[$m],
                'diterima' => 0,
                'ditolak' => 0,
            ];
        }

        // Ambil data jumlah pengajuan per bulan dan status di tahun tertentu
        $data = PengajuanSurat::select(
                    DB::raw('MONTH(created_at) as bulan'),
                    'status',
                    DB::raw('COUNT(*) as total_status')
                )
                ->whereYear('created_at', $year)
                ->whereIn('status', ['Disetujui', 'Selesai', 'Ditolak'])
                ->groupBy(DB::raw('MONTH(created_at)'), 'status')
                ->orderBy('bulan')
                ->get();

        foreach ($data as $item) {
            $monthIndex = $item->bulan - 1;

            if ($monthIndex >= 0 && $monthIndex < 12) {
                if (in_array($item->status, ['Disetujui', 'Selesai'])) {
                    $result[$monthIndex]['diterima'] += $item->total_status;
                } elseif ($item->status == 'Ditolak') {
                    $result[$monthIndex]['ditolak'] += $item->total_status;
                }
            }
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
