<?php

namespace App\Http\Controllers;

use App\Models\PadiWinControl;
use App\Models\PadiWinUser;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Traits\AuthHelpers\AuthUserManager;
use App\Traits\JsonBuilders\PaystackJsonRequestBuilder;
use App\Traits\RequestHelpers\APIResponse;
use App\Traits\RequestHelpers\RemoteAPIServerTransactionActions;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    use APIResponse;
    use AuthUserManager;
    use RemoteAPIServerTransactionActions;
    use PaystackJsonRequestBuilder;
    public $user;

    public function __construct(Request $request)
    {
        $this->user = $this->getCurrentUser($request);
    }

    public function deposit(Request $request){
        $request->validate([
            'amount' => 'required|numeric',
            'note'  => 'required|string|max:255',
        ]);
        $data = [
            'action' => 'deposit_money',
            'amount' => $request->amount,
            'note'   => $request->note,
            'username'   => $this->user->username,
            'user_type' => $this->user->user_type
        ];

        // Check if the Parent agency has enough credit
        $agency = User::where('user_type', 'agency')->where('username', $this->user->agency)->first();
        if ($agency->wallet->balance < $request->amount){
            return $this->errorResponse([
                'errorCode' => 'TRANSACTION_ERROR',
                'message'   => 'Agency does not have enough balance'
            ], 409);
        }

//        // Trigger payment gateway here
//        $gatewayResponse = $this->initiatePaymentGateway();
//        if ($gatewayResponse->errorResponse == "FAIL"){
//            return $this->errorResponse($gatewayResponse, 422);
//        }

        // Credit user account
        $creditUserResponse = $this->creditUser($data);
        if ($creditUserResponse['errorCode'] === "SUCCESS"){
            // Credit the referrer 10% of the deposit
            if ($this->user->referred){
                $padiWinControl = PadiWinControl::first();
                $percentage_cut = ($request->amount / 100 )* $padiWinControl->percentage_bonus;
                 // Create a transaction for the referrer
                Transaction::create([
                    'user_id'      => $this->user->referrer_id,
                    'payment_type' => 'Padiwin_bonus',
                    'status'       => 'Success',
                    'amount'       => $percentage_cut
                ]);

                // Update the wallet of the referrer
                $refWallet = Wallet::where('user_id', $this->user->referrer_id)->first();
                $refWallet->padi_win_bonus = $refWallet->padi_win_bonus + $percentage_cut;
                $refWallet->save();
            }

            //Finally return the server response if fails
            return $this->successResponse($creditUserResponse, 200);
        }


        //Finally return the server response if fails
        return $this->errorResponse($creditUserResponse, 400);

    }

    public function withdraw(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric',
            'note'  => 'required|string|max:255',
        ]);
        $data = [
            'amount' => $request->amount,
            'action' => 'withdrawal_money',
            'note'   => $request->note,
            'username'   => $this->user->username,
            'user_type' => $this->user->user_type
        ];

        //If payment is successful through the payment gateway


        // Debit user account
        $debitUserResponse = $this->debitUser($data);
        if ($debitUserResponse['errorCode'] === "SUCCESS"){
            //Finally return the server response if fails
            return $this->successResponse($debitUserResponse, 200);
        }

        //Finally return the server response if fails
        return $this->errorResponse($debitUserResponse, 400);
    }

    public function creditUser($data){
        $data['agency'] = $this->user->username;
        //Initiate remotely, if successful, update locally
        $response = $this->initiateRemoteTransaction($data);

        if ($response['errorCode'] === "SUCCESS"){
            Transaction::create([
                'user_id' => $this->user->id,
                'payment_type' => 'Deposit',
                'status'    => ucwords($response['errorCode']),
                'amount'    => $data['amount']
            ]);

            //Update user wallet
            $this->user->wallet->update([
                'balance'  => $this->user->wallet->balance +  $data['amount']
            ]);

            $wallet = Wallet::where('user_id', $this->user->id)->first();
            $transactionDetails = [
                "errorCode" => "SUCCESS",
                'transaction-details' => $response['data'],
                'wallet' => $wallet
            ];


            // user_type is Player, then deduct from agency
            if ($this->user->user_type === 'player'){
                $agency = User::where('user_type', 'agency')->where('username', $this->user->agency)->first();

                Transaction::create([
                    'user_id'      => $agency->id,
                    'payment_type' => 'Player_credit',
                    'status'       => ucwords($response['errorCode']),
                    'amount'       =>  $data['amount']
                ]);

                $agency->wallet->update([
                    'balance'  => $agency->wallet->balance -  $data['amount']
                ]);

            }

            //Finally return the server response
            return $transactionDetails;
        }
        return $response;
    }

    public function debitUser($data){
        //Initiate remotely, if successful, update locally
        $response = $this->initiateRemoteTransaction($data);

        if ($response['errorCode'] === "SUCCESS"){
            Transaction::create([
                'user_id'      => $this->user->id,
                'payment_type' => 'Withdrawal',
                'status'       => ucwords($response['errorCode']),
                'amount'       =>  $data['amount']
            ]);

            //Update user wallet
            $this->user->wallet->update([
                'user_id'  => $this->user->id,
                'balance'  => $this->user->wallet->balance -  $data['amount']
            ]);

            $wallet = Wallet::where('user_id', $this->user->id)->first();
            $transactionDetails = [
                "errorCode"           => "SUCCESS",
                'transaction-details' => $response['data'],
                'wallet'              => $wallet
            ];

            // user_type is Player, then deduct from agency
            Transaction::create([
                'user_id' => $this->user->id,
                'payment_type' => 'Withdrawal',
                'status'    => ucwords($response['errorCode']),
                'amount'    =>  $data['amount']
            ]);

            //Finally return the server response
            return $transactionDetails;
        }
        return $response;
    }

    public function initiatePaymentGateway($data){
        $data = [
            "email" => $this->user,
            "amount" => $data['amount'],
        ];
        $response =  $this->sendPaymentCharge(getenv('PAYSTACK_PUBLIC_KEY'), $data);

        error_log($response['message']);
        return true;
    }

    public function initiatePaymentGatewayTest(Request $request){
        $request->validate([
            'amount' => 'required|numeric',
        ]);
        $data = [
            "email" => $this->user->email,
            "amount" => $request->amount,
        ];
        $response =  $this->sendPaymentCharge(getenv('PAYSTACK_PAYMENT_URL'), $this->buildChargeRequestData($data));

        error_log($response['message']);
        return $this->successResponse($response, 200);
//        return true;
    }

    public function myTransactions(){
        $transactions = Transaction::orderBy('created_at', 'DESC')->where('user_id', $this->user->id)->get();
        return $this->showAll($transactions);
    }

    public function myWallet(){
        $wallet = Wallet::where('user_id', $this->user->id)->first();
        return $this->showOne($wallet);
    }


}
