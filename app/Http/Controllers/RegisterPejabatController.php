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

            $baseRules = [
                'id_rt' => 'nullable|exists:rt,id',
                'id_rw' => 'nullable|exists:rw,id',
                'periode_mulai' => 'required|string',
                'periode_selesai' => 'required|string',
                'role' => 'required|in:PejabatRT,PejabatRW',
                'ttd' => 'required|file|mimes:png,jpg,jpeg|max:2048',
            ];

            if ($request->has('id_warga')) {
                $request->validate(array_merge($baseRules, [
                    'id_warga' => 'required|exists:wargas,id',
                ]));

                $warga = Warga::with('user')->findOrFail($request->id_warga);
                $user = $warga->user;

                
                if ($request->role == "PejabatRT" && $warga->id_rt != $request->id_rt) {
                    $rt = RT::findOrFail($request->id_rt);
                    if ($rt->id_rw != $request->id_rw) {
                        return response()->json(['error' => 'RT dan RW tidak cocok'], 400);
                    }
                     return response()->json(['error' => 'Pejabat RT harus berasal dari RT yang bersangkutan.'], 400);
                }

                $wargaRt = RT::find($warga->id_rt);
                if ($request->role == "PejabatRW" && (!$wargaRt || $wargaRt->id_rw != $request->id_rw)) {
                    return response()->json(['error' => 'Pejabat RW harus berasal dari RW yang bersangkutan.'], 400);
                }

                $ttdUrl = null;
                if ($request->hasFile('ttd')) {
                    $ttdPath = $request->file('ttd')->store('public/ttd');
                    $ttdUrl = Storage::url($ttdPath);
                }

                if ($user->role !== $request->role) {
                    $user->update(['role' => $request->role]);
                }

                if ($user->status != 1) {
                    $user->update(['status' => 1]);
                }

                if ($request->role == "PejabatRT") {
                    pejabatRT::create([
                        "id_rt" => $request->id_rt,
                        "id_warga" => $warga->id,
                        "periode_mulai" => $request->periode_mulai,
                        "periode_selesai" => $request->periode_selesai,
                        "ttd" => $ttdUrl,
                    ]);
                } elseif ($request->role == "PejabatRW") {
                    pejabatRW::create([
                        "id_rw" => $request->id_rw,
                        "id_warga" => $warga->id,
                        "periode_mulai" => $request->periode_mulai,
                        "periode_selesai" => $request->periode_selesai,
                        "ttd" => $ttdUrl,
                    ]);
                }

                DB::commit();
                return response()->json([
                    "message" => "Pejabat " . $request->role . " berhasil ditambahkan",
                    "data" => [
                        "user" => $user->fresh(),
                        "warga" => $warga,
                        "ttd" => $ttdUrl,
                        "role" => $request->role,
                        "periode_mulai" => $request->periode_mulai,
                        "periode_selesai" => $request->periode_selesai,
                        "id_rt" => $request->id_rt,
                        "id_rw" => $request->id_rw,
                    ],
                ], 200);


            } 

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Validasi gagal',
                'messages' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Registrasi gagal',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
//     public function update(Request $request, $id)
// {
//     try {
//         DB::beginTransaction();

//         $request->validate([
//             "nama" => "sometimes|string",
//             "phone" => "sometimes|numeric",
//             "alamat" => "sometimes|string",
//             "kabupaten" => "sometimes|string",
//             "provinsi" => "sometimes|string",
//             'periode_mulai' => 'sometimes|string',
//             'periode_selesai' => 'sometimes|string',
//             'ttd' => 'sometimes|file|mimes:png,jpg,jpeg|max:2048',
//         ]);

//         // Find the warga record
//         $warga = Warga::findOrFail($id);
        
//         // Update basic warga info
//         $warga->update($request->only([
//             'nama', 'phone'
//         ]));

//         // Update address if provided
//         if ($request->has('alamat') || $request->has('kabupaten') || $request->has('provinsi')) {
//             $warga->alamat()->update([
//                 'alamat' => $request->alamat ?? $warga->alamat->alamat,
//                 'kabupaten' => $request->kabupaten ?? $warga->alamat->kabupaten,
//                 'provinsi' => $request->provinsi ?? $warga->alamat->provinsi
//             ]);
//         }

//         // Handle signature update
//         $ttdUrl = null;
//         if ($request->hasFile('ttd')) {
//             // Delete old signature if exists
//             if ($warga->pejabatRT || $warga->pejabatRW) {
//                 $oldTtd = $warga->pejabatRT ? $warga->pejabatRT->ttd : $warga->pejabatRW->ttd;
//                 Storage::delete(str_replace('/storage', 'public', $oldTtd));
//             }
            
//             // Store new signature
//             $ttdPath = $request->file('ttd')->store('public/ttd');
//             $ttdUrl = Storage::url($ttdPath);
//         }

//         // Update pejabat data
//         if ($warga->pejabatRT) {
//             $warga->pejabatRT->update([
//                 'periode_mulai' => $request->periode_mulai ?? $warga->pejabatRT->periode_mulai,
//                 'periode_selesai' => $request->periode_selesai ?? $warga->pejabatRT->periode_selesai,
//                 'ttd' => $ttdUrl ?? $warga->pejabatRT->ttd
//             ]);
//         } elseif ($warga->pejabatRW) {
//             $warga->pejabatRW->update([
//                 'periode_mulai' => $request->periode_mulai ?? $warga->pejabatRW->periode_mulai,
//                 'periode_selesai' => $request->periode_selesai ?? $warga->pejabatRW->periode_selesai,
//                 'ttd' => $ttdUrl ?? $warga->pejabatRW->ttd
//             ]);
//         }

//         DB::commit();

//         return response()->json([
//             "message" => "Data pejabat berhasil diperbarui",
//             "data" => $warga->load(['user', 'alamat', 'pejabatRT', 'pejabatRW'])
//         ], 200);

//     } catch (\Exception $e) {
//         DB::rollBack();
//         return response()->json([
//             'error' => 'Gagal memperbarui data',
//             'message' => $e->getMessage()
//         ], 500);
//     }
// }

public function storeJabatanRT(Request $request){
    try {
        $request->validate([
            'nama_rt' => 'required|string',
            'id_rw' => 'required|exists:rw,id',
        ]);

        $rt = RT::create([
            'nama_rt' => $request->nama_rt,
            'id_rw' => $request->id_rw,
        ]);

        return response()->json([
            'message' => 'Jabatan RT berhasil ditambahkan',
            'data' => $rt,
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Terjadi kesalahan saat menambahkan jabatan RT',
            'error' => $e->getMessage()
        ], 500);
    }
}

public function storeJabatanRW(Request $request){
    try {
        $request->validate([
            'nama_rw' => 'required|string',
        ]);

        $rw = RW::create([
            'nama_rw' => $request->nama_rw,
        ]);

        return response()->json([
            'message' => 'Jabatan RW berhasil ditambahkan',
            'data' => $rw,
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Terjadi kesalahan saat menambahkan jabatan RW',
            'error' => $e->getMessage()
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


public function updateRT(Request $request, $id)
{
    DB::beginTransaction();
    try {
        $pejabatRT = pejabatRT::where('id_rt', $id)->firstOrFail();
        $warga = Warga::findOrFail($pejabatRT->id_warga);
        $user = User::findOrFail($warga->id_users);

        $request->validate([
            'id_warga' => 'required|string',
            'periode_mulai' => 'required|integer',
            'periode_selesai' => 'required|integer',
            'ttd' => 'nullable|file|mimes:png,jpg,jpeg|max:2048',
        ]);

        $isChangeWarga = $request->id_warga != $warga->id;

        if ($isChangeWarga) {
            $user->role = 'Warga';
            $user->save();
            $newWarga = Warga::findOrFail($request->id_warga);
            $newUser = User::findOrFail($newWarga->id_users);
            $newUser->role = 'PejabatRT';
            $newUser->save();
        }

        // Update pejabat RT
        $pejabatRT->id_warga = $request->id_warga;
        $pejabatRT->periode_mulai = $request->periode_mulai;
        $pejabatRT->periode_selesai = $request->periode_selesai;
        if ($request->hasFile('ttd')) {
            if ($pejabatRT->ttd) {
                Storage::delete(str_replace('/storage', 'public', $pejabatRT->ttd));
            }
            
            $ttdPath = $request->file('ttd')->store('public/ttd');
            $ttdUrl = Storage::url($ttdPath);
            $pejabatRT->ttd = $ttdUrl;
        }
        $pejabatRT->save();

        DB::commit();
        return response()->json(['message' => 'RT berhasil diperbarui ' . ($isChangeWarga ? $warga->nama . ' menjadi ' . $newWarga->nama : '')], 200);
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
        $pejabatRW = pejabatRW::where('id_rw', $id)->firstOrFail();
        $warga = Warga::findOrFail($pejabatRW->id_warga);
        $user = User::findOrFail($warga->id_users);

        $request->validate([
            'id_warga' => 'required|string',
            'periode_mulai' => 'required|integer',
            'periode_selesai' => 'required|integer',
            'ttd' => 'nullable|file|mimes:png,jpg,jpeg|max:2048',
        ]);

        $isChangeWarga = $request->id_warga != $warga->id;

        if ($isChangeWarga) {
            // Update role user lama menjadi Warga
            $user->role = 'Warga';
            $user->save();

            // Update role user baru menjadi PejabatRW
            $newWarga = Warga::findOrFail($request->id_warga);
            $newUser = User::findOrFail($newWarga->id_users);
            $newUser->role = 'PejabatRW';
            $newUser->save();
        }

        // Update pejabat RW
        $pejabatRW->id_warga = $request->id_warga;
        $pejabatRW->periode_mulai = $request->periode_mulai;
        $pejabatRW->periode_selesai = $request->periode_selesai;
        if ($request->has('ttd')) {
            if ($pejabatRW->ttd) {
                Storage::delete(str_replace('/storage', 'public', $pejabatRW->ttd));
            }
            
            $ttdPath = $request->file('ttd')->store('public/ttd');
            $ttdUrl = Storage::url($ttdPath);
            $pejabatRW->ttd = $ttdUrl;
        }
        $pejabatRW->save();

        DB::commit();
        return response()->json(['message' => 'RW berhasil diperbarui ' . ($isChangeWarga ? $warga->nama . ' menjadi ' . $newWarga->nama : '')], 200);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'message' => 'Terjadi kesalahan saat update RW',
            'error' => $e->getMessage(),
        ], 500);
    }
}

public function deleteRT(Request $request, $id)
{
    DB::beginTransaction();
    try {
        $pejabatRT = pejabatRT::where('id_rt', $id)->first();

        if (!$pejabatRT) {
            DB::rollBack();
            return response()->json(['error' => 'Pejabat RT not found for the given RT ID'], 404);
        }
        
        $warga = Warga::findOrFail($pejabatRT->id_warga);
        $user = User::findOrFail($warga->id_users);

        if ($pejabatRT && $pejabatRT->ttd) {
            Storage::delete(str_replace('/storage', 'public', $pejabatRT->ttd));
        }

        $pejabatRT->delete();
        $user->role = 'Warga';
        $user->save();

        DB::commit();
        return response()->json(['message' => 'RT berhasil dihapus'], 200);
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        DB::rollBack();
        return response()->json([
            'error' => 'Data not found',
            'message' => $e->getMessage()
        ], 404);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'message' => 'Terjadi kesalahan saat menghapus RT',
            'error' => $e->getMessage(),
        ], 500);
    }
}

public function deleteRW(Request $request, $id)
{
    DB::beginTransaction();
    try {
        $pejabatRW = pejabatRW::where('id_rw', $id)->firstOrFail();
        $warga = Warga::findOrFail($pejabatRW->id_warga);
        $user = User::findOrFail($warga->id_users);

        if ($pejabatRW && $pejabatRW->ttd) {
            Storage::delete(str_replace('/storage', 'public', $pejabatRW->ttd));
        }

        $pejabatRW->delete();
        $user->role = 'Warga';
        $user->save();

        DB::commit();

        return response()->json(['message' => 'RW berhasil dihapus'], 200);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'message' => 'Terjadi kesalahan saat menghapus RW',
            'error' => $e->getMessage(),
        ], 500);
    }
}

public function getRTDetails($id_rt_entity)
{
    try {
        $rt = RT::with('rw')->find($id_rt_entity);

        if (!$rt) {
            return response()->json([
                'success' => false,
                'message' => 'RT tidak ditemukan',
            ], 404);
        }

        $pejabatModel = pejabatRT::where('id_rt', $rt->id)
                            ->with(['warga', 'warga.user']) 
                            ->latest('periode_mulai')
                            ->first();

        $pejabatDetails = null;
        if ($pejabatModel && $pejabatModel->warga) {
            $pejabatDetails = [
                'id_pejabat_rt' => $pejabatModel->id,
                'id_warga' => $pejabatModel->id_warga,
                'nama_warga' => $pejabatModel->warga->nama,
                'nik_warga' => $pejabatModel->warga->nik,
                'user_email' => $pejabatModel->warga->user ? $pejabatModel->warga->user->email : null,
                'periode_mulai' => $pejabatModel->periode_mulai,
                'periode_selesai' => $pejabatModel->periode_selesai,
                'ttd_url' => $pejabatModel->ttd,
            ];
        }

        $rtDetails = [
            'id' => $rt->id, 
            'nama_rt' => $rt->nama_rt,
            'id_rw' => $rt->id_rw, 
            'rw' => $rt->rw ? [
                'id_rw' => $rt->rw->id, 
                'nama_rw' => $rt->rw->nama_rw
            ] : null,
            'pejabat' => $pejabatDetails
        ];

        return response()->json([
            'success' => true,
            'message' => 'Detail Data RT berhasil diambil',
            'data' => $rtDetails
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Gagal mengambil detail data RT',
            'error' => $e->getMessage()
        ], 500);
    }
}

public function getRWDetails($id_rw_entity)
{
    try {
        $rw = RW::find($id_rw_entity);

        if (!$rw) {
            return response()->json([
                'success' => false,
                'message' => 'RW tidak ditemukan',
            ], 404);
        }

        $pejabatModel = pejabatRW::where('id_rw', $rw->id) 
                            ->with(['warga', 'warga.user']) 
                            ->latest('periode_mulai')
                            ->first();

        $pejabatDetails = null;
        if ($pejabatModel && $pejabatModel->warga) {
            $pejabatDetails = [
                'id_pejabat_rw' => $pejabatModel->id,
                'id_warga' => $pejabatModel->id_warga,
                'nama_warga' => $pejabatModel->warga->nama,
                'nik_warga' => $pejabatModel->warga->nik,
                'user_email' => $pejabatModel->warga->user ? $pejabatModel->warga->user->email : null,
                'periode_mulai' => $pejabatModel->periode_mulai,
                'periode_selesai' => $pejabatModel->periode_selesai,
                'ttd_url' => $pejabatModel->ttd,
            ];
        }

        $rwDetails = [
            'id_rw' => $rw->id, 
            'nama_rw' => $rw->nama_rw,
            'pejabat' => $pejabatDetails
        ];

        return response()->json([
            'success' => true,
            'message' => 'Detail Data RW berhasil diambil',
            'data' => $rwDetails
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Gagal mengambil detail data RW',
            'error' => $e->getMessage()
        ], 500);
    }
}
}