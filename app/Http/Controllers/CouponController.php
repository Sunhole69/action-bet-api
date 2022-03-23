<?php

namespace App\Http\Controllers;

use App\Models\AgencyBet;
use App\Models\PlayerBet;
use App\Models\PlayerBetCombine;
use App\Models\PlayerBetCombineAmount;
use App\Models\PlayerBetCombineEvent;
use App\Models\PlayerBetMultiple;
use App\Models\PlayerBetMultipleEvent;
use App\Models\Token;
use App\Models\User;
use App\Models\Wallet;
use App\Traits\AuthHelpers\AuthUserManager;
use App\Traits\RequestHelpers\APIResponse;
use App\Traits\RequestHelpers\RemoteAPIServerCouponActions;
use Illuminate\Http\Request;
use function Symfony\Component\Finder\in;

class CouponController extends Controller
{
    use APIResponse;
    use AuthUserManager;
    use RemoteAPIServerCouponActions;
    public $user;
    public $token;

    public function __construct(Request $request)
    {
        $this->user  = $this->getCurrentUser($request);
        if ($this->user){
            $this->token = Token::where('username', $this->user->username)->first()->token;
        }
    }

    public function defaultAgencyCoupon(){
        $response = $this->getAgencyDefaultCouponBonus();
        return $this->successResponse($response, 200);

    }

    public function userCouponBonus(Request $request){

        $data = [
            'username'  => $this->user->username,
            'user_type' => $this->user->user_type,
        ];
        if (ucwords($data['user_type']) === 'Player'){
            $token = $this->initiatePlayerToken($data);
        }

        if (ucwords($data['user_type']) === 'Agency'){
            $token = $this->initiateAgencyToken($data);
        }

        $data['token'] = $token;
        $response = $this->getUserCouponBonus($data);
        return $this->successResponse($response, 200);
    }


    public function playerPlayCouponSingle(Request $request){
        $request->validate([
            'amount'       => 'required|numeric',
            'search_code'  => 'required|string',
            'sign_key'     => 'required|string',
            'rank'         => 'required|numeric',
        ]);

        //Check player wallet before placing bet
        $wallet = Wallet::where('user_id', $this->user->id)->first();
        if ($wallet->balance === 0 && $wallet->bonus === 0){
            return $this->errorResponse(array([
                'status' => 'Insufficient fund',
                'message'   => "You have 0 fund in your wallet"
            ]), 422);
        }

        if ($wallet->balance < $request->amount && $wallet->bonus < $request->amount){
            return $this->errorResponse(array([
                'status' => 'Insufficient fund',
                'message'   => "Your wallet balance is insufficient for the bet"
            ]), 422);
        }

        $data['amount']       = $request->amount;
        $data['search_code']  = $request->search_code;
        $data['sign_key']     = $request->sign_key;
        $data['rank']         = $request->rank;
        $data['token']        = $this->token;
        $response = $this->playerPlayCouponSingleSetup($data);


        //Debit agency balance or bonus if request is successful
        if ($response["errorCode"] === "SUCCESS"){
            // Check if they have enough cash in their bonus wallet
            if ($wallet->bonus >= $request->amount){
                $wallet->bonus = $wallet->bonus - $request->amount;
            } else {
                //Debit from their balance
                $wallet->balance = $wallet->balance - $request->amount;
            }
            $wallet->save();

            // Lastly Insert the betting record to database
            PlayerBet::create([
                'user_id'         => $this->user->id,
                'bet_type'        => 'single',
                'amount'          => $request->amount,
            ]);
        }

        return $this->successResponse($response, 200);
    }

    public function playerPlayCouponMultipleAndSplit(Request $request){
        $request->validate([
            "events"                => "required|array|min:2",
            "events.*"              => "required|array",
            'events.*.search_code'  => 'required|string',
            'events.*.sign'         => 'required|string',
            'events.*.rank'         => 'required|numeric',
            'amount'                => 'required|numeric',
            'bet_type'              => 'required|string'
        ]);

        if ($request->bet_type !== 'multiple' && $request->bet_type !== 'split'){
            return $this->errorResponse([
                "error" => "Invalid bet type",
                "message"  => 'Bet type can either be multiple or split'
            ], 422);
        }

        //Check player wallet before placing bet
        $wallet = Wallet::where('user_id', $this->user->id)->first();
        if ($wallet->balance === 0 && $wallet->bonus === 0){
            return $this->errorResponse(array([
                'status' => 'Insufficient fund',
                'message'   => "You have 0 fund in your wallet"
            ]), 422);
        }

        if ($wallet->balance < $request->amount && $wallet->bonus < $request->amount){
            return $this->errorResponse(array([
                'status' => 'Insufficient fund',
                'message'   => "Your wallet balance is insufficient for the bet"
            ]), 422);
        }

        $data['username']     = $this->user->username;
        $data['amount']       = $request->amount;
        $data['search_code']  = $request->search_code;
        $data['sign_key']     = $request->sign_key;
        $data['rank']         = $request->rank;
        $data['token']        = $this->token;
        $data['events']       = $request->events;
        $data['type']        = $request->bet_type;
        $response = $this->playCouponMultipleAndSplitSetup($data);

        //Debit agency balance or bonus if request is successful
        if ($response["errorCode"] === "SUCCESS"){
            // Check if they have enough cash in their bonus wallet
            if ($wallet->bonus >= $request->amount){
                $wallet->bonus = $wallet->bonus - $request->amount;
            } else {
                //Debit from their balance
                $wallet->balance = $wallet->balance - $request->amount;
            }
            $wallet->save();

            // Lastly Insert the betting record to database
           $bet = PlayerBetMultiple::create([
                'user_id'         => $this->user->id,
                'bet_type'        => $data['type'],
                'amount'          => $request->amount,
                'coupon_id'       => $response['data']['coupon_id'],
                'status'          => $response['data']['status']
            ]);

            foreach ($data['events'] as $game){
                PlayerBetMultipleEvent::create([
                    'player_bet_multiples_id' => $bet->id,
                    'search_code'      => $game['search_code'],
                    'sign_key'         => $game['sign'],
                    'rank'             => $game['rank']
                ]);
            }

        }

        return $this->successResponse($response, 200);
    }

    public function playerPlayCouponCombined(Request $request){
        $request->validate([
            'amount'                  => 'required|numeric',
            "events"                  => "required|array|min:2",
            "events.*"                => "required|array",
            'events.*.search_code'    => 'required|string',
            'events.*.sign'           => 'required|string',
            'events.*.rank'           => 'required|numeric',
            "amounts"                 => "required|array|min:1",
            "amounts.*"               => "required|array",
            'amounts.*.events_count'  => 'required|numeric',
            'amounts.*.amount'        => 'required|numeric',
        ]);

        $betCash = 0;
        foreach ($request->amounts as $cash){
            $betCash = $betCash + ($cash['amount'] * $cash['events_count']);
        }

        if ($betCash !== $request->amount){
            return $this->errorResponse([
                'betCash'  => $betCash,
                'amount'  => $request->amount,
                'error' => 'Conflict with betting amount',
                'message' => 'The sum of you split amount should match your total stake amount'
            ], 422);
        }

        //Check player wallet before placing bet
        $walletStatus = $this->checkUserWallet($betCash);
        if ($walletStatus['errorCode'] !== 'SUCCESS'){
            return $this->errorResponse($walletStatus, 422);
        }

        $data['username']     = $this->user->username;
        $data['amount']       = $request->amount;
        $data['search_code']  = $request->search_code;
        $data['sign_key']     = $request->sign_key;
        $data['rank']         = $request->rank;
        $data['token']        = $this->token;
        $data['combined']     = ['combined'];
        $data['events']       = $request->events;
        $data['type']         = 'combined';
        $data['amounts']      = $request->amounts;
        $response = $this->playerPlayCouponCombinedSetup($data);

        //Debit agency balance or bonus if request is successful
        if ($response["errorCode"] === "SUCCESS"){
            // Check if they have enough cash in their bonus wallet
            $this->debitUserWallet($walletStatus['wallet'], $betCash);

            // Lastly Insert the betting record to database
            $bet = PlayerBetCombine::create([
                'user_id'         => $this->user->id,
                'amount'          => $request->amount,
                'coupon_id'       => $response['data']['coupon_id'],
                'status'          => $response['data']['status']
            ]);

            foreach ($data['events'] as $game){
                PlayerBetCombineEvent::create([
                    'player_bet_combines_id' => $bet->id,
                    'search_code'             => $game['search_code'],
                    'sign_key'                => $game['sign'],
                    'rank'                    => $game['rank']
                ]);
            }

            foreach ($data['amounts'] as $amount){
                PlayerBetCombineAmount::create([
                    'player_bet_combines_id' => $bet->id,
                    'events_count'  => $amount->eventS_count,
                    'amount'        => $amount->amount
                ]);
            }

        }

        return $this->successResponse($response, 200);
    }

    public function AgencyPlayCouponSingle(Request $request){
        $request->validate([
            'amount'              => 'required|numeric',
            'search_code'         => 'required|string',
            'sign_key'            => 'required|string',
            'rank'                => 'required|numeric',
            'player_username'     => 'required',
        ]);

        if(!User::where('username', $request->player_username)->where('user_type', 'player')->first()){
            return $this->errorResponse(array([
                'status' => 'Invalid request',
                'message'   => "Player with the username ' $request->player_username ' not found"
            ]), 404);
        }

        //Check agent wallet before placing bet
        $wallet = Wallet::where('user_id', $this->user->id)->first();
        if ($wallet->balance === 0 && $wallet->bonus === 0){
            return $this->errorResponse(array([
                'status' => 'Insufficient fund',
                'message'   => "You have 0 fund in your wallet"
            ]), 422);
        }

        if ($wallet->balance < $request->amount && $wallet->bonus < $request->amount){
            return $this->errorResponse(array([
                'status' => 'Insufficient fund',
                'message'   => "Your wallet balance is insufficient for the bet"
            ]), 422);
        }

        $data['username']     = $request->player_username;
        $data['amount']       = $request->amount;
        $data['search_code']  = $request->search_code;
        $data['sign_key']     = $request->sign_key;
        $data['rank']         = $request->rank;
        $data['token']        = $this->token;
        $response             = $this->agencyPlayCouponSingleSetup($data);

        //Debit agency balance or bonus if request is successful
        if ($response["errorCode"] === "SUCCESS"){
            // Check if they have enough cash in their bonus wallet
            if ($wallet->bonus >= $request->amount){
                $wallet->bonus = $wallet->bonus - $request->amount;
            } else {
                //Debit from their balance
                $wallet->balance = $wallet->balance - $request->amount;
            }
            $wallet->save();

            // Lastly Insert the betting record to database
            AgencyBet::create([
                'user_id'         => $this->user->id,
                'player_username' => $data['username'],
                'bet_type'        => 'single',
                'amount'          => $request->amount,
            ]);
        }

        return $this->successResponse($response, 200);
    }

    public function getPlayerCoupons(Request $request){
        $request->validate([
            'page'        => 'required|numeric',
            'date_from'   => 'required|date_format:Y-m-d|before_or_equal:today',
            'date_to'     => 'required|date_format:Y-m-d|before_or_equal:today|after_or_equal:date_from',
        ]);

        $data = [
            'page'      => $request->page,
            'username'  => $this->user->username,
            'user_type' => $this->user->user_type,
            'dateFrom'  => $request->date_from,
            'dateTo'    => $request->date_to,
            'timezone'  => 'Africa/Lagos'
        ];

        if (ucwords($data['user_type']) === 'Player'){
            $token = $this->initiatePlayerToken($data);
        }

        if (ucwords($data['user_type']) === 'Agency'){
            $token = $this->initiateAgencyToken($data);
        }

        $data['token'] = $token;
        $response = $this->playerGetCouponsSetup($data);
        return $this->successResponse($response, 200);
    }

    public function showPlayerCoupon(Request $request){
        $request->validate([
            'coupon_id'        => 'required|numeric',
        ]);

        $data = [
            'username'  => $this->user->username,
            'user_type' => $this->user->user_type,
            'timezone'  => 'Africa/Lagos',
            'lang'      => 'EN',
            'coupon_id'  => $request->coupon_id,
        ];

        if (ucwords($data['user_type']) === 'Player'){
            $token = $this->initiatePlayerToken($data);
        }

        if (ucwords($data['user_type']) === 'Agency'){
            $token = $this->initiateAgencyToken($data);
        }

        $data['token'] = $token;
        $response = $this->playerShowCouponsSetup($data);
        return $this->successResponse($response, 200);
    }

    public function playerCashOutList(Request $request){
        $data = [
            'username'  => $this->user->username,
            'user_type' => $this->user->user_type,
        ];

        if (ucwords($data['user_type']) === 'Player'){
            $token = $this->initiatePlayerToken($data);
        }

        if (ucwords($data['user_type']) === 'Agency'){
            $token = $this->initiateAgencyToken($data);
        }

        $data['token'] = $token;
        $response = $this->playerCouponsCashoutListSetup($data);
        return $this->successResponse($response, 200);
    }

    public function playerDoCashOut(Request $request){
        $request->validate([
            'coupon_id'        => 'required|numeric',
            'cashout_amount'   => 'required|numeric'
        ]);

        $data = [
            'username'         => $this->user->username,
            'user_type'        => $this->user->user_type,
            'coupon_id'        => $request->coupon_id,
            'cashout_amount'   => $request->cashout_amount
        ];

        if (ucwords($data['user_type']) === 'Player'){
            $token = $this->initiatePlayerToken($data);
        }

        if (ucwords($data['user_type']) === 'Agency'){
            $token = $this->initiateAgencyToken($data);
        }

        $data['token'] = $token;
        $response = $this->playerDoCouponsCashoutSetup($data);
        return $this->successResponse($response, 200);
    }




    public function checkUserWallet ($amount){
        //Check player wallet before placing bet
        $wallet = Wallet::where('user_id', $this->user->id)->first();
        if ($wallet->balance === 0 && $wallet->bonus === 0){
            return array([
                'errorCode' => 'FAIL',
                'status'    => 'Insufficient fund',
                'message'   => "You have 0 fund in your wallet"
            ]);
        }

        if ($wallet->balance < $amount && $wallet->bonus < $amount){
            return array([
                'errorCode' => 'FAIL',
                'status'    => 'Insufficient fund',
                'message'   => "Your wallet balance is insufficient for the bet"
            ]);
        }

        // Return error and user wallet details
        return [
            'errorCode' => 'SUCCESS',
            'wallet'    => $wallet
        ];
    }

    public function debitUserWallet(Wallet $wallet, $amount){
        if ($wallet->bonus >= $amount){
            $wallet->bonus = $wallet->bonus - $amount;
        } else {
            //Debit from their balance
            $wallet->balance = $wallet->balance - $amount;
        }
        $wallet->save();
    }

}

