<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TokenController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Routes that are not needed now
Route::post('agency/login', [AuthController::class, 'agencyLogin']);
Route::post('admin/login',  [AuthController::class, 'adminLogin']);
Route::get('/user/{id}',    [UserController::class, 'show']);
Route::get('/players',      [UserController::class, 'index']);
Route::patch('/user/update-status/{user_id}', [UserController::class, 'updateUserStatus']);
// End of routes that are not needed now



// Public routes
Route::post('/sign-in',         [AuthController::class, 'login']);
Route::post('/sign-up',         [AuthController::class, 'signUp']);
Route::post('/agency/sign-up',  [AuthController::class, 'registerAffiliate']);

// Protected routes
Route::group(['middleware' => ['token-check']], function () {
    Route::post('/payment/deposit', [TransactionController::class, 'deposit']);
    Route::post('/payment/withdraw', [TransactionController::class, 'deposit']);
    Route::get('/user/{id}',    [UserController::class, 'show']);
    Route::get('/user/me',    [UserController::class, 'showMe']);
    Route::patch('/user/update-password/{user_id}', [UserController::class, 'updatePassword']);
});
