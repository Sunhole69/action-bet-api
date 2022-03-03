<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Traits\APIResponse;
use App\Traits\AuthUserManager;
use App\Traits\RemoteAPIServerTransactionActions;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    use APIResponse;
    use AuthUserManager;
    use RemoteAPIServerTransactionActions;
    public object $user;

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
            'action' => '"withdrawal_money',
            'note'   => $request->note,
            'username'   => $this->user->username,
            'user_type' => $this->user->user_type
        ];

        // Debit user account
        $debitUserResponse = $this->debitUser($data);
        if ($debitUserResponse->errorResponse === "SUCCESS"){
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
            return $this->successResponse($transactionDetails, 200);
        }
        return $this->errorResponse($response, 200);
    }

    public function debitUser($data){
        //Initiate remotely, if successful, update locally
        $response = $this->initiateRemoteTransaction($data);

        if ($response['errorCode'] === "SUCCESS"){
            Transaction::create([
                'user_id' => $this->user->id,
                'payment_type' => 'Withdrawal',
                'status'    => ucwords($response['errorCode']),
                'amount'    =>  $data['amount']
            ]);

            //Update user wallet
            $this->user->wallet->update([
                'user_id' => $this->user->id,
                'balance'  => $this->user->wallet->balance -  $data['amount']
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
            return $this->successResponse($transactionDetails, 200);
        }
        return $this->errorResponse($response, 200);
    }

    public function initiatePaymentGateway(){


        return true;
    }


}
