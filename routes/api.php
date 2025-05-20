<?php

use App\Http\Controllers\GetRtRW;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisterPejabatController;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});
Route::get('/RW/list',[GetRtRW::class, 'GetRW']);
Route::get('/RT/list/{id}',[GetRtRW::class, 'GetRT']);

Route::post('/register/pejabat', [RegisterPejabatController::class, 'Store']);
Route::put('/pejabat/{id}', [RegisterPejabatController::class, 'update']);
Route::delete('/pejabat/{id}', [RegisterPejabatController::class, 'destroy']);

Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {

});
require __DIR__.'/auth.php';    