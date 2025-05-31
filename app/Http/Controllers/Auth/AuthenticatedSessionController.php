<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Warga;
use App\Models\RT;
use App\Models\RW;

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
            $role = $user->role;
            if ($role !== 'Admin'){
                $warga = Warga::where('id_users', $user->id)->first();
                $rt = RT::where('id', $warga->rt->id)->first();
                $rw = RW::where('id', $warga->rt->rw->id)->first();
                $payload = base64_encode(json_encode(['id' => $user->id, 'id_warga' => $warga->id, 'id_rt' => $rt->id, 'id_rw' => $rw->id, 'role' => $user->role, 'email' => $user->email, 'name' => $warga->nama, 'no_kk' => $warga->nomor_kk]));
            } else {
                $payload = base64_encode(json_encode(['id' => $user->id, 'role' => $user->role, 'email' => $user->email, 'name' => $user->name]));
            }
            $plainTextToken = $user->createToken('auth_token')->plainTextToken;

            $header = base64_encode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
            $token = $header . '.' . $payload . '.' . $plainTextToken;


            return response()->json([
                'message' => 'Login berhasil',
                'token' => $token,
                // 'remember' => $request->IsRemember(),
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

                try {
                    $sessionId = $request->session()->getId();
                    if ($sessionId) {
                        DB::table('sessions')->where('id', $sessionId)->delete();
                        Log::info('Session berhasil dihapus untuk user ID: ' . $user->id);
                    } else {
                        Log::warning('Session ID tidak ditemukan saat logout untuk user ID: ' . $user->id);
                    }
                } catch (\Exception $sessionException) {
                    Log::error('Gagal menghapus session saat logout untuk user ID: ' . $user->id . '. Error: ' . $sessionException->getMessage());
                }

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

