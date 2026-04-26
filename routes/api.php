<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MasterController;
use App\Http\Controllers\ProspectingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'message' => 'API is running'
    ]);
});

Route::post('/login', [AuthController::class, 'login']);



Route::middleware('jwt.auth')->group(function () {
    Route::post('/subs-id', [AuthController::class, 'updateSubsId']);

    Route::get('/dashboard/total-prospect', [DashboardController::class, 'totalProspect']);
    Route::get('/prospecting', [ProspectingController::class, 'index']);
    Route::get('/prospecting/store', [ProspectingController::class, 'store']);

    Route::prefix('master')->group(function () {
        Route::get('/provinsi', [MasterController::class, 'getProvinsi']);
        Route::get('/kota/{id?}', [MasterController::class, 'getKota']);
        Route::get('/kecamatan/{id?}', [MasterController::class, 'getKecamatan']);
    });

    Route::prefix('customer')->group(function () {
        Route::get('/', [CustomerController::class, 'index']);
        Route::post('/store', [CustomerController::class, 'store']);
        Route::get('/detail/{id?}', [CustomerController::class, 'show']);
        Route::put('/update/{id?}', [CustomerController::class, 'update']);
    });
});
Route::post('/refresh', [AuthController::class, 'refresh']);
