<?php

namespace App\Http\Controllers;

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
        // Trigger payment gateway here
        $gatewayResponse = $this->initiatePaymentGateway();
        if ($gatewayResponse->errorResponse == "FAIL"){
            return $this->errorResponse($gatewayResponse, 422);
        }

        // Credit user account
        $creditUserResponse = $this->creditUser($data);
        if ($creditUserResponse->errorResponse === "SUCCESS"){
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
                'user_id' => $this->user->id,
                'balance'  => $this->user->wallet->balance +  $data['amount']
            ]);

            $wallet = Wallet::where('user_id', $this->user->id)->first();
            $transactionDetails = [
                "errorCode" => "SUCCESS",
                'transaction-details' => $response['data'],
                'wallet' => $wallet
            ];

            // user_type is Player, then deduct from agency
            Transaction::create([
                'user_id' => $this->user->id,
                'payment_type' => 'Deposit',
                'status'    => ucwords($response['errorCode']),
                'amount'    =>  $data['amount']
            ]);

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
