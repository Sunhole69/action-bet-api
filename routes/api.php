<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\SportBookController;
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
Route::patch('/user/update-status/{user_id}', [UserController::class, 'updateUserStatus']);
// End of routes that are not needed now



// Public routes
Route::post('/sign-in',         [AuthController::class, 'login']);
Route::post('/sign-up',         [AuthController::class, 'signUp']);
Route::post('/agency/sign-up',  [AuthController::class, 'registerAffiliate']);

// Protected routes
Route::group(['middleware' => ['token-check']], function () {
    Route::post('/payment/deposit',                 [TransactionController::class, 'deposit']);
    Route::post('/payment/withdraw',                [TransactionController::class, 'withdraw']);
    Route::get('/payment/transactions',             [TransactionController::class, 'myTransactions']);
    Route::get('/payment/wallet',                   [TransactionController::class, 'myWallet']);
    Route::post('/payment/pay',                     [TransactionController::class, 'initiatePaymentGatewayTest']);
    Route::get('/user/{id}',                        [UserController::class, 'show']);
    Route::get('/user/me',                          [UserController::class, 'showMe']);
    Route::get('/players',                          [UserController::class, 'index']);
    Route::patch('/user/update-password/{user_id}', [UserController::class, 'updatePassword']);



    // Sports Book Section
    Route::get('/sport-book/sports',                [SportBookController::class, 'fetchPrematchSports']);
    Route::get('/sport-book/groups/{sport_id}',     [SportBookController::class, 'fetchPrematchSportGroups']);
    Route::get('/sport-book/leagues/{group_id}',    [SportBookController::class, 'fetchPrematchGroupLeagues']);
    Route::get('/sport-book/events/{league_id}',    [SportBookController::class, 'fetchPrematchLeagueEvents']);

});

