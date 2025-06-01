<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Warga;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    /**
     * Get the authenticated user's complete profile details.
     * Includes User, Warga, and Address (DetailAlamat) information.
     *
     * @OA\Get(
     * path="/api/profile",
     * operationId="getAuthenticatedUserProfile",
     * tags={"User Profile"},
     * summary="Get authenticated user's complete profile",
     * description="Retrieves the detailed profile information for the currently authenticated user, including their associated resident (Warga) and address (DetailAlamat) data.",
     * security={{"bearerAuth":{}}},
     * @OA\Response(
     * response=200,
     * description="Successfully retrieved profile data",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="Profil berhasil diambil."),
     * @OA\Property(property="data", type="object",
     * @OA\Property(property="user", type="object",
     * @OA\Property(property="id", type="integer", example=1),
     * @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     * @OA\Property(property="role", type="string", example="Warga"),
     * @OA\Property(property="status_akun", type="boolean", example=true)
     * ),
     * @OA\Property(property="warga", type="object",
     * @OA\Property(property="id", type="integer", example=1),
     * @OA\Property(property="nama", type="string", example="Nama Lengkap Warga"),
     * @OA\Property(property="nomor_kk", type="string", example="1234567890123456"),
     * @OA\Property(property="nik", type="string", example="1234567890123456"),
     * @OA\Property(property="jenis_kelamin", type="string", enum={"Laki-Laki", "Perempuan"}, example="Laki-Laki"),
     * @OA\Property(property="phone", type="string", example="081234567890"),
     * @OA\Property(property="tempat_lahir", type="string", example="Jakarta"),
     * @OA\Property(property="tanggal_lahir", type="string", format="date", example="1990-01-01"),
     * @OA\Property(property="id_users", type="integer", example=1),
     * @OA\Property(property="id_alamat", type="integer", example=1),
     * @OA\Property(property="id_rt", type="integer", example=1),
     * @OA\Property(property="created_at", type="string", format="date-time"),
     * @OA\Property(property="updated_at", type="string", format="date-time"),
     * @OA\Property(property="rt", type="object", description="RT details"),
     * @OA\Property(property="alamat", type="object", description="Address details")
     * )
     * )
     * )
     * ),
     * @OA\Response(
     * response=401,
     * description="Unauthenticated",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="Unauthenticated.")
     * )
     * ),
     * @OA\Response(
     * response=404,
     * description="Warga data not found for the authenticated user",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="Data warga tidak ditemukan.")
     * )
     * ),
     * @OA\Response(
     * response=500,
     * description="Server error",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="Terjadi kesalahan saat mengambil profil."),
     * @OA\Property(property="error", type="string")
     * )
     * )
     * )
     */
    public function show()
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['message' => 'Pengguna tidak terautentikasi.'], 401);
            }

            // Load warga data with related RT, RW, and Alamat
            // 'rt.rw' ensures that RW data is also loaded through the RT relationship
            $warga = Warga::with(['rt.rw', 'alamat'])->where('id_users', $user->id)->first();

            if (!$warga) {
                return response()->json(['message' => 'Data warga tidak ditemukan.'], 404);
            }

            return response()->json([
                'message' => 'Profil berhasil diambil.',
                'data' => [
                    'user' => $user,
                    'warga' => $warga,
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat mengambil profil.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the authenticated user's profile details.
     * Allows updating email, name, gender, phone, and address details.
     *
     * @OA\Put(
     * path="/api/profile",
     * operationId="updateAuthenticatedUserProfile",
     * tags={"User Profile"},
     * summary="Update authenticated user's profile details",
     * description="Updates the profile information for the currently authenticated user. Fields like email, name, phone, gender, and address details can be updated. NIK, Nomor KK, Tempat Lahir, Tanggal Lahir cannot be updated via this endpoint.",
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * @OA\Property(property="email", type="string", format="email", example="new.email@example.com", description="New email address (optional). Must be unique.", nullable=true),
     * @OA\Property(property="nama", type="string", example="Nama Lengkap Baru", description="New full name (optional)", nullable=true),
     * @OA\Property(property="jenis_kelamin", type="string", enum={"Laki-Laki", "Perempuan"}, example="Perempuan", description="Gender (optional)", nullable=true),
     * @OA\Property(property="phone", type="string", example="081234567899", description="New phone number (optional)", nullable=true),
     * @OA\Property(property="alamat", type="string", example="Jl. Contoh Baru No. 10", description="New street address (optional)", nullable=true),
     * @OA\Property(property="kabupaten", type="string", example="Kabupaten Baru", description="New regency/city (optional)", nullable=true),
     * @OA\Property(property="provinsi", type="string", example="Provinsi Baru", description="New province (optional)", nullable=true)
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Profile updated successfully",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="Profil berhasil diperbarui."),
     * @OA\Property(property="data", type="object",
     * @OA\Property(property="user", type="object", description="Updated User data"),
     * @OA\Property(property="warga", type="object", description="Updated Warga data"),
     * @OA\Property(property="alamat", type="object", description="Updated Address data")
     * )
     * )
     * ),
     * @OA\Response(
     * response=401,
     * description="Unauthenticated",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="Unauthenticated.")
     * )
     * ),
     * @OA\Response(
     * response=422,
     * description="Validation error (e.g., duplicate email, invalid gender)",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="The given data was invalid."),
     * @OA\Property(property="errors", type="object")
     * )
     * ),
     * @OA\Response(
     * response=404,
     * description="Warga data not found for the authenticated user or associated address not found",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="Data warga tidak ditemukan.")
     * )
     * ),
     * @OA\Response(
     * response=500,
     * description="Server error",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="Terjadi kesalahan saat memperbarui profil."),
     * @OA\Property(property="error", type="string")
     * )
     * )
     * )
     */
    public function update(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['message' => 'Pengguna tidak terautentikasi.'], 401);
            }

            $warga = Warga::where('id_users', $user->id)->first();
            if (!$warga) {
                return response()->json(['message' => 'Data warga tidak ditemukan.'], 404);
            }

            $request->validate([
                'email' => ['sometimes', 'email', Rule::unique('users')->ignore($user->id)],
                'nama' => ['sometimes', 'string', 'max:255'],
                'jenis_kelamin' => ['sometimes', 'in:Laki-Laki,Perempuan'],
                'phone' => ['sometimes', 'string', 'max:20'],
                'alamat' => ['sometimes', 'string', 'max:255'],
                'kabupaten' => ['sometimes', 'string', 'max:255'],
                'provinsi' => ['sometimes', 'string', 'max:255'],
            ]);

            DB::beginTransaction();

            // Update User data (email)
            /** @var \App\Models\User $user */
            if ($request->has('email')) {
                $user->email = $request->email;
                $user->save();
            }

            // Update Warga data
            $warga->fill($request->only([
                'nama',
                'jenis_kelamin',
                'phone',
            ]));
            $warga->save();

            // Update DetailAlamat data
            if ($request->hasAny(['alamat', 'kabupaten', 'provinsi'])) {
                $alamat = $warga->alamat; // Assuming 'alamat' relationship exists
                if ($alamat) {
                    $alamat->fill($request->only([
                        'alamat',
                        'kabupaten',
                        'provinsi',
                    ]));
                    $alamat->save();
                } else {
                    // This case should ideally not happen if registration is always done with alamat
                    return response()->json(['message' => 'Detail alamat tidak ditemukan.'], 404);
                }
            }


            DB::commit();

            // Reload data to return the updated values
            /** @var \App\Models\User $user */
            $user->refresh();
            // Reload 'warga' with its relationships if they were affected, for a complete response
            $warga->load(['alamat', 'rt.rw']);

            return response()->json([
                'message' => 'Profil berhasil diperbarui.',
                'data' => [
                    'user' => $user,
                    'warga' => $warga,
                    'alamat' => $warga->alamat,
                ]
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Validasi gagal.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Terjadi kesalahan saat memperbarui profil.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the authenticated user's password.
     *
     * @OA\Put(
     * path="/api/profile/password",
     * operationId="updateUserPassword",
     * tags={"User Profile"},
     * summary="Update authenticated user's password",
     * description="Allows the authenticated user to change their password by providing the current password and a new password. The new password must be confirmed.",
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * required={"current_password", "new_password", "new_password_confirmation"},
     * @OA\Property(property="current_password", type="string", format="password", example="old_password", minLength=8, description="Current password of the user"),
     * @OA\Property(property="new_password", type="string", format="password", example="new_secure_password123", minLength=8, description="New password for the user"),
     * @OA\Property(property="new_password_confirmation", type="string", format="password", example="new_secure_password123", minLength=8, description="Confirmation of the new password. Must match 'new_password'.")
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Password updated successfully",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="Kata sandi berhasil diperbarui.")
     * )
     * )
     * ),
     * @OA\Response(
     * response=401,
     * description="Unauthenticated / Current password mismatch",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="Kata sandi saat ini salah.")
     * )
     * ),
     * @OA\Response(
     * response=422,
     * description="Validation error (e.g., new password too short, confirmation mismatch)",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="The given data was invalid."),
     * @OA\Property(property="errors", type="object")
     * )
     * ),
     * @OA\Response(
     * response=500,
     * description="Server error",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="Terjadi kesalahan saat memperbarui kata sandi."),
     * @OA\Property(property="error", type="string")
     * )
     * )
     * )
     */
    public function updatePassword(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['message' => 'Pengguna tidak terautentikasi.'], 401);
            }

            $request->validate([
                'current_password' => ['required', 'string'],
                'new_password' => ['required', 'string', 'min:8', 'confirmed'],
            ]);

            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json(['message' => 'Kata sandi saat ini salah.'], 401);
            }

            $user->password = Hash::make($request->new_password);
            

            return response()->json(['message' => 'Kata sandi berhasil diperbarui.'], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validasi gagal.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat memperbarui kata sandi.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}