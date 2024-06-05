<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MarchandController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PaiementController;

// Routes pour UserController
Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);
Route::get('/users', [UserController::class, 'index']);
Route::get('/marchands', [MarchandController::class, 'index']);
Route::post('/marchands', [MarchandController::class, 'store']);
Route::middleware('auth:sanctum')->get('/paiements/graphe/{period}', [PaiementController::class, 'getPaymentsForGraph']);
Route::middleware('auth:sanctum')->get('/paiements/stats', [PaiementController::class, 'getDashboardData']);
Route::middleware('auth:sanctum')->post('/transferToMarchand', [MarchandController::class, 'transfer']);
Route::get('/paiements/list/{marchandId}', [PaiementController::class, 'getPaiementsMarchand']);


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
