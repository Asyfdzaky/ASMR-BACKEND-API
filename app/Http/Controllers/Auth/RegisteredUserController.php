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
/**
* @OA\Info(
* version="1.0.0",
* title="ASMR Backend API Documentation",
* description="API Documentation for the ASMR project backend, managing residents, officials, letter submissions, and approvals.",
* @OA\Contact(
* email="asyfdzaky@example.com"
* )
* )
*
* @OA\Server(
* url=L5_SWAGGER_CONST_HOST,
* description="ASMR API Server"
* )
*
* @OA\SecurityScheme(
* securityScheme="bearerAuth",
* in="header",
* name="bearerAuth",
* type="http",
* scheme="bearer",
* bearerFormat="JWT",
* )
*/

class RegisteredUserController extends Controller
{
    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    /**
     * Register a new resident user.
     *
     * @OA\Post(
     * path="/api/register",
     * operationId="registerUser",
     * tags={"Authentication"},
     * summary="Register a new resident user",
     * description="Registers a new user with 'warga' role and creates associated resident and address data. Account requires admin activation.",
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * required={"email","password","nama","nomer_kk","nik","jenis_kelamin","phone","tempat_lahir","tanggal_lahir","id_rt","id_rw","alamat","kabupaten","provinsi"},
     * @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
     * @OA\Property(property="password", type="string", format="password", example="password"),
     * @OA\Property(property="nama", type="string", example="John Doe"),
     * @OA\Property(property="nomer_kk", type="string", example="1234567890123456"),
     * @OA\Property(property="nik", type="string", example="1234567890123456"),
     * @OA\Property(property="jenis_kelamin", type="string", enum={"laki-laki", "perempuan"}, example="laki-laki"),
     * @OA\Property(property="phone", type="string", example="081234567890"),
     * @OA\Property(property="tempat_lahir", type="string", example="Jakarta"),
     * @OA\Property(property="tanggal_lahir", type="string", format="date", example="1990-01-01"),
     * @OA\Property(property="id_rt", type="integer", example="1"),
     * @OA\Property(property="id_rw", type="integer", example="1"),
     * @OA\Property(property="alamat", type="string", example="Jl. Contoh No. 1"),
     * @OA\Property(property="kabupaten", type="string", example="Sleman"),
     * @OA\Property(property="provinsi", type="string", example="DI Yogyakarta"),
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Registration successful, awaiting admin activation.",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="Registrasi berhasil! Tunggu aktivasi dari admin."),
     * @OA\Property(property="data", type="object",
     * @OA\Property(property="user", type="object"),
     * @OA\Property(property="warga", type="object")
     * )
     * )
     * ),
     * @OA\Response(
     * response=400,
     * description="RT and RW mismatch",
     * @OA\JsonContent(
     * @OA\Property(property="error", type="string", example="RT dan RW tidak cocok")
     * )
     * ),
     * @OA\Response(
     * response=422,
     * description="Validation error",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="The given data was invalid."),
     * @OA\Property(property="errors", type="object")
     * )
     * ),
     * @OA\Response(
     * response=500,
     * description="Server error",
     * @OA\JsonContent(
     * @OA\Property(property="error", type="string")
     * )
     * )
     * )
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
            "jenis_kelamin" => "required|in:Laki-Laki,Perempuan",
            "phone" => "required|numeric",
            "agama" => "required|in:Islam,Kristen,Katolik,Hindu,Buddha,Khonghucu",
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
            "role" => "Warga",
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
            "agama" => $request->agama,
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