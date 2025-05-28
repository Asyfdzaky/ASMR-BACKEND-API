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
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Storage;

class RegisterPejabatController extends Controller
{
    public function Store(Request $request){
        try {
            DB::beginTransaction();

            $request->validate([
                "email" => "required|email|unique:users",
                "password" => ['required', Rules\Password::defaults()],
                "nama" => "required|string",
                "nomer_kk" => "required|numeric|unique:wargas,nomor_kk",
                "nik" => "required|numeric|unique:wargas,nik",
                "jenis_kelamin" => "required|in:Laki-Laki,Perempuan",
                "phone" => "required|numeric",
                "tempat_lahir" => "required|string",
                "tanggal_lahir" => "required|date",
                'id_rt' => 'required|exists:rt,id',
                'id_rw' => 'required|exists:rw,id',
                "alamat" => "required|string",
                "kabupaten" => "required|string",
                "provinsi" => "required|string",
                'periode' => 'required|string',
                'role' => 'required',
                'ttd' => 'required|file|mimes:png,jpg,jpeg|max:2048',
            ]);

            // Verify RT belongs to RW
            $rt = RT::findOrFail($request->id_rt);
            if ($rt->id_rw != $request->id_rw) {
                return response()->json(['error' => 'RT dan RW tidak cocok'], 400);
            }

            // Upload signature
            $ttdPath = $request->file('ttd')->store('public/ttd');
            $ttdUrl = Storage::url($ttdPath);

            // Create user
            $user = User::create([
                "email" => $request->email,
                "password" => Hash::make($request->password),
                "role" => $request->role, // Default role, will be updated if needed
                "status" => 1, // Needs admin activation
            ]);

            // Create address
            $detailAlamat = DetailAlamat::create([
                "alamat" => $request->alamat,
                "kabupaten" => $request->kabupaten,
                "provinsi" => $request->provinsi,
            ]);

            // Create resident
            $warga = Warga::create([
                "id_users" => $user->id,
                "id_alamat" => $detailAlamat->id,
                "id_rt" => $request->id_rt,
                "nama" => $request->nama,
                "nomor_kk" => $request->nomer_kk,
                "nik" => $request->nik,
                "jenis_kelamin" => $request->jenis_kelamin,
                "phone" => $request->phone,
                "tempat_lahir" => $request->tempat_lahir,
                "tanggal_lahir" => $request->tanggal_lahir,
            ]);

            // Assign official position
            if ($request->role == "PejabatRT") {
                pejabatRT::create([
                    "id_rt" => $request->id_rt,
                    "id_warga" => $warga->id,
                    "periode" => $request->periode,
                    "ttd" => $ttdUrl,
                ]); 
            } elseif ($request->role == "PejabatRW") {
                pejabatRW::create([
                    "id_rw" => $request->id_rw,
                    "id_warga" => $warga->id,
                    "periode" => $request->periode,
                    "ttd" => $ttdUrl,   
                ]);
            }

            DB::commit();
            event(new Registered($user));
            return response()->json([
                "message" => "Registrasi pejabat berhasil",
                "data" => [
                    "user" => $user,
                    "warga" => $warga,
                    "detailAlamat" => $detailAlamat,
                    "ttd" => $ttdUrl,
                    "role" => $request->role,
                    "pejabat" => $request->role,
                    "periode" => $request->periode,
                    "id_rt" => $request->id_rt,                
                    "id_rw" => $request->id_rw,
                ],
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Registrasi gagal',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function update(Request $request, $id)
{
    try {
        DB::beginTransaction();

        $request->validate([
            "nama" => "sometimes|string",
            "phone" => "sometimes|numeric",
            "alamat" => "sometimes|string",
            "kabupaten" => "sometimes|string",
            "provinsi" => "sometimes|string",
            'periode' => 'sometimes|string',
            'ttd' => 'sometimes|file|mimes:png,jpg,jpeg|max:2048',
        ]);

        // Find the warga record
        $warga = Warga::findOrFail($id);
        
        // Update basic warga info
        $warga->update($request->only([
            'nama', 'phone'
        ]));

        // Update address if provided
        if ($request->has('alamat') || $request->has('kabupaten') || $request->has('provinsi')) {
            $warga->alamat()->update([
                'alamat' => $request->alamat ?? $warga->alamat->alamat,
                'kabupaten' => $request->kabupaten ?? $warga->alamat->kabupaten,
                'provinsi' => $request->provinsi ?? $warga->alamat->provinsi
            ]);
        }

        // Handle signature update
        $ttdUrl = null;
        if ($request->hasFile('ttd')) {
            // Delete old signature if exists
            if ($warga->pejabatRT || $warga->pejabatRW) {
                $oldTtd = $warga->pejabatRT ? $warga->pejabatRT->ttd : $warga->pejabatRW->ttd;
                Storage::delete(str_replace('/storage', 'public', $oldTtd));
            }
            
            // Store new signature
            $ttdPath = $request->file('ttd')->store('public/ttd');
            $ttdUrl = Storage::url($ttdPath);
        }

        // Update pejabat data
        if ($warga->pejabatRT) {
            $warga->pejabatRT->update([
                'periode' => $request->periode ?? $warga->pejabatRT->periode,
                'ttd' => $ttdUrl ?? $warga->pejabatRT->ttd
            ]);
        } elseif ($warga->pejabatRW) {
            $warga->pejabatRW->update([
                'periode' => $request->periode ?? $warga->pejabatRW->periode,
                'ttd' => $ttdUrl ?? $warga->pejabatRW->ttd
            ]);
        }

        DB::commit();

        return response()->json([
            "message" => "Data pejabat berhasil diperbarui",
            "data" => $warga->load(['user', 'alamat', 'pejabatRT', 'pejabatRW'])
        ], 200);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'error' => 'Gagal memperbarui data',
            'message' => $e->getMessage()
        ], 500);
    }
}

public function getWargaByNIK($nik)
{
    try {
        $dataWarga = Warga::where('nik', $nik)->with(['alamat', 'user'])->get();
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
        $pejabatData = $warga->pejabatRT ?: $warga->pejabatRW;
        $role = $pejabatData ? ($warga->pejabatRT ? 'RT' : 'RW') : 'Warga';
        
        // 1. Delete signature file if exists
        if ($pejabatData && $pejabatData->ttd) {
            Storage::delete(str_replace('/storage', 'public', $pejabatData->ttd));
        }

        // 2. Delete official position if exists
        if ($pejabatData) {
            $pejabatData->delete();
        }

        // 3. Delete address
        if ($warga->alamat) {
            $warga->alamat()->delete();
        }

        // 4. Delete warga record
        $warga->delete();

        // 5. Delete user account (optional - can be made configurable)
        $deleteUser = $request->input('delete_user', true);
        if ($deleteUser) {
            $user->delete();
        }

        DB::commit();

        return response()->json([
            "message" => "Data berhasil dihapus",
            "deleted" => [
                "role" => $role,
                "warga_id" => $warga->id,
                "user_id" => $user->id,
                "user_deleted" => $deleteUser
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
