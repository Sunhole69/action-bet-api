<?php

use App\Http\Controllers\AntePostSportBookController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\LiveSportBookController;
use App\Http\Controllers\PrematchSportBookController;
use App\Http\Controllers\SpecialSportBookController;
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
Route::post('agency/login',                                [AuthController::class, 'agencyLogin']);
Route::post('admin/login',                                 [AuthController::class, 'adminLogin']);
Route::patch('/user/update-status/{user_id}',              [UserController::class, 'updateUserStatus']);
// End of routes that are not needed now



// Public routes
Route::post('/sign-in',                                     [AuthController::class, 'login']);
Route::post('/sign-up',                                     [AuthController::class, 'signUp']);
Route::post('/agency/sign-up',                              [AuthController::class, 'registerAffiliate']);

// Protected routes
Route::group(['middleware' => ['token-check']], function () {
    Route::post('/payment/deposit',                         [TransactionController::class, 'deposit']);
    Route::post('/payment/withdraw',                        [TransactionController::class, 'withdraw']);
    Route::get('/payment/transactions',                     [TransactionController::class, 'myTransactions']);
    Route::get('/payment/wallet',                           [TransactionController::class, 'myWallet']);
    Route::post('/payment/pay',                             [TransactionController::class, 'initiatePaymentGatewayTest']);
    Route::get('/user/{id}',                                [UserController::class, 'show']);
    Route::get('/user/me',                                  [UserController::class, 'showMe']);
    Route::get('/players',                                  [UserController::class, 'index']);
    Route::patch('/user/update-password/{user_id}',         [UserController::class, 'updatePassword']);



    // Prematch Sports Book Section
    Route::get('/sport-book/prematch/sports',                [PrematchSportBookController::class, 'fetchPrematchSports']);
    Route::get('/sport-book/prematch/groups/{sport_id}',     [PrematchSportBookController::class, 'fetchPrematchSportGroups']);
    Route::get('/sport-book/prematch/leagues/{group_id}',    [PrematchSportBookController::class, 'fetchPrematchGroupLeagues']);
    Route::get('/sport-book/prematch/events/{league_id}',    [PrematchSportBookController::class, 'fetchPrematchLeagueEvents']);
    Route::get('/sport-book/prematch/sync',                  [PrematchSportBookController::class, 'syncPrematchSportBook']);


    // Antepost Sports Book Section
    Route::get('/sport-book/antepost/sports',                [AntePostSportBookController::class, 'fetchAntePostSports']);
    Route::get('/sport-book/antepost/groups/{sport_id}',     [AntePostSportBookController::class, 'fetchAntePostSportGroups']);
    Route::get('/sport-book/antepost/events/{group_id}',     [AntePostSportBookController::class, 'fetchAntePostGroupEvents']);
    Route::get('/sport-book/antepost/odds/{search_code}',    [AntePostSportBookController::class, 'fetchAntePostOddList']);
    Route::get('/sport-book/prematch/sync',                  [PrematchSportBookController::class, 'syncPrematchSportBook']);


    // Special Sports Book Section
    Route::get('/sport-book/special/sports',                 [SpecialSportBookController::class, 'fetchSpecialSports']);
    Route::get('/sport-book/special/groups/{sport_id}',      [SpecialSportBookController::class, 'fetchSpecialSportGroups']);
    Route::get('/sport-book/special/leagues/{group_id}',     [SpecialSportBookController::class, 'fetchSpecialGroupLeagues']);
    Route::get('/sport-book/special/events/{league_id}',     [SpecialSportBookController::class, 'fetchSpecialLeagueEvents']);
    Route::get('/sport-book/special/odds/{search_code}',     [SpecialSportBookController::class, 'fetchSpecialOddList']);
    Route::get('/sport-book/special/sync',                   [SpecialSportBookController::class, 'syncSpecialSportBook']);


    // Live Sports Book Section
    Route::get('/sport-book/live/events',                    [LiveSportBookController::class, 'fetchLiveEvents']);
    Route::get('/sport-book/live/odds',                      [LiveSportBookController::class, 'fetchLiveOddsStructure']);


    // Coupon and betting
    Route::get('/coupon/agency-default',                      [CouponController::class, 'defaultAgencyCoupon']);
    Route::get('/coupon/user-bonus',                          [CouponController::class, 'userCouponBonus']);




});

