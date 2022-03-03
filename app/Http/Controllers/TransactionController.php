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

    public function deposit(Request $request){
        $user = $this->getCurrentUser($request);
        $request->validate([
            'amount' => 'required|numeric',
            'note'  => 'required|string|max:255',
        ]);
        $data = [
            'amount' => $request->amount,
            'note'   => $request->note,
            'username'   => $user->username,
            'user_type' => $user->user_type
        ];
        // Trigger payment gateway here



        $creditUserResponse = $this->creditUser($data);
        if ($creditUserResponse->errorResponse === "SUCCESS"){
            //Finally return the server response if fails
            return $this->successResponse($creditUserResponse, 200);
        }

        //Finally return the server response if fails
        return $this->errorResponse($creditUserResponse, 200);

    }

    public function initiatePaymentGateway(){

        return true;
    }

    public function creditUser($data){

        //Initiate remotely, if successful, update locally
        $response = $this->depositRemotely($data);

        if ($response['errorCode'] === "SUCCESS"){
            Transaction::create([
                'user_id' => $user->id,
                'payment_type' => 'Deposit',
                'status'    => ucwords($response['errorCode']),
                'amount'    => $request->amount
            ]);

            //Update user wallet
            $user->wallet->update([
                'user_id' => $user->id,
                'balance'  => $user->wallet->balance + $request->amount
            ]);

            $wallet = Wallet::where('user_id', $user->id)->first();
            $transactionDetails = [
                "errorCode" => "SUCCESS",
                'transaction-details' => $response['data'],
                'wallet' => $wallet
            ];


            $agency = User::where('username', $user->agency)->first();
            // user_type is Player, then deduct from agency
            Transaction::create([
                'user_id' => $user->id,
                'payment_type' => 'Deposit',
                'status'    => ucwords($response['errorCode']),
                'amount'    => $request->amount
            ]);

            //Finally return the server response
            return $this->successResponse($transactionDetails, 200);
        }
        return $this->errorResponse($response, 200);
    }



}
