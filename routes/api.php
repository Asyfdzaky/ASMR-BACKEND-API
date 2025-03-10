<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisterPejabatController;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});
Route::get('/RW/list',[RegisterPejabatController::class, 'GetRW']);
Route::get('/RT/list/{id}',[RegisterPejabatController::class, 'GetRT']);

require __DIR__.'/auth.php';