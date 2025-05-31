<?php

namespace App\Http\Controllers;

use App\Models\ProgramKerja;
use App\Models\RW;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class programKerjaController extends Controller
{
    private function getAssociatedRwId(User $user): ?int
    {
        if ($user->role === 'PejabatRW') {
            $rw = RW::where('id', $user->Warga->rt->rw->id)->first();
            return $rw ? $rw->id : null;
        } else {
            if ($user->Warga && $user->Warga->rt && $user->Warga->rt->rw) {
                return $user->Warga->rt->rw->id;
            }
            return null;
        }
    }

    public function index(){
        try {
            $user = auth('sanctum')->user();
            $programKerja = collect(); 

            if($user->role === 'Admin'){
                $programKerja = ProgramKerja::all();
            }else{
                $rwId = $this->getAssociatedRwId($user);
                if ($rwId) {
                    $programKerja = ProgramKerja::where('id_rw', $rwId)->get();
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'List Program Kerja',
                'data' => $programKerja
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching data',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function store(Request $request){
        try {
            $user = auth('sanctum')->user();

            if ($user->role !== 'PejabatRW') {
                return response()->json([
                    'success' => false,
                    'message' => 'Forbidden. Only RW can create Program Kerja.'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'nama_program_kerja' => 'required|string|max:255',
                'tanggal_mulai' => 'required|date',
                'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
                'waktu_mulai' => 'required',
                'waktu_selesai' => 'nullable',
                'penanggung_jawab' => 'required|string|max:255',
                'tempat' => 'required|string|max:255',
            ]);

            if($validator->fails()){
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $rw = RW::where('id', $user->Warga->rt->rw->id)->first();
            if(!$rw){
                return response()->json([
                    'success' => false,
                    'message' => 'RW not found for this user.',
                ], 404);
            }

            $programKerja = ProgramKerja::create([
                'nama_program_kerja' => $request->nama_program_kerja,
                'tempat' => $request->tempat,
                'tanggal_mulai' => $request->tanggal_mulai,
                'tanggal_selesai' => $request->tanggal_selesai,
                'waktu_mulai' => $request->waktu_mulai,
                'waktu_selesai' => $request->waktu_selesai,
                'penanggung_jawab' => $request->penanggung_jawab,
                'id_rw' => $rw->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Program Kerja created successfully',
                'data' => $programKerja
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating Program Kerja',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function show($id){
        try {
            $user = auth('sanctum')->user();
            $programKerja = ProgramKerja::findOrFail($id);

            if ($user->role !== 'Admin') {
                $rwId = $this->getAssociatedRwId($user);
                if (!$rwId || $programKerja->id_rw !== $rwId) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Forbidden. You do not have access to this Program Kerja.'
                    ], 403);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Program Kerja found',
                'data' => $programKerja
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Program Kerja not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching Program Kerja',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function update(Request $request, $id){
        try {
            $user = auth('sanctum')->user();
            $programKerja = ProgramKerja::findOrFail($id);

            if ($user->role === 'PejabatRW') {
                $rw = RW::where('id', $user->Warga->rt->rw->id)->first();
                if (!$rw || $programKerja->id_rw !== $rw->id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Forbidden. You can only update your own RW Program Kerja.'
                    ], 403);
                }
            } elseif ($user->role !== 'Admin') {
                 return response()->json([
                    'success' => false,
                    'message' => 'Forbidden. You do not have permission to update this Program Kerja.'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'nama_program_kerja' => 'sometimes|required|string|max:255',
                'tanggal_mulai' => 'sometimes|required|date',
                'tanggal_selesai' => 'sometimes|nullable|date|after_or_equal:tanggal_mulai',
                'waktu_mulai' => 'sometimes|required',
                'waktu_selesai' => 'sometimes|nullable',
                'penanggung_jawab' => 'sometimes|required|string|max:255',
                'tempat' => 'sometimes|required|string|max:255',
            ]);

            if($validator->fails()){
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $programKerja->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Program Kerja updated successfully',
                'data' => $programKerja
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Program Kerja not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating Program Kerja',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function destroy($id){
        try {
            $user = auth('sanctum')->user();
            $programKerja = ProgramKerja::findOrFail($id);

            if ($user->role === 'PejabatRW') {
                $rw = RW::where('id', $user->Warga->rt->rw->id)->first();
                if (!$rw || $programKerja->id_rw !== $rw->id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Forbidden. You can only delete your own RW Program Kerja.'
                    ], 403);
                }
            } elseif ($user->role !== 'Admin') {
                 return response()->json([
                    'success' => false,
                    'message' => 'Forbidden. You do not have permission to delete this Program Kerja.'
                ], 403);
            }

            $programKerja->delete();
            return response()->json([
                'success' => true,
                'message' => 'Program Kerja deleted successfully'
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Program Kerja not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting Program Kerja',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
