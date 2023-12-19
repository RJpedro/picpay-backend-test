<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\Controller; 

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

Route::apiResource('transaction', TransactionController::class);
Route::patch('refund-transaction/{transaction_id}', [TransactionController::class, 'refund_to_user']);
Route::apiResource('account', AccountController::class);
Route::apiResource('user', UserController::class);

Route::get('all-routes', [Controller::class, 'all_routes']);
