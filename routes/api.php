<?php

use App\Http\Controllers\AuthController;
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
});
Route::post('/refresh', [AuthController::class, 'refresh']);
