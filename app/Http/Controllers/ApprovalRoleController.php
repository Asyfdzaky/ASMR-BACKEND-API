<?php

namespace App\Http\Controllers;

use App\Models\Warga;
use Illuminate\Http\Request;

class ApprovalRoleController extends Controller
{
    public function getWarga(){
        try{
            $Warga = Warga::with('rt.rw')->get();  
            return response()->json([
            'message' => 'Berhasil mengambil data warga',
            'data' => $Warga
        ], 200);
        }catch(\Exception $e){
            return response()->json([
                'message' => 'Gagal mengambil data warga',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function ApproveWarga($id){
        try{
            $Warga = Warga::find($id);
            $Warga->status = true;
            $Warga->save();
            return response()->json([
                'message' => 'Berhasil mengapprove warga',
                'data' => $Warga
            ], 200);
        }catch(\Exception $e){
            return response()->json([
                'message' => 'Gagal mengapprove warga',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function RejectWarga($id){
        try{
            $Warga = Warga::find($id);
            $Warga->status = false;
            $Warga->save();
            return response()->json([
                'message' => 'Berhasil menolak warga',
                'data' => $Warga
            ], 200);
        }catch(\Exception $e){
            return response()->json([
                'message' => 'Gagal menolak warga',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
