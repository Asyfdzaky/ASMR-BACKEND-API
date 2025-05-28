<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Warga;

class AuthenticatedSessionController extends Controller
{
    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request)
    {
        try {
            $request->authenticate();
            $user = $request->user();
            $warga = Warga::where('id_users', $user->id)->first();
            $plainTextToken = $user->createToken('auth_token')->plainTextToken;

            $header = base64_encode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
            $payload = base64_encode(json_encode(['id' => $user->id, 'role' => $user->role, 'email' => $user->email, 'name' => $warga->nama, 'no_kk' => $warga->nomor_kk]));
            $token = $header . '.' . $payload . '.' . $plainTextToken;


            return response()->json([
                'message' => 'Login berhasil',
                'token' => $token,
                'Rember' => $request->IsRemember(),
                // 'user' => $user,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Login gagal',
                'error' => $e->getMessage(),
                'status' => 'error',
            ], 400);
        }
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request)
    {
        try{
            $user = $request->user();
            if (!$user) {
                return response()->json([
                    'message' => 'Tidak ada pengguna yang login',
                    'status' => 'error'
                ], 401);
            }
    
            // Pastikan token tidak null sebelum dihapus
            if ($user->currentAccessToken()) {
                $user->tokens()->delete();
            } else {
                Log::error('Logout gagal: Token tidak ditemukan atau sudah dihapus');
                return response()->json([
                    'message' => 'Token tidak ditemukan atau sudah dihapus',
                    'status' => 'error'
                ], 400);
            }

            return response()->json([
                'message' => 'Logout berhasil',
                'status' => 'success',
            ], 200);
           
        } catch (\Exception $e) {
            Log::error('Logout gagal: ' . $e->getMessage());
            return response()->json([
                'message' => 'Logout gagal'. $e->getMessage(),
                'status' => 'error',
                
            ], 400);
        }
    }
}

