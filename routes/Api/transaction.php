<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TransactionController;

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

Route::prefix('v1')->group(function(){
    Route::middleware('auth:sanctum')->group(function() {
        Route::apiResource('/transaction', TransactionController::class);
        Route::patch('/refund-transaction/{transaction_id}', [TransactionController::class, 'refund_to_user']);
    });
});