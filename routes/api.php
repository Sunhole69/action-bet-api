<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TokenController;
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

Route::post('/sign-up', [AuthController::class, 'signUp']);
Route::post('/affiliate/sign-up', [AuthController::class, 'registerAffiliate']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('agency/login', [AuthController::class, 'agencyLogin']);
Route::post('admin/login', [AuthController::class, 'adminLogin']);

Route::get('/user/{id}', [UserController::class, 'show']);
Route::get('/players', [UserController::class, 'index']);
Route::patch('/user/update-password/{user_id}', [UserController::class, 'updatePassword']);
Route::patch('/user/update-status/{user_id}', [UserController::class, 'updateUserStatus']);


// Protected routes
Route::group(['middleware' => ['auth:sanctum']], function () {


});
