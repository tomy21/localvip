<?php

use App\Http\Controllers\api\GateController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/gate', [GateController::class, 'index'])->name('home');
Route::post('/gate', [GateController::class, 'store'])->name('gate.post');
Route::get('/gate/{id}', [GateController::class, 'showById'])->name('gate.byId');
Route::put('/gate/{id}', [GateController::class, 'update'])->name('gate.update.byId');
Route::post('/inputTrxPK', [GateController::class, 'storeByPK'])->name('gate.storeByPK');
Route::post('/inputTrxPM', [GateController::class, 'storeByPM'])->name('gate.storeByPM');
