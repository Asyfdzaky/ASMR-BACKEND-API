<?php

namespace App\Http\Controllers;

use App\Models\RW;
use Illuminate\Http\Request;

class GetRtRW extends Controller
{
    public function GetRW(){
        $rw = RW::all();
        return response()->json([
            'success' => true,
            'message' => 'Detail Data RW',
            'data' => $rw
        ], 200);
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
}
