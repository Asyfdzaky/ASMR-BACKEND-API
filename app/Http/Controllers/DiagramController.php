<?php

namespace App\Http\Controllers;

use App\Models\PengajuanSurat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DiagramController extends Controller
{
    public function jumlahPengajuanPerBulan(Request $request)
    {
        $year = $request->input('year', date('Y'));
        $half = $request->input('half');
        $monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];

        $data = PengajuanSurat::select(
            DB::raw('MONTH(created_at) as bulan'),
            'status',
            'jenis_surat',
            'id_rw',
            'created_at'
        )->with('rw')
            ->whereYear('created_at', $year)
            ->whereIn('status', ['Disetujui', 'Selesai', 'Ditolak'])
            ->orderBy('created_at')
            ->get();

        $result = [];
        foreach ($monthNames as $index => $name) {
            $result[$index] = [
                'bulan' => $name,
                'pengajuan' => [] 
            ];
        }

        foreach ($data as $item) {
            $monthIndex = $item->bulan - 1;

            $result[$monthIndex]['pengajuan'][] = [
                'status' => $item->status,
                'jenis_surat' => $item->jenis_surat,
                'id_rw' => $item->id_rw,
                'nama_rw' => $item->rw ? $item->rw->nama_rw : null,
                'tanggal' => $item->created_at->format('Y-m-d H:i:s'),
            ];
        }

        if ($half === 'start') {
            $result = array_slice($result, 0, 6);
        } elseif ($half === 'end') {
            $result = array_slice($result, 6, 6);
        }

        return response()->json(array_values($result));
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
