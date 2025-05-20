<?php

namespace App\Http\Controllers;

use App\Models\ProgramKerja;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Http\Request;

class programKerjaController extends Controller
{
    public function index(){
        try {
            $programKerja = ProgramKerja::all();
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
            $programKerja = ProgramKerja::create($request->all());
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
            $programKerja = ProgramKerja::findOrFail($id);
            return response()->json([
                'success' => true,
                'message' => 'Program Kerja found',
                'data' => $programKerja
            ], 200);
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
            $programKerja = ProgramKerja::findOrFail($id);
            $programKerja->update($request->all());
            return response()->json([
                'success' => true,
                'message' => 'Program Kerja updated successfully',
                'data' => $programKerja
            ], 200);
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
            $programKerja = ProgramKerja::findOrFail($id);
            $programKerja->delete();
            return response()->json([
                'success' => true,
                'message' => 'Program Kerja deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting Program Kerja',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
