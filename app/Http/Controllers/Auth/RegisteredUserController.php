<?php

namespace App\Http\Controllers\Auth;

use Exception;
use App\Models\RT;
use App\Models\User;
use App\Models\Warga;
use App\Models\DetailAlamat;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;

class RegisteredUserController extends Controller
{
    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        try {
        // Mulai transaksi database
        DB::beginTransaction();
            $request->validate([
            "email" => "required|email|unique:users",
            'password' => ['required',  Rules\Password::defaults()],
            "nama" => "required|string",
            "nomer_kk" => "required|numeric",
            "nik" => "required|numeric",
            "jenis_kelamin" => "required|in:laki-laki,perempuan",
            "phone" => "required|numeric",
            "tempat_lahir" => "required|string",
            "tanggal_lahir" => "required|date",
            'id_rt' => 'required|exists:rt,id',
            'id_rw' => 'required|exists:rw,id',
            "alamat" => "required|string",
            "kabupaten" => "required|string",
            "provinsi" => "required|string",
        ]);
        $rt = RT::find($request->id_rt);
        if ($rt->id_rw != $request->id_rw) {
            return response()->json(['error' => 'RT dan RW tidak cocok'], 400);
        }
        // Buat user baru
        $user = User::create([
            "email" => $request->email,
            "password" => Hash::make($request->password),
            "role" => "warga",
            "statusAkun" => false,
        ]);
        // Buat detail alamat
        $detailAlamat = DetailAlamat::create([
            "alamat" => $request->alamat,
            "kabupaten" => $request->kabupaten,
            "provinsi" => $request->provinsi,
        ]); 
        // Buat warga
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
        // Commit transaksi jika semua berhasil
        DB::commit();
        event(new Registered($user));
        return response()->json([
            "message" => "Registrasi berhasil! Tunggu aktivasi dari admin.",
            "data" => [
                "user" => $user,
                "warga" => $warga,
            ],
        ], 200);
    } catch (\Exception $e) {
        // Rollback jika terjadi error
        DB::rollBack();
        return response()->json(['error' => $e->getMessage()], 500);
    }
}
}