<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Warga;
use Illuminate\Http\Request;

class ApprovalRoleController extends Controller
{
    public function getWarga(){
        try{
            $Warga = Warga::with('rt.rw', 'user')
                        ->whereHas('user', function ($query) {
                            $query->where('role', 'warga');
                        })
                        ->get();  
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
        # error	"Call to undefined method Illuminate\\Database\\Eloquent\\Relations\\BelongsTo::save()"
        try{
            $Warga = Warga::find($id);
            $user = $Warga->user;
            $user->status_akun = 1;
            $user->save();
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
            $user = $Warga->user;
            $user->status_akun = 2;
            $user->save();
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
